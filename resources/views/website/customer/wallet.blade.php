@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'محفظتي' }}
@endsection

@section('content')
@php
    $user = $user ?? auth()->user();
    $balance = (int) ($user?->wallet_points_balance ?? 0);
    $ppSar = (float) ($pointPrices['sar'] ?? 3.75);
    $ppUsd = (float) ($pointPrices['usd'] ?? 1.0);
@endphp

<section class="max-w-5xl mx-auto px-4 py-8" dir="rtl">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">محفظتي (نقاط)</h1>
            <p class="text-sm text-gray-600 mt-1">رصيدك من النقاط + سجل الإيداعات والحركات.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('customer.dashboard') }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
                العودة للحساب
            </a>
            <a href="{{ route('customer.wallet.topup') }}"
               class="inline-flex items-center justify-center rounded-xl bg-black px-4 py-2 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                إيداع نقاط
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <div class="text-xs text-gray-500">الرصيد الحالي</div>
            <div class="mt-1 text-3xl font-extrabold text-gray-900">{{ number_format($balance) }}</div>
            <div class="mt-1 text-xs text-gray-500">نقطة</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <div class="text-xs text-gray-500">سعر النقطة (SAR)</div>
            <div class="mt-1 text-2xl font-extrabold text-green-700">ر.س {{ number_format($ppSar, 2) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <div class="text-xs text-gray-500">سعر النقطة (USD)</div>
            <div class="mt-1 text-2xl font-extrabold text-blue-700">$ {{ number_format($ppUsd, 2) }}</div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-gray-900">طلبات الإيداع</h2>
                <a class="text-xs font-bold text-blue-700 hover:underline" href="{{ route('customer.wallet.topup') }}">طلب جديد</a>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($topups as $t)
                    @php
                        $statusLabel = match((string) ($t->status ?? 'pending')) {
                            'approved' => 'مقبول',
                            'rejected' => 'مرفوض',
                            default => 'قيد المراجعة',
                        };
                        $statusClass = match((string) ($t->status ?? 'pending')) {
                            'approved' => 'bg-green-100 text-green-800 border-green-200',
                            'rejected' => 'bg-red-100 text-red-800 border-red-200',
                            default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        };
                    @endphp
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs text-gray-500">نقاط</div>
                                <div class="text-lg font-extrabold text-gray-900">{{ number_format((int) $t->points) }}</div>
                                <div class="text-xs text-gray-600 mt-1">
                                    ر.س {{ number_format((float) $t->amount_sar, 2) }} — $ {{ number_format((float) $t->amount_usd, 2) }}
                                </div>
                                <div class="text-[11px] text-gray-500 mt-1">{{ $t->created_at?->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                                @if($t->receipt_path)
                                    <a href="{{ route('customer.wallet.topups.receipt', $t) }}" target="_blank"
                                       class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold hover:bg-gray-50 transition">
                                        فتح الإيصال
                                    </a>
                                @endif
                            </div>
                        </div>
                        @if(!empty($t->admin_note))
                            <div class="mt-3 text-xs text-gray-700">
                                <span class="font-bold">ملاحظة:</span> {{ $t->admin_note }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-sm text-gray-500">لا يوجد طلبات إيداع بعد.</div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $topups->links() }}
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-extrabold text-gray-900">سجل الحركات</h2>
            <div class="mt-4 space-y-3">
                @forelse($transactions as $tx)
                    @php
                        $delta = (int) ($tx->points_delta ?? 0);
                        $isPlus = $delta > 0;
                    @endphp
                    <div class="rounded-2xl border border-gray-100 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs text-gray-500">{{ $tx->type }}</div>
                                <div class="mt-1 text-sm font-bold text-gray-900">
                                    {{ $isPlus ? '+' : '' }}{{ number_format($delta) }} نقطة
                                </div>
                                <div class="text-[11px] text-gray-500 mt-1">{{ $tx->created_at?->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">الرصيد بعد</div>
                                <div class="text-sm font-extrabold {{ $isPlus ? 'text-green-700' : 'text-gray-900' }}">{{ number_format((int) $tx->balance_after) }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">لا توجد حركات حتى الآن.</div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

