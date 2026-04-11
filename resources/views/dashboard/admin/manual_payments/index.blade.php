<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'طلبات الدفع اليدوي' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<main class="max-w-7xl mx-auto p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">{{ $pageTitle ?? 'طلبات الدفع اليدوي' }}</h1>
            <p class="text-sm text-gray-600 mt-1">مراجعة الإيصالات والموافقة/الرفض.</p>
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

    <div class="mt-4 flex flex-col sm:flex-row gap-2">
        <form id="mprBulkDeleteForm" method="POST" action="{{ route('admin.manual_payments.bulk_delete') }}" class="inline-flex">
            @csrf
            <input type="hidden" name="confirm" id="mprBulkConfirm" value="">
            <button type="button"
                    onclick="const v=prompt('اكتب DELETE لتأكيد حذف المحدد'); if(v==='DELETE'){ document.getElementById('mprBulkConfirm').value='DELETE'; this.form.submit(); }"
                    class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-extrabold text-red-700 hover:bg-red-100 transition">
                حذف المحدد
            </button>
        </form>

        <form method="POST" action="{{ route('admin.manual_payments.delete_all') }}" class="inline-flex">
            @csrf
            <input type="hidden" name="confirm" value="DELETE">
            <button type="button"
                    onclick="const v=prompt('سيتم حذف جميع طلبات الدفع اليدوي. اكتب DELETE للتأكيد'); if(v==='DELETE'){ this.form.submit(); }"
                    class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-extrabold text-white hover:bg-red-700 transition">
                حذف الكل
            </button>
        </form>
    </div>

    <div class="mt-5 overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
            <tr class="text-right">
                <th class="p-3 font-extrabold">
                    <input type="checkbox" id="mprSelectAll" class="h-4 w-4"
                           onclick="document.querySelectorAll('input[name=&quot;ids[]&quot;]').forEach(c=>c.checked=this.checked);">
                </th>
                <th class="p-3 font-extrabold">المرجع</th>
                <th class="p-3 font-extrabold">المستخدم</th>
                <th class="p-3 font-extrabold">رصيد النقاط</th>
                <th class="p-3 font-extrabold">الباقة</th>
                <th class="p-3 font-extrabold">Player ID</th>
                <th class="p-3 font-extrabold">المبلغ</th>
                <th class="p-3 font-extrabold">طريقة الدفع</th>
                <th class="p-3 font-extrabold">الحالة</th>
                <th class="p-3 font-extrabold">التاريخ</th>
                <th class="p-3 font-extrabold">إجراء</th>
                <th class="p-3 font-extrabold">حذف</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($requests as $mpr)
                <tr class="text-right">
                    <td class="p-3">
                        <input form="mprBulkDeleteForm" type="checkbox" name="ids[]" value="{{ $mpr->id }}" class="h-4 w-4">
                    </td>
                    <td class="p-3 font-mono text-xs select-all">{{ $mpr->reference }}</td>
                    <td class="p-3">
                        @if($mpr->user)
                            <div class="font-bold">{{ $mpr->user->name ?? ('User#' . $mpr->user->id) }}</div>
                            <div class="text-xs text-gray-500 font-mono">ID: {{ $mpr->user->id }}</div>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="p-3">
                        @if($mpr->user)
                            <a href="{{ route('admin.user.wallet', $mpr->user) }}"
                               class="inline-flex items-center rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-extrabold text-green-800">
                                {{ number_format((int) ($mpr->user->wallet_points_balance ?? 0)) }}
                            </a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="p-3 font-bold">{{ $mpr->product?->name ?? '-' }}</td>
                    <td class="p-3">{{ $mpr->player_id }}</td>
                    <td class="p-3">
                        <div class="font-extrabold text-green-700">ر.س {{ number_format((float)$mpr->amount, 2) }}</div>
                        @if(!empty($mpr->points_spent))
                            <div class="text-xs font-extrabold text-gray-800">نقاط: {{ number_format((int)$mpr->points_spent) }}</div>
                        @endif
                    </td>
                    <td class="p-3">{{ $mpr->payment_method ?? '-' }}</td>
                    <td class="p-3">
                        @php
                            $badge = match($mpr->status) {
                                'approved' => 'bg-green-100 text-green-800 border-green-200',
                                'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            };
                            $label = match($mpr->status) {
                                'approved' => 'مقبول',
                                'rejected' => 'مرفوض',
                                default => 'قيد المراجعة',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold {{ $badge }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="p-3 text-gray-600">{{ $mpr->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="p-3">
                        <a href="{{ route('admin.manual_payments.show', $mpr) }}"
                           class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold hover:bg-gray-50">
                            فتح
                        </a>
                    </td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('admin.manual_payments.destroy', $mpr) }}">
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
                <tr><td class="p-6 text-center text-gray-500" colspan="12">لا توجد طلبات.</td></tr>
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

