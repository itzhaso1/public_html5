<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MoneyExchangeRequest;
use App\Support\Email\EmailNotifier;
use App\Support\WhatsApp\WhatsAppNumber;
use App\Support\WhatsApp\WasenderNotifier;
use Illuminate\Http\Request;

class MoneyExchangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'pending');
        if (!in_array($status, ['pending', 'completed', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $q = MoneyExchangeRequest::query()->with(['user'])->latest();
        if ($status !== 'all') {
            $q->where('status', $status);
        }

        $requests = $q->paginate(40)->withQueryString();

        return view('dashboard.admin.money_exchange.requests.index', [
            'pageTitle' => 'طلبات تحويل الأموال (SAR ↔ USDT)',
            'requests' => $requests,
            'status' => $status,
        ]);
    }

    public function show(MoneyExchangeRequest $moneyExchangeRequest)
    {
        $moneyExchangeRequest->load(['user']);

        return view('dashboard.admin.money_exchange.requests.show', [
            'pageTitle' => 'تفاصيل طلب التحويل',
            'req' => $moneyExchangeRequest,
        ]);
    }

    public function complete(Request $request, MoneyExchangeRequest $moneyExchangeRequest)
    {
        if ((string) ($moneyExchangeRequest->status ?? '') !== 'pending') {
            return back()->withErrors(['error' => 'لا يمكن تنفيذ هذا الإجراء لأن الطلب ليس معلقاً.']);
        }

        $oldStatus = (string) ($moneyExchangeRequest->status ?? '');
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $moneyExchangeRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'admin_note' => $data['admin_note'] ?? $moneyExchangeRequest->admin_note,
        ]);

        if ($oldStatus !== 'completed') {
            $this->notifyCustomer($moneyExchangeRequest, true);
        }

        return back()->with('success', 'تم تغيير الحالة إلى مكتمل ✅');
    }

    public function reject(Request $request, MoneyExchangeRequest $moneyExchangeRequest)
    {
        if ((string) ($moneyExchangeRequest->status ?? '') !== 'pending') {
            return back()->withErrors(['error' => 'لا يمكن تنفيذ هذا الإجراء لأن الطلب ليس معلقاً.']);
        }

        $oldStatus = (string) ($moneyExchangeRequest->status ?? '');
        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);

        $moneyExchangeRequest->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'admin_note' => $data['admin_note'],
        ]);

        if ($oldStatus !== 'rejected') {
            $this->notifyCustomer($moneyExchangeRequest, false);
        }

        return back()->with('success', 'تم رفض الطلب ✅');
    }

    private function notifyCustomer(MoneyExchangeRequest $req, bool $completed): void
    {
        try { $req->loadMissing(['user', 'user.profile']); } catch (\Throwable $e) {}

        $app = (string) config('app.name', 'المتجر');
        $status = $completed ? 'تم إكمال طلبك ✅' : 'تم رفض طلبك ❌';
        $dir = $req->direction === 'usdt_to_sar' ? 'USDT → SAR' : 'SAR → USDT';
        $note = trim((string) ($req->admin_note ?? ''));
        $noteLine = $note !== '' ? ("\nملاحظة: " . mb_substr($note, 0, 180)) : '';

        $text = trim(
            "{$app}\n" .
            "{$status}\n" .
            "الخدمة: تحويل الأموال ({$dir})\n" .
            "رقم الطلب: {$req->reference}\n" .
            $noteLine
        );

        // WhatsApp (optional)
        if ((bool) config('services.wasender.enabled', false) && (bool) config('services.wasender.notify_customers', true)) {
            $to = WhatsAppNumber::normalize($req->user?->phone ?? '');
            if ($to === '') $to = WhatsAppNumber::normalize($req->user?->profile?->phone ?? '');
            if ($to !== '') {
                WasenderNotifier::sendAfterCommit($to, $text);
            }
        }

        // Email (optional additional channel)
        if ((bool) config('services.email_notify.enabled', false) && (bool) config('services.email_notify.notify_customers', true)) {
            $email = trim((string) ($req->user?->email ?? ''));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $subject = $completed ? 'تم إكمال طلبك ✅' : 'تم رفض طلبك ❌';
                EmailNotifier::sendAfterCommit($email, $subject, $text);
            }
        }
    }

    public function destroy(Request $request, MoneyExchangeRequest $moneyExchangeRequest)
    {
        $request->validate([
            'confirm' => ['required', 'in:DELETE'],
        ]);

        $moneyExchangeRequest->delete();

        return redirect()
            ->route('admin.money_exchange.requests.index', ['status' => 'all'])
            ->with('success', 'تم حذف الطلب ✅');
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'confirm' => ['required', 'in:DELETE'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['ids'] ?? [])));
        if (empty($ids)) {
            return back()->withErrors(['error' => 'لم يتم تحديد طلبات للحذف.']);
        }

        MoneyExchangeRequest::query()->whereIn('id', $ids)->delete();

        return back()->with('success', 'تم حذف الطلبات المحددة ✅');
    }

    public function deleteAll(Request $request)
    {
        $data = $request->validate([
            'confirm' => ['required', 'in:DELETE'],
            'status' => ['nullable', 'in:pending,completed,rejected,all'],
        ]);

        $status = (string) ($data['status'] ?? $request->query('status', 'all'));
        if (!in_array($status, ['pending', 'completed', 'rejected', 'all'], true)) {
            $status = 'all';
        }

        $q = MoneyExchangeRequest::query();
        if ($status !== 'all') {
            $q->where('status', $status);
        }
        $q->delete();

        return back()->with('success', 'تم حذف الطلبات ✅');
    }
}

