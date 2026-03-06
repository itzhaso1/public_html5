@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
<section class="max-w-5xl mx-auto px-4 py-10" dir="rtl">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <h1 class="text-xl font-black text-gray-900">الاشتراك في المزاد</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $auction->title }}</p>

            <div class="mt-4 rounded-xl bg-blue-50 border border-blue-100 p-4 text-sm">
                <div class="font-bold text-blue-700">مبلغ الضمان: {{ number_format((float) $auction->subscription_fee, 2) }}</div>
                <ul class="mt-2 space-y-1 text-blue-900 list-disc pr-5">
                    <li>غير الفائز يتم إرجاع الاشتراك له.</li>
                    <li>الفائز يتم احتساب الاشتراك ضمن السعر النهائي.</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('auctions.subscribe.store', $auction) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold mb-1">طريقة الدفع</label>
                    <select name="payment_method_id" id="payment_method_id" class="w-full rounded-xl border border-gray-200 px-3 py-2.5" required>
                        <option value="">اختر طريقة الدفع</option>
                        @foreach($paymentMethods as $method)
                            <option
                                value="{{ $method->id }}"
                                data-bank="{{ $method->bank_name }}"
                                data-account="{{ $method->account_number }}"
                                data-iban="{{ $method->iban }}"
                                data-holder="{{ $method->account_holder }}"
                                data-instructions="{{ $method->instructions }}"
                            >
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-1">إيصال التحويل</label>
                    <input type="file" name="receipt" accept="image/*" class="w-full rounded-xl border border-gray-200 px-3 py-2.5" required>
                </div>

                <button type="submit" class="w-full rounded-xl bg-blue-700 px-4 py-3 font-black text-white hover:bg-blue-800">
                    إرسال طلب الدفع
                </button>
            </form>
        </div>

        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <h2 class="text-lg font-black text-gray-900">بيانات التحويل</h2>
            <div class="mt-3 text-sm space-y-2">
                <div>اسم البنك: <span id="pm-bank" class="font-bold text-gray-900">-</span></div>
                <div>رقم الحساب: <span id="pm-account" class="font-bold text-gray-900 select-all">-</span></div>
                <div>IBAN: <span id="pm-iban" class="font-bold text-gray-900 select-all">-</span></div>
                <div>اسم صاحب الحساب: <span id="pm-holder" class="font-bold text-gray-900">-</span></div>
            </div>
            <div class="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                <div class="font-bold mb-1">تعليمات الدفع</div>
                <div id="pm-instructions">-</div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('payment_method_id');
    const bank = document.getElementById('pm-bank');
    const account = document.getElementById('pm-account');
    const iban = document.getElementById('pm-iban');
    const holder = document.getElementById('pm-holder');
    const instructions = document.getElementById('pm-instructions');

    const apply = () => {
        const option = select.options[select.selectedIndex];
        bank.textContent = option?.dataset.bank || '-';
        account.textContent = option?.dataset.account || '-';
        iban.textContent = option?.dataset.iban || '-';
        holder.textContent = option?.dataset.holder || '-';
        instructions.textContent = option?.dataset.instructions || '-';
    };

    select.addEventListener('change', apply);
    apply();
});
</script>
@endpush
