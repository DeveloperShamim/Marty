<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\Courier\BdCourierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BdCourierKeyFailoverTest extends TestCase
{
    use RefreshDatabase;

    private function keys(array $keys): void
    {
        Setting::put('bdcourier_keys', json_encode($keys));
    }

    private function ok(): array
    {
        return ['status' => 'success', 'data' => ['summary' => ['total_parcel' => 2, 'success_parcel' => 2, 'cancelled_parcel' => 0, 'success_ratio' => 100]]];
    }

    private function tokensUsed(): array
    {
        return Http::recorded()->map(fn ($p) => str_replace('Bearer ', '', $p[0]->header('Authorization')[0]))->all();
    }

    public function test_first_key_is_used_until_its_daily_limit_then_the_next(): void
    {
        Http::fake(['api.bdcourier.com/courier-check' => Http::response($this->ok())]);
        $this->keys([
            ['id' => 'a', 'label' => 'Main', 'token' => 'TOKEN_A', 'limit' => 2],
            ['id' => 'b', 'label' => 'Backup', 'token' => 'TOKEN_B', 'limit' => 1],
        ]);
        $s = app(BdCourierService::class);

        foreach (['01711000001', '01711000002', '01711000003'] as $phone) {
            $this->assertTrue($s->check($phone)['success']);
        }
        $last = $s->check('01711000004');

        $this->assertSame(['TOKEN_A', 'TOKEN_A', 'TOKEN_B'], $this->tokensUsed());
        $this->assertFalse($last['success']);
        $this->assertStringContainsString("used today's searches", $last['message']);
        $this->assertSame(['a' => ['used' => 2, 'blocked' => null], 'b' => ['used' => 1, 'blocked' => null]], $s->usageToday());

        // Next day the limits start again.
        $this->travel(1)->days();
        $s->check('01711000005');
        $this->assertSame('TOKEN_A', $this->tokensUsed()[3]);
    }

    public function test_a_key_that_is_refused_is_paused_for_today_and_the_next_key_answers(): void
    {
        Http::fake(function (HttpRequest $r) {
            return str_contains($r->header('Authorization')[0], 'TOKEN_A')
                ? Http::response(['status' => 'error', 'message' => 'Daily limit exceeded.'], 429)
                : Http::response($this->ok());
        });
        $this->keys([
            ['id' => 'a', 'label' => 'Main', 'token' => 'TOKEN_A', 'limit' => null],
            ['id' => 'b', 'label' => 'Backup', 'token' => 'TOKEN_B', 'limit' => null],
        ]);
        $s = app(BdCourierService::class);

        $this->assertTrue($s->check('01711000001')['success']);
        $this->assertTrue($s->check('01711000002')['success']);

        $this->assertSame(['TOKEN_A', 'TOKEN_B', 'TOKEN_B'], $this->tokensUsed(), 'Key A is tried once, then skipped for the day');
        $this->assertStringContainsString('Daily limit exceeded', $s->usageToday()['a']['blocked']);
    }

    public function test_the_older_single_token_setting_still_works(): void
    {
        Http::fake(['api.bdcourier.com/courier-check' => Http::response($this->ok())]);
        Setting::put('bdcourier_api_token', 'LEGACY');

        $this->assertTrue(app(BdCourierService::class)->check('01711000001')['success']);
        $this->assertSame(['LEGACY'], $this->tokensUsed());
    }

    public function test_keys_are_saved_from_integrations_and_keep_their_usage_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::put('bdcourier_api_token', 'LEGACY');

        $this->actingAs($admin)->put(route('admin.integrations.update', 'couriers'), [
            'bdcourier_keys' => [
                ['id' => '', 'label' => 'Main', 'token' => 'TOKEN_A', 'limit' => '40'],
                ['id' => '', 'label' => '', 'token' => 'TOKEN_B', 'limit' => ''],
                ['id' => '', 'label' => 'Empty row', 'token' => '', 'limit' => '5'],
            ],
        ])->assertSessionHasNoErrors();

        $keys = app(BdCourierService::class)->keys();
        $this->assertCount(2, $keys);
        $this->assertSame(['Main', 'API key 2'], array_column($keys, 'label'));
        $this->assertSame([40, null], array_column($keys, 'limit'));
        $this->assertSame('', (string) setting('bdcourier_api_token'));

        // Saving again with the ids keeps them (so today's counts aren't lost).
        $this->actingAs($admin)->put(route('admin.integrations.update', 'couriers'), [
            'bdcourier_keys' => array_map(fn ($k) => $k + ['limit' => $k['limit'] ?? ''], $keys),
        ]);
        $this->assertSame(array_column($keys, 'id'), array_column(app(BdCourierService::class)->keys(), 'id'));

        $this->actingAs($admin)->get(route('admin.integrations.index'))->assertOk()->assertSee('Main')->assertSee('+ Add another key');
    }

    public function test_plan_check_for_a_chosen_key(): void
    {
        Http::fake(['api.bdcourier.com/my-plan' => Http::response(['data' => ['plan_name' => 'Starter', 'remaining_paid_calls' => 31]])]);
        $this->keys([['id' => 'a', 'label' => 'Main', 'token' => 'TOKEN_A'], ['id' => 'b', 'label' => 'Backup', 'token' => 'TOKEN_B']]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->getJson(route('admin.integrations.bdcourier-plan', ['key' => 'b']))->assertJson(['success' => true, 'remaining' => 31]);
        $this->assertSame(['TOKEN_B'], $this->tokensUsed());
    }
}
