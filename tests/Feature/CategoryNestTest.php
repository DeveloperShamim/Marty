<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryNestTest extends TestCase
{
    use RefreshDatabase;

    public function test_sub_categories_nest_even_when_the_driver_returns_parent_id_as_a_string(): void
    {
        $footwear = Category::create(['name' => 'Footwear', 'slug' => 'footwear', 'is_active' => true, 'position' => 0]);
        Category::create(['name' => 'Slides', 'slug' => 'slides', 'is_active' => true, 'position' => 0, 'parent_id' => $footwear->id]);

        // Like the live MySQL driver: ids come back as integers, parent_id as a string
        $all = Category::all()->each(fn ($c) => $c->setRawAttributes(['parent_id' => $c->getRawOriginal('parent_id') === null ? null : (string) $c->getRawOriginal('parent_id')] + $c->getAttributes()));

        $nested = Category::nest($all);

        $this->assertSame(['Footwear'], $nested->pluck('name')->all());
        $this->assertSame(['Slides'], $nested->first()->children->pluck('name')->all());
    }
}
