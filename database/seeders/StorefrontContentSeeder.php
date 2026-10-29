<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;

/** One-time transfer of the finalized page's editorial copy; never seeds products or reviews. */
class StorefrontContentSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::first();
        if (!$store || $store->content !== null) return;
        $content = json_decode(file_get_contents(__DIR__ . '/storefront-editorial.json'), true, 512, JSON_THROW_ON_ERROR);
        $content['benefits'] = [
            ['title' => 'Thoughtfully Chosen', 'subtitle' => 'Everyday Essentials'],
            ['title' => 'Soft & Gentle', 'subtitle' => 'Comfort Comes First'],
            ['title' => 'Little Adventures', 'subtitle' => 'Ready for Every Day'],
            ['title' => 'Gift-Ready Favorites', 'subtitle' => 'Made for Big Smiles'],
            ['title' => 'Zero Toxins', 'subtitle' => '100% Tested Safe'],
            ['title' => 'Fast & Free Shipping', 'subtitle' => 'Orders Over ₹999'],
        ];
        $content['instagram'] = array_map(fn ($i) => ['image' => "/assets/hd/insta$i.webp", 'title' => 'Little moments of baby joy', 'link' => '/community'], range(1, 6));
        $content['newborn'] = ['title' => 'Newborn Essentials', 'description' => "Everything designed for your baby's precious first moments & gentle care.", 'image' => '/assets/hd/newborn.webp', 'link' => '/age-newborn', 'cta' => 'Shop Newborn Bundle'];
        $content['app'] = ['title' => 'Shop Anytime, Anywhere', 'description' => 'Download the KIDOBOX app for exclusive perks & speedy delivery.', 'ios_link' => '/app', 'android_link' => '/app'];
        $content['copy'] = ['announcement' => 'Little Needs. Big Smiles.', 'instagram_handle' => '@kidobox', 'instagram_link' => '/community'];
        $content['hero_promises'] = [['title' => 'Safe & Gentle', 'subtitle' => 'Tested for delicate skin'], ['title' => 'Parent-Approved', 'subtitle' => 'Loved by parents'], ['title' => 'Certified Safe', 'subtitle' => 'Zero toxins & BPA free']];
        $store->update(['content' => $content]);
    }
}
