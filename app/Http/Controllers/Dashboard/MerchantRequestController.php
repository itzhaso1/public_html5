<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MerchantRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MerchantRequestController extends Controller
{
    public function index()
    {
        $requests = MerchantRequest::query()
            ->with(['user'])
            ->latest('id')
            ->paginate(50);

        return view('dashboard.admin.merchant_requests.index', [
            'pageTitle' => 'طلبات التجار',
            'requests' => $requests,
        ]);
    }

    public function approve(MerchantRequest $merchantRequest): RedirectResponse
    {
        if ($merchantRequest->status !== 'approved') {
            $merchantRequest->status = 'approved';
            $merchantRequest->reviewed_by = auth('admin')->id();
            $merchantRequest->reviewed_at = now();
            $merchantRequest->save();
        }

        if ($merchantRequest->user_id) {
            /** @var User|null $user */
            $user = User::query()->find($merchantRequest->user_id);
            if ($user) {
                $user->is_merchant = true;
                $user->save();
            }
        }

        return back()->with('success', 'تمت الموافقة وتحويل المستخدم إلى تاجر ✅');
    }

    public function reject(Request $request, MerchantRequest $merchantRequest): RedirectResponse
    {
        $merchantRequest->status = 'rejected';
        $merchantRequest->reviewed_by = auth('admin')->id();
        $merchantRequest->reviewed_at = now();
        $merchantRequest->save();

        return back()->with('success', 'تم رفض الطلب.');
    }
}

