<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryMovement;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * List products with search, status filters, and pagination
     */
    public function index(Request $request)
    {
        $query = Product::query()->with('variants');

        // Search by title or tag
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%")
                  ->orWhere('tags', $search);
            });
        }

        // Filter by status
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Filter by collection
        if ($collectionId = $request->query('collection_id')) {
            $query->where('collection_ids', $collectionId);
        }

        $perPage = min((int) ($request->query('per_page', 15)), 100);
        $products = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Create product with variants and initial inventory
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vendor' => 'nullable|string|max:100',
            'product_type' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'status' => 'required|in:draft,active,archived',
            'images' => 'nullable|array',
            'options' => 'nullable|array',
            'has_variants' => 'nullable|boolean',
            'hsn_code' => 'nullable|string|max:20',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'seo' => 'nullable|array',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'collection_ids' => 'nullable|array',
            // Default single-variant fields or array of variants
            'price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_per_item' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'inventory_quantity' => 'nullable|integer|min:0',
            'variants' => 'nullable|array',
        ]);

        $baseSlug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $seoTitle = $validated['seo_title'] ?? ($validated['seo']['title'] ?? $validated['title']);
        $seoDesc = $validated['seo_description'] ?? ($validated['seo']['description'] ?? Str::limit(strip_tags($validated['description'] ?? ''), 160));

        $product = Product::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? '',
            'vendor' => $validated['vendor'] ?? '',
            'product_type' => $validated['product_type'] ?? 'Standard Product',
            'tags' => $validated['tags'] ?? [],
            'status' => $validated['status'],
            'images' => $validated['images'] ?? [],
            'options' => $validated['options'] ?? [],
            'has_variants' => $validated['has_variants'] ?? (!empty($validated['variants'])),
            'hsn_code' => $validated['hsn_code'] ?? '9403', // Homeware default HSN
            'tax_rate' => $validated['tax_rate'] ?? 18.0,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDesc,
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDesc,
            ],
            'collection_ids' => $validated['collection_ids'] ?? [],
        ]);

        // Create variants
        if (!empty($validated['variants'])) {
            foreach ($validated['variants'] as $vData) {
                $variant = ProductVariant::create([
                    'product_id' => (string) $product->_id,
                    'title' => $vData['title'] ?? 'Default',
                    'sku' => $vData['sku'] ?? (strtoupper(Str::random(8))),
                    'barcode' => $vData['barcode'] ?? null,
                    'price' => (float) ($vData['price'] ?? 0),
                    'compare_at_price' => isset($vData['compare_at_price']) ? (float) $vData['compare_at_price'] : null,
                    'cost_per_item' => isset($vData['cost_per_item']) ? (float) $vData['cost_per_item'] : null,
                    'inventory_quantity' => (int) ($vData['inventory_quantity'] ?? 0),
                    'weight' => (float) ($vData['weight'] ?? 0.5),
                    'weight_unit' => $vData['weight_unit'] ?? 'kg',
                    'options_map' => $vData['options_map'] ?? [],
                    'image_url' => $vData['image_url'] ?? ($product->images[0] ?? null),
                ]);

                if ($variant->inventory_quantity > 0) {
                    InventoryMovement::create([
                        'variant_id' => (string) $variant->_id,
                        'delta' => $variant->inventory_quantity,
                        'resulting_quantity' => $variant->inventory_quantity,
                        'reason' => 'initial_stock_creation',
                        'reference_type' => 'manual',
                    ]);
                }
            }
        } else {
            // Create single default variant
            $initialQty = (int) ($validated['inventory_quantity'] ?? 10);
            $variant = ProductVariant::create([
                'product_id' => (string) $product->_id,
                'title' => 'Default Title',
                'sku' => $validated['sku'] ?? ('SKU-' . strtoupper(Str::random(6))),
                'barcode' => $validated['barcode'] ?? null,
                'price' => (float) ($validated['price'] ?? 0),
                'compare_at_price' => isset($validated['compare_at_price']) ? (float) $validated['compare_at_price'] : null,
                'cost_per_item' => isset($validated['cost_per_item']) ? (float) $validated['cost_per_item'] : null,
                'inventory_quantity' => $initialQty,
                'weight' => 0.5,
                'weight_unit' => 'kg',
                'options_map' => [],
                'image_url' => $product->images[0] ?? null,
            ]);

            if ($initialQty > 0) {
                InventoryMovement::create([
                    'variant_id' => (string) $variant->_id,
                    'delta' => $initialQty,
                    'resulting_quantity' => $initialQty,
                    'reason' => 'initial_stock_creation',
                    'reference_type' => 'manual',
                ]);
            }
        }

        AuditLog::record('product_created', ['product_id' => (string) $product->_id, 'title' => $product->title]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'product' => $product->load('variants'),
        ], 201);
    }

    /**
     * Show single product details with variants
     */
    public function show($id)
    {
        $product = Product::with('variants')->findOrFail($id);

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    /**
     * Update product and variants
     */
    public function update(Request $request, $id)
    {
        $product = Product::with('variants')->findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'vendor' => 'nullable|string|max:100',
            'product_type' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'status' => 'nullable|in:draft,active,archived',
            'images' => 'nullable|array',
            'options' => 'nullable|array',
            'has_variants' => 'nullable|boolean',
            'hsn_code' => 'nullable|string|max:20',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'collection_ids' => 'nullable|array',
            'slug' => 'nullable|string|max:255',
            'variants' => 'nullable|array',
            // Flat single-variant fallback fields
            'price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_per_item' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'inventory_quantity' => 'nullable|integer|min:0',
        ]);

        $updateData = array_filter($validated, fn($key) => !in_array($key, [
            'variants', 'price', 'compare_at_price', 'cost_per_item', 'sku', 'barcode', 'inventory_quantity'
        ]), ARRAY_FILTER_USE_KEY);

        if (isset($validated['slug'])) {
            $rawSlug = !empty($validated['slug']) ? $validated['slug'] : $product->title;
            $baseSlug = Str::slug($rawSlug);
            $slug = $baseSlug;
            $counter = 1;
            while (Product::where('slug', $slug)->where('_id', '!=', $product->_id)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
            $updateData['slug'] = $slug;
        }

        if (!empty($validated['seo_title']) || !empty($validated['seo_description'])) {
            $updateData['seo'] = [
                'title' => $validated['seo_title'] ?? $product->seo_title,
                'description' => $validated['seo_description'] ?? $product->seo_description,
            ];
        }

        $product->update($updateData);

        if (!empty($validated['variants'])) {
            foreach ($validated['variants'] as $vData) {
                if (!empty($vData['id']) || !empty($vData['_id'])) {
                    $variantId = $vData['id'] ?? $vData['_id'];
                    $variant = ProductVariant::find($variantId);
                    if ($variant && (string) $variant->product_id === (string) $product->_id) {
                        $variant->update($vData);
                    }
                } else {
                    ProductVariant::create(array_merge($vData, ['product_id' => (string) $product->_id]));
                }
            }
        } elseif (isset($validated['price']) || isset($validated['sku']) || isset($validated['inventory_quantity'])) {
            $firstVariant = $product->variants->first();
            if ($firstVariant) {
                $variantUpdate = [];
                if (isset($validated['price'])) $variantUpdate['price'] = (float) $validated['price'];
                if (isset($validated['compare_at_price'])) $variantUpdate['compare_at_price'] = $validated['compare_at_price'] ? (float) $validated['compare_at_price'] : null;
                if (isset($validated['cost_per_item'])) $variantUpdate['cost_per_item'] = $validated['cost_per_item'] ? (float) $validated['cost_per_item'] : null;
                if (isset($validated['sku'])) $variantUpdate['sku'] = $validated['sku'];
                if (isset($validated['barcode'])) $variantUpdate['barcode'] = $validated['barcode'];
                if (isset($validated['inventory_quantity'])) $variantUpdate['inventory_quantity'] = (int) $validated['inventory_quantity'];
                if (!empty($validated['images'][0])) $variantUpdate['image_url'] = $validated['images'][0];

                $firstVariant->update($variantUpdate);
            }
        }

        AuditLog::record('product_updated', ['product_id' => (string) $product->_id, 'title' => $product->title]);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'product' => $product->fresh()->load('variants'),
        ]);
    }

    /**
     * Upload Product Image / Media
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:8192',
        ]);

        $path = $request->file('image')->store('products', 'public');
        $url = asset('storage/' . $path);

        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path,
        ]);
    }

    /**
     * Adjust Variant Inventory
     */
    public function adjustStock(Request $request, $variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);

        $validated = $request->validate([
            'adjustment' => 'required|integer', // e.g. +5 or -2
            'reason' => 'required|string', // e.g. 'restock', 'cycle_count', 'damaged'
        ]);

        $newQty = max(0, $variant->inventory_quantity + $validated['adjustment']);
        $variant->update(['inventory_quantity' => $newQty]);

        InventoryMovement::create([
            'variant_id' => (string) $variant->_id,
            'delta' => $validated['adjustment'],
            'resulting_quantity' => $newQty,
            'reason' => $validated['reason'],
            'reference_type' => 'manual',
        ]);

        AuditLog::record('inventory_adjusted', [
            'variant_id' => (string) $variant->_id,
            'delta' => $validated['adjustment'],
            'new_quantity' => $newQty,
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully.',
            'variant' => $variant->fresh(),
        ]);
    }

    /**
     * Delete Product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        ProductVariant::where('product_id', (string) $product->_id)->delete();
        $product->delete();

        AuditLog::record('product_deleted', ['product_id' => $id, 'title' => $product->title]);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Export products to CSV
     */
    public function exportCsv()
    {
        $products = Product::with('variants')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_export_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            // CSV Header Row
            fputcsv($handle, [
                'Title',
                'Vendor',
                'Product Type',
                'Status',
                'Variant Title',
                'SKU',
                'Price',
                'Compare At Price',
                'Stock',
                'HSN Code',
                'Tax Rate',
                'Tags',
                'Description',
            ]);

            foreach ($products as $p) {
                if ($p->variants && count($p->variants) > 0) {
                    foreach ($p->variants as $v) {
                        fputcsv($handle, [
                            $p->title,
                            $p->vendor,
                            $p->product_type,
                            $p->status,
                            $v->title,
                            $v->sku,
                            $v->price,
                            $v->compare_at_price,
                            $v->inventory_quantity,
                            $p->hsn_code,
                            $p->tax_rate,
                            is_array($p->tags) ? implode(', ', $p->tags) : $p->tags,
                            $p->description,
                        ]);
                    }
                } else {
                    fputcsv($handle, [
                        $p->title,
                        $p->vendor,
                        $p->product_type,
                        $p->status,
                        'Default Variant',
                        '',
                        0,
                        0,
                        0,
                        $p->hsn_code,
                        $p->tax_rate,
                        is_array($p->tags) ? implode(', ', $p->tags) : $p->tags,
                        $p->description,
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import products from CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:csv,txt|max:5120',
            'csv_data' => 'nullable|string',
        ]);

        $rows = [];
        if ($request->hasFile('file')) {
            $path = $request->file('file')->getRealPath();
            $fileData = file_get_contents($path);
            $lines = explode("\n", trim($fileData));
            $rows = array_map('str_getcsv', $lines);
        } elseif ($request->filled('csv_data')) {
            $lines = explode("\n", trim($request->input('csv_data')));
            $rows = array_map('str_getcsv', $lines);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a CSV file or csv_data string.',
            ], 422);
        }

        if (count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'CSV file is empty or missing headers.',
            ], 422);
        }

        $header = array_shift($rows);
        $header = array_map('trim', array_map('strtolower', $header));

        $importedCount = 0;
        foreach ($rows as $row) {
            if (empty($row) || count($row) < 3) continue;
            
            $data = array_combine(array_slice($header, 0, count($row)), $row);

            $title = $data['title'] ?? 'Imported Product';
            $vendor = $data['vendor'] ?? 'General';
            $productType = $data['product type'] ?? $data['product_type'] ?? 'Standard Product';
            $status = in_array(strtolower($data['status'] ?? ''), ['active', 'draft', 'archived']) 
                ? strtolower($data['status']) 
                : 'active';
            $variantTitle = $data['variant title'] ?? $data['variant_title'] ?? 'Default';
            $sku = $data['sku'] ?? ('SKU-' . strtoupper(Str::random(6)));
            $price = floatval($data['price'] ?? 0);
            $comparePrice = floatval($data['compare at price'] ?? $data['compare_at_price'] ?? 0);
            $stock = intval($data['stock'] ?? $data['inventory'] ?? 10);
            $hsn = $data['hsn code'] ?? $data['hsn'] ?? '9403';
            $taxRate = floatval($data['tax rate'] ?? $data['tax_rate'] ?? 18);
            $desc = $data['description'] ?? '';
            $tags = !empty($data['tags']) ? array_map('trim', explode(',', $data['tags'])) : ['imported'];

            $product = Product::firstOrCreate(
                ['title' => $title],
                [
                    'slug' => Str::slug($title) . '-' . strtolower(Str::random(4)),
                    'vendor' => $vendor,
                    'product_type' => $productType,
                    'status' => $status,
                    'hsn_code' => $hsn,
                    'tax_rate' => $taxRate,
                    'description' => $desc,
                    'tags' => $tags,
                    'images' => ['https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80'],
                    'has_variants' => true,
                ]
            );

            ProductVariant::updateOrCreate(
                ['product_id' => (string) $product->_id, 'sku' => $sku],
                [
                    'title' => $variantTitle,
                    'price' => $price,
                    'compare_at_price' => $comparePrice,
                    'inventory_quantity' => $stock,
                    'barcode' => 'BC-' . strtoupper(Str::random(6)),
                ]
            );

            $importedCount++;
        }

        AuditLog::record('products_imported_csv', ['count' => $importedCount]);

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$importedCount} product row(s).",
            'count' => $importedCount,
        ]);
    }
}
