<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('dashboard.admin.payment_methods.index', [
            'pageTitle' => 'طرق الدفع',
            'methods' => $methods,
        ]);
    }

    public function create()
    {
        return view('dashboard.admin.payment_methods.form', [
            'pageTitle' => 'إضافة طريقة دفع',
            'method' => new PaymentMethod(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        PaymentMethod::create($data);

        return redirect()
            ->route('admin.payment_methods.index')
            ->with('success', 'تمت إضافة طريقة الدفع ✅');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('dashboard.admin.payment_methods.form', [
            'pageTitle' => 'تعديل طريقة دفع',
            'method' => $paymentMethod,
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $this->validated($request, $paymentMethod->id);

        $paymentMethod->update($data);

        return redirect()
            ->route('admin.payment_methods.index')
            ->with('success', 'تم تحديث طريقة الدفع ✅');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return redirect()
            ->route('admin.payment_methods.index')
            ->with('success', 'تم حذف طريقة الدفع ✅');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'key' => ['required', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];

        if ($ignoreId) {
            $rules['key'][] = 'unique:payment_methods,key,' . $ignoreId;
        } else {
            $rules['key'][] = 'unique:payment_methods,key';
        }

        $v = $request->validate($rules);

        $details = [
            'bank_name' => trim((string) $request->input('bank_name', '')),
            'account_name' => trim((string) $request->input('account_name', '')),
            'account_number' => trim((string) $request->input('account_number', '')),
            'iban' => trim((string) $request->input('iban', '')),
            'click_id' => trim((string) $request->input('click_id', '')),
            'network' => trim((string) $request->input('network', '')),
            'address' => trim((string) $request->input('address', '')),
            'link' => trim((string) $request->input('link', '')),
            'note' => trim((string) $request->input('note', '')),
        ];
        $details = array_filter($details, fn ($x) => $x !== '');

        return [
            'key' => strtolower(trim((string) $v['key'])),
            'title' => trim((string) $v['title']),
            'enabled' => (bool) $request->boolean('enabled'),
            'allowed_for_charge' => (bool) $request->boolean('allowed_for_charge'),
            'sort_order' => (int) ($v['sort_order'] ?? 0),
            'details' => $details,
        ];
    }
}

