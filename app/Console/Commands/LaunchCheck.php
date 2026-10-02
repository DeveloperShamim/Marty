<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/** Pre-launch / health checklist for the live server: php artisan app:launch-check */
class LaunchCheck extends Command
{
    protected $signature = 'app:launch-check';

    protected $description = 'Check the server and store settings before (and after) going live';

    private array $rows = [];

    public function handle(): int
    {
        $url = (string) config('app.url');

        // Server & Laravel
        $this->check(app()->environment('production'), 'APP_ENV is production', 'Set APP_ENV=production in .env', 'fail');
        $this->check(! config('app.debug'), 'APP_DEBUG is off', 'Set APP_DEBUG=false: error pages would show code and secrets to visitors', 'fail');
        $this->check(str_starts_with($url, 'https://') && ! str_ends_with(parse_url($url, PHP_URL_HOST) ?? '', '.test'), "APP_URL is the live https address ({$url})", 'Set APP_URL=https://your-domain (used in emails, sitemap, webhooks)', 'fail');
        $this->check((bool) config('app.key'), 'APP_KEY is set', 'Run: php artisan key:generate', 'fail');
        $this->check(version_compare(PHP_VERSION, '8.3.0', '>='), 'PHP ' . PHP_VERSION, 'PHP 8.3 or newer is required', 'fail');
        $this->check(is_link(public_path('storage')), 'Storage link exists', 'Run: php artisan storage:link (uploaded images will not show)', 'fail');
        $this->check((bool) config('session.secure'), 'Session cookie is HTTPS-only', 'Set SESSION_SECURE_COOKIE=true', 'warn');
        $this->check(! in_array(config('logging.channels.' . config('logging.default') . '.level', config('logging.channels.single.level')), ['debug'], true), 'Log level is not debug', 'Set LOG_LEVEL=error (debug logs grow fast and may contain customer data)', 'warn');
        $this->check(! testing_mode(), 'Testing (read-only demo) mode is off', 'Remove TESTING_MODE from .env', 'fail');
        $beat = Cache::get('scheduler_heartbeat');
        $beat = is_int($beat) ? \Illuminate\Support\Carbon::createFromTimestamp($beat) : null;
        $this->check($beat && now()->diffInMinutes($beat, true) <= 5, 'Cron / scheduler is running', 'Add the cron job: * * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1 (courier sync, clean-ups)', 'warn');

        // Accounts
        $weak = User::whereIn('role', ['admin', 'store_manager', 'order_manager', 'inventory_manager'])->get()
            ->filter(fn ($u) => Hash::check('password', $u->password))->pluck('email');
        $this->check($weak->isEmpty(), 'No staff account uses the password "password"', 'Change the password of: ' . $weak->implode(', '), 'fail');
        $demo = User::where('email', 'like', '%@marty.com')
            ->orWhereIn('email', ['manager@vantbd.com', 'orders@vantbd.com', 'inventory@vantbd.com', 'customer@vantbd.com'])
            ->pluck('email');
        $this->check($demo->isEmpty(), 'No demo accounts', 'Delete or rename demo accounts: ' . $demo->implode(', '), 'warn');

        // Store settings
        foreach (['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket'] as $k => $name) {
            if (setting("pay_{$k}_enabled") === '1') {
                $n = preg_replace('/\D/', '', (string) setting("{$k}_number"));
                $this->check(strlen($n) === 11 && ! preg_match('/^01[789]0{8}$/', $n), "{$name} number set", "{$name} payments are on but the number is empty or a sample: customers would send money to the wrong number", 'fail');
            }
        }
        $phone = preg_replace('/\D/', '', (string) setting('contact_phone'));
        $this->check($phone !== '' && ! str_ends_with($phone, '1700000000'), 'Store phone number set', 'Set your real phone number in Settings', 'warn');
        $mailer = (string) setting('mail_mailer', config('mail.default'));
        $mailWorks = ! in_array($mailer, ['', 'log', 'array'], true);
        if (setting('otp_enabled') === '1') {
            $this->check($mailWorks, 'Email server set (needed for sign-up codes)', 'Sign-up codes are on but email is "' . $mailer . '": customers cannot register. Set up mail in Integrations or turn codes off', 'fail');
        } else {
            $this->check($mailWorks, 'Email server set', 'Email is "' . $mailer . '": no emails are sent', 'warn');
        }

        $this->table(['', 'Check', 'What to do'], $this->rows);
        $fails = collect($this->rows)->where(0, '✗')->count();
        $warns = collect($this->rows)->where(0, '!')->count();
        $fails ? $this->error("{$fails} problem(s) must be fixed before going live; {$warns} warning(s).") : $this->info("Ready. {$warns} warning(s).");

        return $fails ? self::FAILURE : self::SUCCESS;
    }

    private function check(bool $ok, string $label, string $fix, string $severity): void
    {
        $this->rows[] = [$ok ? '✓' : ($severity === 'fail' ? '✗' : '!'), $label, $ok ? '' : $fix];
    }
}
