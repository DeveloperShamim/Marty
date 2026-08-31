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

        $data = $request->validate([
            'author_name'  => [$user ? 'nullable' : 'required', 'string', 'max:120'],
            'author_email' => [$user ? 'nullable' : 'required', 'email', 'max:120'],
            'rating'       => ['required', 'integer', 'min:1', 'max:5'],
            'title'        => ['nullable', 'string', 'max:180'],
            'body'         => ['required', 'string', 'max:2000'],
        ]);

        $authorEmail = $user ? ($data['author_email'] ?? $user->email) : $data['author_email'];
        $authorName  = $user ? ($data['author_name'] ?: $user->name) : $data['author_name'];

        $already = ProductReview::query()
            ->where('product_id', $product->id)
            ->where(function ($q) use ($user, $authorEmail) {
                if ($user) {
                    $q->where('user_id', $user->id);
                } elseif ($authorEmail) {
                    $q->where('author_email', $authorEmail);
                }
            })
            ->whereIn('status', [ProductReview::STATUS_PENDING, ProductReview::STATUS_APPROVED])
            ->exists();

        if ($already) {
            return back()
                ->withInput()
                ->withErrors(['review_body' => 'You have already submitted a review for this product.'])
                ->withFragment('reviews');
        }

        $verified = false;
        if ($user) {
            $verified = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($q) use ($user) {
                    $q->where(function ($w) use ($user) {
                        $w->where('user_id', $user->id);
                        if ($user->email) {
                            $w->orWhere('customer_email', $user->email);
                        }
                        if ($user->phone) {
                            $w->orWhere('customer_phone', $user->phone);
                        }
                    })->whereNotIn('status', ['cancelled']);
                })
                ->exists();
        } elseif ($authorEmail) {
            $verified = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($q) use ($authorEmail) {
                    $q->where('customer_email', $authorEmail)
                        ->whereNotIn('status', ['cancelled']);
                })
                ->exists();
        }

        // STRICT ENFORCEMENT: Only buyers who purchased this product can rate
        if (! $verified) {
            return back()
                ->withInput()
                ->withErrors(['review_body' => 'Only customers who have purchased this product can leave a review.'])
                ->withFragment('reviews');
        }

        ProductReview::create([
            'product_id'           => $product->id,
            'user_id'              => $user?->id,
            'author_name'          => $authorName,
            'author_email'         => $authorEmail,
            'rating'               => $data['rating'],
            'title'                => $data['title'] ?? null,
            'body'                 => $data['body'],
            'status'               => ProductReview::STATUS_PENDING,
            'is_verified_purchase' => true,
        ]);

        return back()
            ->with('status', 'Thank you! Your feedback has been submitted for review.')
            ->withFragment('reviews');
    }
}
