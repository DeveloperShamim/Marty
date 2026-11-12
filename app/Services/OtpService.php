<?php

namespace App\Services;

use App\Models\EmailOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    private const TTL_MINUTES = 10;

    /** Wrong codes allowed per code before it stops working. */
    private const MAX_ATTEMPTS = 5;

    /**
     * Generate a fresh 6-digit code, store it (hashed) and email it.
     * Returns the plain code (used only to reveal it on screen in the local dev env).
     */
    public function send(string $email, string $purpose = 'register', ?string $name = null): string
    {
        $code = (string) random_int(100000, 999999);

        // Invalidate any outstanding codes for this email + purpose, then store the new one.
        EmailOtp::where('email', $email)->where('purpose', $purpose)->whereNull('consumed_at')->delete();
        EmailOtp::create([
            'email'      => $email,
            'code'       => Hash::make($code),
            'purpose'    => $purpose,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        $this->mail($email, $code, $name);

        return $code;
    }

    /** Verify a submitted code for the given email + purpose. Consumes it on success. */
    public function verify(string $email, string $code, string $purpose = 'register'): bool
    {
        $otp = EmailOtp::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if (! $otp || ! $otp->isValid()) {
            return false;
        }

        // Each code allows a few wrong tries, from any number of IP addresses; then it is thrown away
        // and the customer asks for a new one. Stops guessing the 6 digits from many machines.
        $failKey = 'otp-fail:' . $otp->id;
        if (! Hash::check($code, $otp->code)) {
            if (RateLimiter::hit($failKey, self::TTL_MINUTES * 60) >= self::MAX_ATTEMPTS) {
                $otp->delete();
                RateLimiter::clear($failKey);
            }

            return false;
        }

        RateLimiter::clear($failKey);
        $otp->update(['consumed_at' => now()]);

        return true;
    }

    private function mail(string $email, string $code, ?string $name): void
    {
        $siteName = site_name();

        try {
            configure_mail_from_settings();
            Mail::send('emails.otp', [
                'code'     => $code,
                'name'     => $name,
                'siteName' => $siteName,
                'minutes'  => self::TTL_MINUTES,
            ], function ($message) use ($email, $siteName) {
                $message->to($email)->subject("Your {$siteName} verification code");
            });
        } catch (\Throwable $e) {
            // Never let a mail transport failure break the user flow; log it so the admin can fix SMTP.
            Log::warning('OTP email failed to send: ' . $e->getMessage());
        }
    }
}
