<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class ContentController extends Controller
{
    public function show()
    {
        return response()->json(['success' => true, 'data' => app('current_store')->content ?? (object) []]);
    }

    public function update(Request $request)
    {
        $rules = ['content' => 'required|array:ages,articles,instagram,benefits,newborn,app,hero_promises,copy'];
        $schemas = [
            'ages' => ['slug', 'title', 'subtitle', 'image', 'age', 'color', 'description'],
            'articles' => ['slug', 'title', 'category', 'image', 'excerpt'],
            'instagram' => ['image', 'title', 'link'],
            'benefits' => ['title', 'subtitle'],
            'hero_promises' => ['title', 'subtitle'],
        ];
        foreach ($schemas as $section => $fields) {
            $rules["content.$section"] = 'sometimes|array|max:100';
            $rules["content.$section.*"] = 'required|array';
            foreach ($fields as $field) {
                $rules["content.$section.*.$field"] = 'present|nullable|string|max:10000';
            }
        }
        $rules['content.articles.*.minutes'] = 'required|integer|min:1|max:999';
        $rules['content.articles.*.sections'] = 'required|array';
        $rules['content.articles.*.sections.*'] = 'array|size:2';
        $rules['content.articles.*.sections.*.*'] = 'string|max:20000';
        foreach (['newborn', 'app', 'copy'] as $section) {
            $rules["content.$section"] = 'sometimes|array';
            $rules["content.$section.*"] = 'nullable|string|max:10000';
        }
        $validated = $request->validate($rules);
        $store = app('current_store');
        $store->update(['content' => $validated['content']]);
        AuditLog::record('storefront_content_updated');
        return response()->json(['success' => true, 'data' => $store->fresh()->content]);
    }
}
