<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CashExchangeRequest;
use App\Support\Email\EmailNotifier;
use App\Support\WhatsApp\WhatsAppNumber;
use App\Support\WhatsApp\WasenderNotifier;
use Illuminate\Http\Request;

class CashExchangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'pending');
        if (!in_array($status, ['pending', 'completed', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $q = CashExchangeRequest::query()->with(['user', 'offer'])->latest();
        if ($status !== 'all') {
            $q->where('status', $status);
        }

        $requests = $q->paginate(40)->withQueryString();

        return view('dashboard.admin.cash_exchange.requests.index', [
            'pageTitle' => 'طلبات استبدال الرصيد (كاش)',
            'requests' => $requests,
            'status' => $status,
        ]);
    }

    public function show(CashExchangeRequest $cashExchangeRequest)
    {
        $cashExchangeRequest->load(['user', 'offer']);

        return view('dashboard.admin.cash_exchange.requests.show', [
            'pageTitle' => 'تفاصيل طلب الاستبدال',
            'req' => $cashExchangeRequest,
        ]);
    }

    public function complete(Request $request, CashExchangeRequest $cashExchangeRequest)
    {
        if ((string) ($cashExchangeRequest->status ?? '') !== 'pending') {
            return back()->withErrors(['error' => 'لا يمكن تنفيذ هذا الإجراء لأن الطلب ليس قيد المراجعة.']);
        }

        $oldStatus = (string) ($cashExchangeRequest->status ?? '');
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $cashExchangeRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'rejected_at' => null,
            'admin_note' => $data['admin_note'] ?? $cashExchangeRequest->admin_note,
        ]);

        if ($oldStatus !== 'completed') {
            $this->notifyCustomer($cashExchangeRequest, true);
        }

        return back()->with('success', 'تم تغيير الحالة إلى مكتمل ✅');
    }

    public function reject(Request $request, CashExchangeRequest $cashExchangeRequest)
    {
        if ((string) ($cashExchangeRequest->status ?? '') !== 'pending') {
            return back()->withErrors(['error' => 'لا يمكن تنفيذ هذا الإجراء لأن الطلب ليس قيد المراجعة.']);
        }

        $oldStatus = (string) ($cashExchangeRequest->status ?? '');
        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);

        $cashExchangeRequest->update([
            'status' => 'rejected',
            'completed_at' => null,
            'rejected_at' => now(),
            'admin_note' => $data['admin_note'],
        ]);

        if ($oldStatus !== 'rejected') {
            $this->notifyCustomer($cashExchangeRequest, false);
        }

        return back()->with('success', 'تم رفض الطلب ✅');
    }

    public function updateNote(Request $request, CashExchangeRequest $cashExchangeRequest)
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $cashExchangeRequest->update([
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        return back()->with('success', 'تم حفظ الملاحظة ✅');
    }

    public function destroy(Request $request, CashExchangeRequest $cashExchangeRequest)
    {
        $request->validate([
            'confirm' => ['required', 'in:DELETE'],
        ]);

        $cashExchangeRequest->delete();

        return redirect()
            ->route('admin.cash_exchange.requests.index', ['status' => 'all'])
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

        CashExchangeRequest::query()->whereIn('id', $ids)->delete();

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

        $q = CashExchangeRequest::query();
        if ($status !== 'all') {
            $q->where('status', $status);
        }
        $q->delete();

        return back()->with('success', 'تم حذف الطلبات ✅');
    }

    private function notifyCustomer(CashExchangeRequest $req, bool $completed): void
    {
        try { $req->loadMissing(['user', 'user.profile', 'offer']); } catch (\Throwable $e) {}

        $app = (string) config('app.name', 'المتجر');
        $offer = (string) ($req->offer?->name ?? '');
        $note = trim((string) ($req->admin_note ?? ''));
        $noteLine = $note !== '' ? ("\nملاحظة: " . mb_substr($note, 0, 180)) : '';

        $statusLine = $completed ? 'تم إكمال طلبك ✅' : 'تم رفض طلبك ❌';
        $text = trim(
            "{$app}\n" .
            "{$statusLine}\n" .
            "الخدمة: استبدال رصيدك كاش\n" .
            "رقم الطلب: {$req->reference}\n" .
            ($offer !== '' ? "الفئة: {$offer}\n" : '') .
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
}

