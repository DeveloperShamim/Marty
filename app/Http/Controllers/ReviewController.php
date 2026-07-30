<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        abort_unless($product->is_published, 404);

        $user = $request->user();
        if (! $user) {
            return redirect()
                ->guest(route('login', ['redirect' => route('product.show', $product) . '#reviews']))
                ->with('status', 'Please sign in to write a review.');
        }

        $data = $request->validate([
            'author_name'  => ['nullable', 'string', 'max:120'],
            'author_email' => ['nullable', 'email', 'max:120'],
            'rating'       => ['required', 'integer', 'min:1', 'max:5'],
            'title'        => ['nullable', 'string', 'max:180'],
            'body'         => ['required', 'string', 'max:2000'],
        ]);

        $already = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [ProductReview::STATUS_PENDING, ProductReview::STATUS_APPROVED])
            ->exists();

        if ($already) {
            return back()
                ->withInput()
                ->withErrors(['body' => 'You have already reviewed this product.'])
                ->withFragment('reviews');
        }

        $verified = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereNotIn('status', ['cancelled']);
            })
            ->exists();

        ProductReview::create([
            'product_id'           => $product->id,
            'user_id'              => $user->id,
            'author_name'          => $data['author_name'] ?: $user->name,
            'author_email'         => $data['author_email'] ?? $user->email,
            'rating'               => $data['rating'],
            'title'                => $data['title'] ?? null,
            'body'                 => $data['body'],
            'status'               => ProductReview::STATUS_PENDING,
            'is_verified_purchase' => $verified,
        ]);

        return redirect()
            ->route('product.show', $product)
            ->with('status', 'Thanks! Your review was submitted and is awaiting approval.')
            ->withFragment('reviews');
    }
}
