<?php

namespace App\Http\Controllers\Website\Customer;

use App\Http\Controllers\Controller;
use App\Notifications\AdminUserMessageNotification;
use Illuminate\Http\Request;
use App\Models\{Order, Category};
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller {
    public function index() {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->get();
        $adminMessages = collect();
        try {
            $adminMessages = $user->notifications()
                ->where('type', AdminUserMessageNotification::class)
                ->latest()
                ->limit(8)
                ->get();
        } catch (\Throwable $e) {
            // notifications table might be unavailable on some deployments
            $adminMessages = collect();
        }
        $categories = Category::with(['translations', 'media', 'children.translations'])
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->get();
        $data = [
            'total'      => $orders->count(),
            'pending'    => $orders->where('status', 'pending')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
            'delivering' => $orders->where('status', 'delivering')->count(),
            'completed'  => $orders->where('status', 'completed')->count(),
            'canceled'   => $orders->where('status', 'canceled')->count(),
            'refunded'   => $orders->where('status', 'refunded')->count(),
            'pageTitle'  => $user?->name . ' | Dashboard',
        ];
        return view('website.customer.dashboard', compact('data', 'user', 'categories', 'adminMessages'));
    }

    public function ordersByStatus(Request $request) {
        $status = $request->get('status');
        $query = Order::query()->where('user_id', auth()->id());
        if ($status && $status != 'total') {
            $query->where('status', $status);
        }
        $orders = $query->with(['coupon', 'products'])->latest()->paginate(5);
        return response()->json([
            'data' => $orders->items(),
            'links' => (string) $orders->links('pagination::bootstrap-5'),
        ]);
    }

    public function showPartial(Order $order) {
        $order->load([
            'user',
            'coupon',
            'addresses',
            'products.category',
            'products.brand',
            'products.sections'
        ]);

        $statusColors = [
            'pending'     => ['text' => 'قيد الانتظار', 'color' => 'secondary'],
            'processing'  => ['text' => 'جارِ التجهيز', 'color' => 'info'],
            'delivering'  => ['text' => 'جارِ التوصيل', 'color' => 'warning'],
            'completed'   => ['text' => 'مكتمل', 'color' => 'success'],
        ];

        return response()->json([
            'html' => view('website.customer.orders_details', [
                'order' => $order,
                'statusColors' => $statusColors,
                'pageTitle' => 'تفاصيل الطلب'
            ])->render()
        ]);
    }

    /*public function trackOrder(Request $request) {
        $order = Order::where('number', $request->number)->first();
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الطلب غير موجود'
            ], 404);
        }
        $statusColors = [
            'pending' => 'bg-warning',
            'processing' => 'bg-primary',
            'delivering' => 'bg-info',
            'completed' => 'bg-success',
        ];
        $statusTexts = [
            'pending' => 'قيد الانتظار',
            'processing' => 'جاري التجهيز',
            'delivering' => 'جاري التوصيل',
            'completed' => 'تم التسليم',
        ];
        $color = $statusColors[$order->status] ?? 'bg-secondary';
        $statusText = $statusTexts[$order->status] ?? 'غير معروف';
        $html = view('website.customer.order_status_card', compact('order', 'color', 'statusText'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
        
    }*/
    public function trackOrder(Request $request) {
        $request->validate([
            'number' => 'required',
        ]);
        $order = Order::where('number', $request->number)
            ->when(auth()->check(), fn($q) => $q->where('user_id', auth()->id()))
            ->with([
                'user',
                'coupon',
                'addresses',
                'products.category',
                'products.brand',
                'products.sections'
            ])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الطلب غير موجود'
            ], 404);
        }

        $statusColors = [
            'pending' => 'bg-warning',
            'processing' => 'bg-primary',
            'delivering' => 'bg-info',
            'completed' => 'bg-success',
        ];
        $statusTexts = [
            'pending' => 'قيد الانتظار',
            'processing' => 'جاري التجهيز',
            'delivering' => 'جاري التوصيل',
            'completed' => 'تم التسليم',
        ];
        $color = $statusColors[$order->status] ?? 'bg-secondary';
        $statusText = $statusTexts[$order->status] ?? 'غير معروف';
        $html = view('website.customer.order_status_card', compact('order', 'color', 'statusText'))->render();
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
}