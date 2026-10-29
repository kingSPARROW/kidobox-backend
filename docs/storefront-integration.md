# Storefront integration

The existing Next.js storefront now reads Laravel/MongoDB data. Its stylesheets, responsive classes and carousel interactions remain in place. There is no static product fallback.

## Content ownership

| Storefront content | Existing admin area / data |
| --- | --- |
| Product cards, detail pages, images, prices, variants and stock | Products / Product + ProductVariant |
| Category strip, directories, category banners and product membership | Collections / Collection + Product.collection_ids |
| Trending / featured products | Products, `featured` tag |
| Bestsellers | Products, `bestseller` tag (case-insensitive) |
| New arrivals | Products, `new-arrival` tag |
| Age filtering / product highlights | Products, `age:0-6` and `feature:Your text` tags |
| Brand directory and filters | Product vendor |
| Hero carousel | Banners, `hero` position |
| Promotional cards | Banners, `promotional_strip` position |
| Full-width campaign | Banners, `footer_banner` position, or Content newborn panel |
| Offers | Discounts, only active/date-valid/not-exhausted discounts |
| Reviews and product rating summaries | Testimonials; product summaries match `product_title` |
| Age cards, guides, Instagram tiles, benefits, hero promises, newborn/app text | Content / Store.content |
| Header menus, footer copy, columns, social URLs | Navigation / Menu + Store.settings.footer |
| Logo and shipping threshold | Settings / Store.branding + Store.settings |

The shared React Query layer refreshes on focus, mount, every 15 seconds while visible, and immediately after same-browser admin writes. Separate tabs receive a storage event. Public product responses omit internal variant costs. Removed or draft product URLs no longer substitute another product. Unknown/unpublished category API filters return an empty list.

## Database update and rollout

1. Configure the backend `.env`, application key and MongoDB connection. The checked-out workspace has no `.env`. Its default connection expects replica set `rs0`; use the actual deployment topology.
2. Install the locked dependencies with PHP's MongoDB extension **2.5 or later**. This machine has 2.3.3; dependencies were installed with a temporary platform-check bypass for validation. The dependency lockfiles were not changed.
3. Run the targeted data migration:

   ```sh
   php artisan migrate --path=database/migrations/2026_10_29_120000_normalize_product_catalog_arrays.php
   ```

   It converts legacy JSON strings in `products.tags` and `products.collection_ids` to native MongoDB arrays. It is idempotent and preserves values. The new cast reads both formats, but database membership queries require this migration for legacy records. Rollback deliberately does not restore the broken string format.
4. Optionally transfer the finalized page's existing editorial content once:

   ```sh
   php artisan db:seed --class=StorefrontContentSeeder
   ```

   This only fills a store whose `content` field is absent. It never overwrites saved content or creates products, discounts, reviews or customers. Store.content is an additive MongoDB document field and needs no schema migration.
5. Run `php artisan storage:link` for uploaded images, set `APP_URL` to the accessible backend origin, and set the frontend's `NEXT_PUBLIC_API_URL` to its `/api/v1` URL. Configure CORS for the frontend origin.
6. Use the existing admin editors to publish categories, assign real products and merchandising tags, and configure banners/navigation/content. Empty sections stay empty rather than showing demo inventory.

Neither migration nor editorial transfer was applied to the existing store database during this task. Integration tests use randomly named `kidobox_integration_*` databases and remove only those databases.

## Validation

- `npm run build`: passed, including all 67 static pages. Existing warning: `metadataBase` defaults to localhost.
- `npx tsc --noEmit`: passed.
- `node --test tests/storefront.test.cjs`: three passing tests covering catalog mapping, real variant IDs/prices, stock guards, and media/link handling.
- `php artisan test --filter=StorefrontIntegrationTest`: five tests, 53 assertions passed. Covers product create/edit/delete, images/pricing/stock, publication, category edit/delete, variant reconciliation, price sorting, scheduled banners, testimonial deletion, discount exhaustion, validation, and migration idempotency.
- Backend test warnings are from the missing local `.env`, not failed assertions. Tests use a direct connection to local MongoDB rather than the unavailable default replica-set configuration.
- `git diff --check`: passed for both repositories.

Browser visual comparison, production data migration, production deployment, and payment-provider testing were not performed. The storefront endpoint currently loads the complete active catalog to preserve the existing client-side filtering; server-side pagination should be introduced for large catalogs.

## Modified files

### kidobox-frontend

- `src/app/admin/collections/page.tsx`
- `src/app/admin/content/page.tsx`
- `src/app/ages/page.tsx`
- `src/app/brands/page.tsx`
- `src/app/categories/page.tsx`
- `src/app/collections/[slug]/page.tsx`
- `src/app/collections/page.tsx`
- `src/app/community/page.tsx`
- `src/app/guides/[slug]/page.tsx`
- `src/app/guides/page.tsx`
- `src/app/offers/page.tsx`
- `src/app/page.tsx`
- `src/app/products/[slug]/page.tsx`
- `src/app/reviews/page.tsx`
- `src/app/sitemap.ts`
- `src/app/wishlist/page.tsx`
- `src/components/admin/shopify-product-form.tsx`
- `src/components/catalog-view.tsx`
- `src/components/hero-slider.tsx`
- `src/components/lazy-image.tsx`
- `src/components/product-slider.tsx`
- `src/components/reviews-slider.tsx`
- `src/components/store-footer.tsx`
- `src/components/store-nav.tsx`
- `src/lib/api.ts`
- `src/lib/kidobox-data.ts`
- `src/lib/providers.tsx`
- `src/lib/storefront.ts`
- `tests/storefront.test.cjs`

### kidobox-backend

- `app/Http/Controllers/Api/V1/BannerController.php`
- `app/Http/Controllers/Api/V1/CollectionController.php`
- `app/Http/Controllers/Api/V1/NavigationController.php`
- `app/Http/Controllers/Api/V1/ProductController.php`
- `app/Http/Controllers/Api/V1/StorefrontController.php`
- `app/Http/Controllers/Api/V1/TestimonialController.php`
- `app/Http/Middleware/IdentifyStore.php`
- `app/Models/Banner.php`
- `app/Models/Product.php`
- `app/Models/Store.php`
- `routes/api.php`
- `app/Casts/NativeArray.php`
- `app/Http/Controllers/Api/V1/ContentController.php`
- `app/Http/Resources/StorefrontProduct.php`
- `database/migrations/2026_10_29_120000_normalize_product_catalog_arrays.php`
- `database/seeders/StorefrontContentSeeder.php`
- `database/seeders/storefront-editorial.json`
- `docs/storefront-integration.md`
- `tests/Feature/StorefrontIntegrationTest.php`
