<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'طلبات إيداع النقاط' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<main class="max-w-7xl mx-auto p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">{{ $pageTitle ?? 'طلبات إيداع النقاط' }}</h1>
            <p class="text-sm text-gray-600 mt-1">مراجعة إيصالات الإيداع وإضافة النقاط للمستخدم.</p>
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
                <th class="p-3 font-extrabold">#</th>
                <th class="p-3 font-extrabold">المستخدم</th>
                <th class="p-3 font-extrabold">النقاط</th>
                <th class="p-3 font-extrabold">المبلغ</th>
                <th class="p-3 font-extrabold">طريقة الدفع</th>
                <th class="p-3 font-extrabold">الحالة</th>
                <th class="p-3 font-extrabold">التاريخ</th>
                <th class="p-3 font-extrabold">الإيصال</th>
                <th class="p-3 font-extrabold">إجراء</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($requests as $r)
                @php
                    $badge = match((string) ($r->status ?? 'pending')) {
                        'approved' => 'bg-green-100 text-green-800 border-green-200',
                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                        default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    };
                    $label = match((string) ($r->status ?? 'pending')) {
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => 'قيد المراجعة',
                    };
                @endphp
                <tr class="text-right align-top">
                    <td class="p-3 font-mono text-xs">{{ $r->id }}</td>
                    <td class="p-3">
                        <div class="font-bold">{{ $r->user?->name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $r->user?->email ?? '' }}</div>
                        <div class="text-xs text-gray-500">{{ $r->user?->phone ?? '' }}</div>
                    </td>
                    <td class="p-3 font-extrabold">{{ number_format((int) $r->points) }}</td>
                    <td class="p-3">
                        <div class="font-extrabold text-green-700">ر.س {{ number_format((float) $r->amount_sar, 2) }}</div>
                        <div class="text-xs text-gray-500">$ {{ number_format((float) $r->amount_usd, 2) }}</div>
                    </td>
                    <td class="p-3">
                        <span class="font-bold">{{ $r->payment_method ?? '-' }}</span>
                    </td>
                    <td class="p-3">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold {{ $badge }}">
                            {{ $label }}
                        </span>
                        @if(!empty($r->admin_note))
                            <div class="mt-2 text-xs text-gray-600">{{ $r->admin_note }}</div>
                        @endif
                    </td>
                    <td class="p-3 text-gray-600">{{ $r->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="p-3">
                        @if($r->receipt_path)
                            <a href="{{ route('admin.wallet_topups.receipt', $r) }}" target="_blank"
                               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold hover:bg-gray-50">
                                فتح
                            </a>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="p-3">
                        @if(($r->status ?? 'pending') === 'pending')
                            <form method="POST" action="{{ route('admin.wallet_topups.approve', $r) }}" class="space-y-2">
                                @csrf
                                <textarea name="admin_note" rows="2" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs" placeholder="ملاحظة (اختياري)"></textarea>
                                <div class="flex gap-2">
                                    <button type="submit"
                                            class="flex-1 rounded-xl bg-green-600 px-3 py-2 text-xs font-extrabold text-white hover:bg-green-700 transition">
                                        موافقة
                                    </button>
                                    <button type="submit"
                                            formaction="{{ route('admin.wallet_topups.reject', $r) }}"
                                            class="flex-1 rounded-xl bg-red-600 px-3 py-2 text-xs font-extrabold text-white hover:bg-red-700 transition">
                                        رفض
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-xs text-gray-500">تمت المراجعة</div>
                        @endif
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

