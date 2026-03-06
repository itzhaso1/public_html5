<?php

namespace App\Http\Controllers\Dashboard\Auction;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin.auctions.payment_methods.index', [
            'PageTitle' => 'طرق الدفع',
            'methods' => PaymentMethod::query()->latest('id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.auctions.payment_methods.create', [
            'PageTitle' => 'إضافة طريقة دفع',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        PaymentMethod::query()->create($this->validateData($request));

        return redirect()
            ->route('admin.auctions.payment_methods.index')
            ->with('success', 'تم إضافة طريقة الدفع بنجاح.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('dashboard.admin.auctions.payment_methods.edit', [
            'PageTitle' => 'تعديل طريقة الدفع',
            'method' => $paymentMethod,
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update($this->validateData($request));

        return redirect()
            ->route('admin.auctions.payment_methods.index')
            ->with('success', 'تم تحديث طريقة الدفع بنجاح.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        return back()->with('success', 'تم حذف طريقة الدفع.');
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'type' => ['required', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:190'],
            'account_number' => ['nullable', 'string', 'max:190'],
            'iban' => ['nullable', 'string', 'max:190'],
            'account_holder' => ['nullable', 'string', 'max:190'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        return $validated;
    }
}
