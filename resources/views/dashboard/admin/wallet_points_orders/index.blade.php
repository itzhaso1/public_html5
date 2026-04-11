<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'طلبات الشحن بالنقاط' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<main class="max-w-7xl mx-auto p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">{{ $pageTitle ?? 'طلبات الشحن بالنقاط' }}</h1>
            <p class="text-sm text-gray-600 mt-1">طلبات مدفوعة من المحفظة (نقاط) — متابعة حالة المزود للجواهر.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center justify-center rounded-xl bg-black px-4 py-2 text-sm font-bold text-white hover:bg-gray-800 transition">
            العودة للوحة التحكم
        </a>
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

    <div class="mt-5 overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
            <tr class="text-right">
                <th class="p-3 font-extrabold">المرجع</th>
                <th class="p-3 font-extrabold">المستخدم</th>
                <th class="p-3 font-extrabold">المنتج</th>
                <th class="p-3 font-extrabold">النقاط</th>
                <th class="p-3 font-extrabold">الحالة</th>
                <th class="p-3 font-extrabold">حالة المزود</th>
                <th class="p-3 font-extrabold">التاريخ</th>
                <th class="p-3 font-extrabold">إجراء</th>
                <th class="p-3 font-extrabold">حذف</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($requests as $mpr)
                @php
                    $badge = match($mpr->status) {
                        'approved' => 'bg-green-100 text-green-800 border-green-200',
                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                        default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    };
                    $label = match($mpr->status) {
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => 'قيد المعالجة',
                    };
                @endphp
                <tr class="text-right">
                    <td class="p-3 font-mono text-xs select-all">{{ $mpr->reference }}</td>
                    <td class="p-3">
                        <div class="font-bold">{{ $mpr->user?->name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $mpr->user?->email ?? '' }}</div>
                    </td>
                    <td class="p-3 font-bold">{{ $mpr->product?->name ?? '-' }}</td>
                    <td class="p-3 font-extrabold">{{ number_format((int) ($mpr->points_spent ?? 0)) }}</td>
                    <td class="p-3">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold {{ $badge }}">
                            {{ $label }}
                        </span>
                        @if(!empty($mpr->points_refunded_at))
                            <div class="mt-1 text-[11px] font-extrabold text-emerald-700">تم إرجاع النقاط</div>
                        @endif
                    </td>
                    <td class="p-3 font-mono text-xs">{{ $mpr->shop2topup_status ?? '-' }}</td>
                    <td class="p-3 text-gray-600">{{ $mpr->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="p-3">
                        <a href="{{ route('admin.wallet_points_orders.show', $mpr) }}"
                           class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold hover:bg-gray-50">
                            فتح
                        </a>
                    </td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('admin.wallet_points_orders.destroy', $mpr) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="confirm" value="DELETE">
                            <button type="button"
                                    onclick="const v=prompt('اكتب DELETE لتأكيد حذف هذا الطلب'); if(v==='DELETE'){ this.form.submit(); }"
                                    class="inline-flex items-center justify-center rounded-xl bg-red-600 px-3 py-2 text-xs font-extrabold text-white hover:bg-red-700 transition">
                                حذف
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td class="p-6 text-center text-gray-500" colspan="9">لا توجد طلبات.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</main>
</body>
</html>

