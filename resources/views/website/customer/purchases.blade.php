@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'مشترياتي' }}
@endsection

@section('content')
<section class="max-w-5xl mx-auto px-4 py-8" dir="rtl">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">مشترياتي</h1>
            <p class="text-sm text-gray-600 mt-1">طلبات الشحن والدفع اليدوي والأكواد التي تم تسليمها.</p>
        </div>
        <a href="{{ route('customer.dashboard') }}"
           class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
            العودة للحساب
        </a>
    </div>

    <div class="mt-5 space-y-3">
        @forelse($requests as $mpr)
            @php
                $isCodes = ($mpr->product?->service_type ?? null) === 'codes';
                $statusLabel = match($mpr->status) {
                    'approved' => 'مقبول',
                    'rejected' => 'مرفوض',
                    default => 'قيد المراجعة',
                };
                $statusClass = match($mpr->status) {
                    'approved' => 'bg-green-100 text-green-800 border-green-200',
                    'rejected' => 'bg-red-100 text-red-800 border-red-200',
                    default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                };
            @endphp

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="text-xs text-gray-500">رقم الطلب</div>
                        <div class="font-mono text-sm select-all">{{ $mpr->reference }}</div>
                        <div class="mt-2 font-extrabold text-gray-900">
                            {{ $mpr->product?->name ?? '—' }}
                        </div>
                        @unless(($mpr->product?->service_type ?? null) === 'codes')
                            <div class="text-sm text-gray-600 mt-1">
                                Player ID: <span class="font-bold select-all">{{ $mpr->player_id }}</span>
                            </div>
                        @endunless
                    </div>

                    <div class="flex flex-col items-start sm:items-end gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                        <div class="text-sm font-extrabold text-green-700">
                            ر.س {{ number_format((float)$mpr->amount, 2) }}
                        </div>
                        @if(!empty($mpr->points_spent))
                            <div class="text-xs font-extrabold text-gray-800">
                                بالنقاط: {{ number_format((int)$mpr->points_spent) }}
                            </div>
                        @endif
                        <div class="text-xs text-gray-500">{{ $mpr->created_at?->format('Y-m-d H:i') }}</div>
                    </div>
                </div>

                @if($isCodes)
                    <div class="mt-4 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-sm font-extrabold text-gray-900">الكود</div>

                        @if($mpr->status === 'approved' && $mpr->diamondCode)
                            <div class="mt-2 text-sm text-gray-700">
                                الكود:
                                <span class="font-mono font-extrabold select-all">{{ $mpr->diamondCode->code }}</span>
                            </div>
                            @if($mpr->diamondCode->image_path)
                                <div class="mt-3">
                                    <a href="{{ route('customer.diamond_codes.image', $mpr->diamondCode) }}"
                                       target="_blank"
                                       class="inline-flex items-center justify-center rounded-xl bg-black px-4 py-2 text-xs font-bold text-white hover:bg-gray-800 transition">
                                        فتح صورة الكود
                                    </a>
                                </div>
                            @endif
                        @elseif($mpr->status === 'approved')
                            <div class="mt-2 text-sm text-gray-600">
                                تم قبول الطلب، وسيتم تسليم الكود قريبًا.
                            </div>
                        @else
                            <div class="mt-2 text-sm text-gray-600">
                                سيتم تسليم الكود بعد موافقة الأدمن على طلبك.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
                <div class="text-3xl mb-2">🧾</div>
                <h3 class="font-extrabold text-gray-900">لا توجد مشتريات بعد</h3>
                <p class="text-sm text-gray-600 mt-1">ابدأ بطلب شحن أو كود وسيظهر هنا.</p>
                <div class="mt-4 flex flex-col sm:flex-row gap-2 justify-center">
                    <a href="{{ route('website.diamonds.charge') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-black px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-800 transition">
                        شحن الجواهر
                    </a>
                    <a href="{{ route('website.diamonds.codes') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold hover:bg-gray-50 transition">
                        أكواد ملابس
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    @php
        $cashRequests = $cashRequests ?? collect();
    @endphp

    @if($cashRequests->count() > 0)
        <div class="mt-8">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-extrabold text-gray-900">استبدال رصيدك كاش</h2>
                <a href="{{ route('website.cash_exchange.index') }}"
                   class="text-sm font-bold text-blue-700 hover:underline">طلب جديد</a>
            </div>

            <div class="mt-3 space-y-3">
                @foreach($cashRequests as $r)
                    @php
                        $statusLabel = match((string) ($r->status ?? 'pending')) {
                            'completed' => 'مكتمل',
                            'rejected' => 'مرفوض',
                            default => 'قيد المراجعة',
                        };
                        $statusClass = match((string) ($r->status ?? 'pending')) {
                            'completed' => 'bg-green-100 text-green-800 border-green-200',
                            'rejected' => 'bg-red-100 text-red-800 border-red-200',
                            default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        };
                    @endphp

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="text-xs text-gray-500">رقم الطلب</div>
                                <div class="font-mono text-sm select-all">{{ $r->reference }}</div>
                                <div class="mt-2 font-extrabold text-gray-900">
                                    {{ $r->offer?->name ?? 'استبدال رصيد' }}
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    الفئة: <span class="font-bold">{{ (int) $r->face_value }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col items-start sm:items-end gap-2">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                                <div class="text-sm font-extrabold text-green-700">
                                    {{ number_format((float)$r->cash_value, 2) }} {{ $r->currency }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $r->created_at?->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>

                        @if($r->status === 'completed' && $r->completed_at)
                            <div class="mt-3 text-xs text-gray-500">
                                تم الإكمال بتاريخ: {{ $r->completed_at?->format('Y-m-d H:i') }}
                            </div>
                        @endif
                        @if($r->status === 'rejected' && $r->rejected_at)
                            <div class="mt-3 text-xs text-gray-500">
                                تم الرفض بتاريخ: {{ $r->rejected_at?->format('Y-m-d H:i') }}
                            </div>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('website.cash_exchange.show', ['reference' => $r->reference]) }}"
                               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-extrabold text-gray-800 hover:bg-gray-50 transition">
                                عرض التفاصيل
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-5">
        {{ $requests->links() }}
    </div>
</section>
@endsection

