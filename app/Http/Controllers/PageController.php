<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function terms(): View
    {
        return view('storefront.page', [
            'title'   => 'Terms of Service',
            'heading' => 'Terms of Service',
            'body'    => setting('terms_content') ?: $this->defaultTerms(),
        ]);
    }

    public function privacy(): View
    {
        return view('storefront.page', [
            'title'   => 'Privacy Policy',
            'heading' => 'Privacy Policy',
            'body'    => setting('privacy_content') ?: $this->defaultPrivacy(),
        ]);
    }

    public function contact(): View
    {
        return view('storefront.contact', [
            'title' => 'Contact',
        ]);
    }

    /**
     * Generic CMS-style page by slug (contact redirects to dedicated view).
     */
    public function show(string $slug): View
    {
        if ($slug === 'contact') {
            return $this->contact();
        }

        if ($slug === 'terms') {
            return $this->terms();
        }

        if ($slug === 'privacy') {
            return $this->privacy();
        }

        abort(404);
    }

    private function defaultTerms(): string
    {
        $site = site_name();

        return "Welcome to {$site}. By placing an order you agree to provide accurate delivery and payment details, accept our shipping timelines, and understand that product availability may change. Orders may be cancelled if payment verification fails. For returns and support, contact us using the details on this website.";
    }

    private function defaultPrivacy(): string
    {
        $site = site_name();

        return "{$site} collects account, order, and delivery information needed to fulfill purchases. We do not sell your personal data. Payment transaction IDs for mobile banking are stored only to verify your order. Contact us to request account updates or deletion where applicable.";
    }
}
