<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactInquiry;
use App\Models\LaptopHireRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $canViewOperations = in_array(auth()->user()->admin_role, ['owner', 'manager', 'operations'], true);
        $metrics = [
            'products' => Product::query()->count(),
            'categories' => Category::query()->count(),
            'brands' => Brand::query()->count(),
            'open_orders' => $canViewOperations ? Order::query()->whereIn('status', ['awaiting_confirmation', 'confirmed', 'processing'])->count() : null,
            'orders_today' => $canViewOperations ? Order::query()->whereDate('created_at', Carbon::today())->count() : null,
            'pending_hire' => $canViewOperations ? LaptopHireRequest::query()->where('status', 'pending')->count() : null,
            'new_enquiries' => $canViewOperations ? ContactInquiry::query()->where('status', 'new')->count() : null,
        ];
        $recentOrders = $canViewOperations ? Order::query()->latest()->limit(8)->get() : collect();

        return view('admin.dashboard', compact('metrics', 'recentOrders', 'canViewOperations'));
    }
}
