<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MerchantRequest;
use App\Models\User;
use App\Support\Email\EmailNotifier;
use App\Support\WhatsApp\WhatsAppNumber;
use App\Support\WhatsApp\WasenderNotifier;
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

        $this->notifyApplicant($merchantRequest, true);

        return back()->with('success', 'تمت الموافقة وتحويل المستخدم إلى تاجر ✅');
    }

    public function reject(Request $request, MerchantRequest $merchantRequest): RedirectResponse
    {
        $merchantRequest->status = 'rejected';
        $merchantRequest->reviewed_by = auth('admin')->id();
        $merchantRequest->reviewed_at = now();
        $merchantRequest->save();

        $this->notifyApplicant($merchantRequest, false);

        return back()->with('success', 'تم رفض الطلب.');
    }

    private function notifyApplicant(MerchantRequest $req, bool $approved): void
    {
        try { $req->loadMissing(['user', 'user.profile']); } catch (\Throwable $e) {}

        $app = (string) config('app.name', 'المتجر');
        $status = $approved ? 'تم قبول طلب التاجر ✅' : 'تم رفض طلب التاجر ❌';
        $text = trim(
            "{$app}\n" .
            "{$status}\n" .
            "رقم الطلب: {$req->id}\n"
        );

        if ((bool) config('services.wasender.enabled', false) && (bool) config('services.wasender.notify_customers', true)) {
            $to = WhatsAppNumber::normalize((string) ($req->phone ?? ''));
            if ($to === '') $to = WhatsAppNumber::normalize((string) ($req->user?->phone ?? ''));
            if ($to === '') $to = WhatsAppNumber::normalize((string) ($req->user?->profile?->phone ?? ''));
            if ($to !== '') {
                WasenderNotifier::sendAfterCommit($to, $text);
            }
        }

        if ((bool) config('services.email_notify.enabled', false) && (bool) config('services.email_notify.notify_customers', true)) {
            $email = trim((string) ($req->user?->email ?? ''));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $subject = $approved ? 'تم قبول طلب التاجر ✅' : 'تم رفض طلب التاجر ❌';
                EmailNotifier::sendAfterCommit($email, $subject, $text);
            }
        }
    }
}

