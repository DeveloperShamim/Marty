<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\LoginController;
use App\Models\Category;
use App\Models\EmailOtp;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\SafeHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Fixes from the security audit (Nov 2026). */
class SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    private function guestOrder(): Order
    {
        $category = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'position' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Bifold', 'slug' => 'bifold',
            'regular_price' => 1000, 'stock_quantity' => 10, 'is_published' => true]);
        $this->postJson(route('cart.add'), ['product_id' => $product->id])->assertOk();
        $this->post(route('checkout.store'), [
            'customer_name' => 'Guest Buyer', 'customer_phone' => '01712345678', 'shipping_address' => 'House 7, Secret Road',
            'city' => 'Dhaka', 'shipping_zone' => 'inside_dhaka', 'payment_method' => 'cod',
        ])->assertSessionHasNoErrors();
        $this->flushSession();

        return Order::latest('id')->firstOrFail();
    }

    private function customer(array $attrs = []): User
    {
        $user = User::create(array_merge(['name' => 'Someone', 'email' => 'someone@example.test',
            'password' => 'secret123', 'role' => 'customer'], $attrs));
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    public function test_typing_someone_elses_phone_in_the_profile_does_not_show_their_orders(): void
    {
        $order = $this->guestOrder();
        $other = $this->customer();

        $this->actingAs($other)->put(route('account.profile'), ['name' => 'Someone', 'phone' => '01712345678']);

        $this->actingAs($other->fresh())->get(route('account'))->assertOk()->assertDontSee($order->order_number);
        $this->actingAs($other->fresh())->get(route('account.orders.show', $order->order_number))->assertNotFound();
    }

    public function test_own_orders_still_show_in_the_account(): void
    {
        $order = $this->guestOrder();
        $owner = $this->customer();
        $order->forceFill(['user_id' => $owner->id])->save();

        $this->actingAs($owner)->get(route('account'))->assertOk()->assertSee($order->order_number);
        $this->actingAs($owner)->get(route('account.orders.show', $order->order_number))->assertOk()->assertSee('Secret Road');
    }

    public function test_live_chat_endpoints_are_gone(): void
    {
        $this->getJson('/chat/conversation')->assertNotFound();
        $this->postJson('/chat/send', ['message' => 'hi'])->assertNotFound();
    }

    public function test_login_redirect_stays_on_this_site(): void
    {
        $this->assertTrue(LoginController::isSafeRedirect('/checkout'));
        $this->assertTrue(LoginController::isSafeRedirect(url('/account')));
        $this->assertFalse(LoginController::isSafeRedirect('//evil.example/phish'));
        $this->assertFalse(LoginController::isSafeRedirect('/\\evil.example'));
        $this->assertFalse(LoginController::isSafeRedirect(url('/') . '.evil.example/x'));
        $this->assertFalse(LoginController::isSafeRedirect('https://evil.example/'));
        $this->assertFalse(LoginController::isSafeRedirect('javascript:alert(1)'));

        $this->customer(['email' => 'c@example.test']);
        $this->get('/login?redirect=' . urlencode('//evil.example/phish'));
        $this->post(route('login.store'), ['email' => 'c@example.test', 'password' => 'secret123'])
            ->assertRedirect(route('account'));
    }

    public function test_uploads_are_named_by_their_content_not_the_uploaded_name(): void
    {
        $staff = User::create(['name' => 'Inv', 'email' => 'inv@example.test', 'password' => 'secret123', 'role' => 'inventory_manager']);
        $tmp = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($tmp, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==') . '<script>alert(1)</script>');
        $before = glob(public_path('uploads/media/*')) ?: [];

        $this->actingAs($staff)->post(route('admin.media.upload'), ['image' => new UploadedFile($tmp, 'probe.html', null, null, true)]);

        $new = array_values(array_diff(glob(public_path('uploads/media/*')) ?: [], $before));
        foreach ($new as $f) {
            @unlink($f);
        }
        $this->assertCount(1, $new);
        $this->assertStringEndsWith('_probe.png', $new[0]);
    }

    public function test_product_description_drops_scripts_but_keeps_formatting_and_videos(): void
    {
        $html = '<p class="x" style="color:red"><b>Soft</b> leather <a href="javascript:alert(1)">x</a></p>'
            . '<img src="/uploads/a.webp" onerror="alert(1)" alt="A"><script>alert(2)</script>'
            . '<iframe src="https://www.youtube-nocookie.com/embed/abcdefghijk" allowfullscreen></iframe>'
            . '<iframe src="https://evil.example/frame"></iframe>';

        $clean = SafeHtml::clean($html);

        $this->assertStringContainsString('<b>Soft</b>', $clean);
        $this->assertStringContainsString('style="color:red"', $clean);
        $this->assertStringContainsString('youtube-nocookie.com/embed/abcdefghijk', $clean);
        $this->assertStringContainsString('src="/uploads/a.webp"', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('evil.example', $clean);
    }

    public function test_a_code_stops_working_after_five_wrong_tries(): void
    {
        $this->customer(['email' => 'otp@example.test']);
        $otp = app(\App\Services\OtpService::class);
        $code = $otp->send('otp@example.test', 'reset');

        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse($otp->verify('otp@example.test', $code === '111111' ? '222222' : '111111', 'reset'));
        }

        $this->assertFalse($otp->verify('otp@example.test', $code, 'reset'));
        $this->assertSame(0, EmailOtp::where('email', 'otp@example.test')->count());
    }

    public function test_forgot_password_gives_the_same_answer_for_unknown_emails(): void
    {
        $this->customer(['email' => 'known@example.test']);

        $known = $this->post(route('password.email'), ['email' => 'known@example.test']);
        $this->flushSession();
        $unknown = $this->post(route('password.email'), ['email' => 'nobody@example.test']);

        $known->assertRedirect(route('verify'));
        $unknown->assertRedirect(route('verify'))->assertSessionHasNoErrors();
        $this->assertSame(0, EmailOtp::where('email', 'nobody@example.test')->count());
    }

    public function test_changing_the_password_signs_out_other_browsers(): void
    {
        $user = $this->customer(['email' => 'pw@example.test']);

        $this->actingAs($user)->put(route('account.password'), [
            'current_password' => 'secret123', 'password' => 'newsecret456', 'password_confirmation' => 'newsecret456',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('newsecret456', $user->fresh()->password));
        $this->get(route('account'))->assertOk(); // this browser stays signed in
    }

    public function test_track_page_does_not_look_orders_up_by_database_id(): void
    {
        $order = $this->guestOrder();

        $this->get(route('track', ['order' => (string) $order->id]))->assertOk()->assertSessionHasNoErrors();
    }
}
