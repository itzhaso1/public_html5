<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'تفاصيل طلب النقاط' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<main class="max-w-5xl mx-auto p-4 sm:p-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">{{ $pageTitle ?? 'تفاصيل طلب النقاط' }}</h1>
            <div class="text-sm text-gray-600 mt-1">المرجع: <span class="font-mono select-all">{{ $mpr->reference }}</span></div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.wallet_points_orders.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
                رجوع
            </a>
            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center justify-center rounded-xl bg-black px-4 py-2 text-sm font-bold text-white hover:bg-gray-800 transition">
                لوحة التحكم
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

    @php
        $isGems = ($mpr->product?->service_type ?? null) === 'gems';
        $isCodes = ($mpr->product?->service_type ?? null) === 'codes';
    @endphp

    <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h2 class="font-extrabold text-gray-900">معلومات الطلب</h2>

            <div class="mt-3 space-y-2 text-sm">
                <div><span class="text-gray-500">المستخدم:</span> <span class="font-bold">{{ $mpr->user?->name ?? '-' }}</span></div>
                <div><span class="text-gray-500">المنتج:</span> <span class="font-bold">{{ $mpr->product?->name ?? '-' }}</span></div>
                @if($isGems)
                    <div><span class="text-gray-500">Player ID:</span> <span class="font-bold select-all">{{ $mpr->player_id }}</span></div>
                @endif
                <div><span class="text-gray-500">المبلغ:</span> <span class="font-extrabold text-green-700">ر.س {{ number_format((float)$mpr->amount, 2) }}</span></div>
                <div><span class="text-gray-500">النقاط:</span> <span class="font-extrabold text-gray-900">{{ number_format((int)($mpr->points_spent ?? 0)) }}</span></div>
                <div><span class="text-gray-500">الحالة:</span> <span class="font-extrabold">{{ $mpr->status }}</span></div>
                @if(!empty($mpr->points_refunded_at))
                    <div><span class="text-gray-500">استرجاع النقاط:</span> <span class="font-extrabold text-emerald-700">{{ $mpr->points_refunded_at?->format('Y-m-d H:i') }}</span></div>
                @endif
                <div class="text-xs text-gray-500">تاريخ الإنشاء: {{ $mpr->created_at?->format('Y-m-d H:i') }}</div>
            </div>

            @if($isGems)
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <div class="font-extrabold text-gray-900 mb-2">Shop2TopUp</div>
                    <div class="text-sm space-y-1">
                        <div><span class="text-gray-500">TRX ID:</span> <span class="font-mono text-xs select-all">{{ $mpr->shop2topup_trx_id ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Status:</span> <span class="font-extrabold">{{ $mpr->shop2topup_status ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Order ID:</span> <span class="font-mono text-xs select-all">{{ $mpr->shop2topup_order_id ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Delivered at:</span> <span class="font-mono text-xs">{{ $mpr->shop2topup_delivery_at?->format('Y-m-d H:i:s') ?? '-' }}</span></div>
                    </div>

                    @if(($mpr->status ?? 'pending') === 'pending' && !empty($mpr->shop2topup_trx_id))
                        <form method="POST" action="{{ route('admin.wallet_points_orders.refresh', $mpr) }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-extrabold hover:bg-gray-50 transition">
                                تحديث حالة المزود
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            @if($isCodes)
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <div class="font-extrabold text-gray-900 mb-2">الكود</div>
                    @if($mpr->diamondCode)
                        <div class="text-sm text-gray-700">
                            <span class="text-gray-500">الكود:</span>
                            <span class="font-mono font-extrabold select-all">{{ $mpr->diamondCode->code }}</span>
                        </div>
                    @else
                        <div class="text-sm text-gray-600">لم يتم ربط كود بهذا الطلب.</div>
                    @endif
                </div>
            @endif

            @if(!empty($mpr->admin_note))
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <div class="font-extrabold text-gray-900 mb-2">ملاحظة</div>
                    <div class="text-sm text-gray-700 whitespace-pre-line">{{ $mpr->admin_note }}</div>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h2 class="font-extrabold text-gray-900">إدارة</h2>
            <div class="mt-4 text-sm text-gray-600">
                طلبات النقاط لا تعتمد على إيصال. يمكنك فقط متابعة حالة المزود أو حذف الطلب (سيتم محاولة استرجاع النقاط إن لم تكن مسترجعة).
            </div>

            <form method="POST" action="{{ route('admin.wallet_points_orders.destroy', $mpr) }}" class="mt-5">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirm" value="DELETE">
                <button type="button"
                        onclick="const v=prompt('اكتب DELETE لتأكيد حذف هذا الطلب'); if(v==='DELETE'){ this.form.submit(); }"
                        class="w-full inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-red-700 transition">
                    حذف الطلب
                </button>
            </form>
        </div>
    </div>
</main>
</body>
</html>

