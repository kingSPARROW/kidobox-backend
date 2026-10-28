<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->with('addresses');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) ($request->query('per_page', 15)), 100);
        $customers = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $customer = Customer::with(['addresses', 'orders'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'accepts_marketing' => 'nullable|boolean',
        ]);

        $customer = Customer::create(array_merge($validated, [
            'total_orders' => 0,
            'total_spent' => 0.0,
        ]));

        AuditLog::record('customer_created', ['customer_id' => (string) $customer->_id, 'email' => $customer->email]);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully.',
            'customer' => $customer,
        ], 201);
    }
}
