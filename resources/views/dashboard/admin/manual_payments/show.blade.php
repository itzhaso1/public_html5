<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'تفاصيل طلب الدفع اليدوي' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<main class="max-w-5xl mx-auto p-4 sm:p-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">{{ $pageTitle ?? 'تفاصيل طلب الدفع اليدوي' }}</h1>
            <div class="text-sm text-gray-600 mt-1">المرجع: <span class="font-mono select-all">{{ $mpr->reference }}</span></div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.manual_payments.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
                رجوع
            </a>
            <form method="POST" action="{{ route('admin.manual_payments.destroy', $mpr) }}" class="inline-flex">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirm" value="DELETE">
                <button type="button"
                        onclick="const v=prompt('اكتب DELETE لتأكيد حذف هذا الطلب'); if(v==='DELETE'){ this.form.submit(); }"
                        class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-extrabold text-white hover:bg-red-700 transition">
                    حذف الطلب
                </button>
            </form>
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
            <div class="font-extrabold mb-1">حدث خطأ</div>
            <ul class="list-disc ps-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h2 class="font-extrabold text-gray-900">معلومات الطلب</h2>

            <div class="mt-3 space-y-2 text-sm">
                <div><span class="text-gray-500">الباقة:</span> <span class="font-bold">{{ $mpr->product?->name ?? '-' }}</span></div>
                <div><span class="text-gray-500">Player ID:</span> <span class="font-bold select-all">{{ $mpr->player_id }}</span></div>
                <div><span class="text-gray-500">المبلغ:</span> <span class="font-extrabold text-green-700">ر.س {{ number_format((float)$mpr->amount, 2) }}</span></div>
                    @if(!empty($mpr->points_spent))
                        <div><span class="text-gray-500">النقاط:</span> <span class="font-extrabold text-gray-900">{{ number_format((int)$mpr->points_spent) }}</span></div>
                    @endif
                <div><span class="text-gray-500">الحالة:</span> <span class="font-extrabold">{{ $mpr->status }}</span></div>
                    <div><span class="text-gray-500">طريقة الدفع:</span> <span class="font-bold">{{ $mpr->payment_method ?? '-' }}</span></div>
                @if(($mpr->product?->service_type ?? null) === 'gems')
                    <div class="pt-2 mt-2 border-t border-gray-100">
                        <div class="font-extrabold text-gray-900 mb-1">Shop2TopUp</div>
                        <div><span class="text-gray-500">TRX ID:</span> <span class="font-mono text-xs select-all">{{ $mpr->shop2topup_trx_id ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Status:</span> <span class="font-extrabold">{{ $mpr->shop2topup_status ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Order ID:</span> <span class="font-mono text-xs select-all">{{ $mpr->shop2topup_order_id ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Delivered at:</span> <span class="font-mono text-xs">{{ $mpr->shop2topup_delivery_at?->format('Y-m-d H:i:s') ?? '-' }}</span></div>
                    </div>
                @endif
                @if($mpr->contact_phone)
                    <div><span class="text-gray-500">الهاتف:</span> <span class="font-bold select-all">{{ $mpr->contact_phone }}</span></div>
                @endif
                @if($mpr->contact_email)
                    <div><span class="text-gray-500">الإيميل:</span> <span class="font-bold select-all">{{ $mpr->contact_email }}</span></div>
                @endif
                <div><span class="text-gray-500">IP:</span> <span class="font-mono text-xs select-all">{{ $mpr->ip ?? '-' }}</span></div>
                <div class="text-xs text-gray-500">تاريخ الإنشاء: {{ $mpr->created_at?->format('Y-m-d H:i') }}</div>
            </div>

            <div class="mt-5">
                <label class="block text-sm font-extrabold mb-2">ملاحظة الأدمن (اختياري)</label>
                @if(($mpr->status ?? 'pending') !== 'pending')
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                        هذا الطلب تم {{ ($mpr->status === 'approved') ? 'قبوله' : 'رفضه' }} ولا يمكن تنفيذ قبول/رفض مرة أخرى.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.manual_payments.approve', $mpr) }}" class="space-y-3">
                    @csrf
                    <textarea name="admin_note" rows="3"
                              class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                              placeholder="مثال: تم التأكد من الإيصال وسيتم الشحن الآن..."
                              {{ ($mpr->status ?? 'pending') !== 'pending' ? 'disabled' : '' }}>{{ old('admin_note', $mpr->admin_note) }}</textarea>
                    @if(($mpr->product?->service_type ?? null) === 'gems')
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">TRX ID (من Shop2TopUp) - اختياري</label>
                            <div class="flex gap-2">
                                <input type="text" name="trx_id"
                                       value="{{ old('trx_id', $mpr->shop2topup_trx_id) }}"
                                       class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm font-mono"
                                       placeholder="efd37fee-3fc9-429c-8042-850707612306">
                                <button formaction="{{ route('admin.manual_payments.transaction', $mpr) }}"
                                        class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-extrabold hover:bg-gray-50 transition">
                                    تحديث الحالة
                                </button>
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1">زر "تحديث الحالة" يسحب حالة العملية من Shop2TopUp عبر `/transaction`.</div>
                        </div>
                    @endif
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="submit"
                                onclick="this.disabled=true; this.innerText='...جارِ الإرسال'; this.form.submit();"
                                class="flex-1 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-green-700 transition {{ ($mpr->status ?? 'pending') !== 'pending' ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ ($mpr->status ?? 'pending') !== 'pending' ? 'disabled' : '' }}>
                            موافقة
                        </button>
                </form>
                <form method="POST" action="{{ route('admin.manual_payments.reject', $mpr) }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="admin_note" value="{{ old('admin_note', $mpr->admin_note) }}">
                    <button type="submit"
                            class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-red-700 transition {{ ($mpr->status ?? 'pending') !== 'pending' ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ ($mpr->status ?? 'pending') !== 'pending' ? 'disabled' : '' }}>
                        رفض
                    </button>
                </form>
                    </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h2 class="font-extrabold text-gray-900">الإيصال</h2>
            @if($receiptUrl)
                <div class="mt-3">
                    <a href="{{ $receiptUrl }}" target="_blank"
                       class="inline-flex items-center justify-center rounded-xl bg-black px-4 py-2 text-sm font-bold text-white hover:bg-gray-800 transition">
                        فتح الإيصال
                    </a>
                </div>
                <div class="mt-4 rounded-2xl border border-gray-100 bg-gray-50 p-3">
                    @if(($receiptIsPdf ?? false) === true)
                        <div class="text-sm text-gray-600">الإيصال PDF. افتحه من الزر بالأعلى.</div>
                    @else
                        <img src="{{ $receiptUrl }}" alt="Receipt" class="w-full rounded-xl">
                    @endif
                </div>
            @else
                <div class="mt-3 text-sm text-gray-500">لا يوجد إيصال مرفوع.</div>
            @endif
        </div>
    </div>
</main>
</body>
</html>

