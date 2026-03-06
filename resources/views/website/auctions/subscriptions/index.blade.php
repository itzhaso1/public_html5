@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
<section class="max-w-7xl mx-auto px-4 py-10" dir="rtl">
    <h1 class="text-2xl font-black text-gray-900 mb-6">اشتراكاتي</h1>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-right">المزاد</th>
                        <th class="px-4 py-3 text-right">المبلغ</th>
                        <th class="px-4 py-3 text-right">طريقة الدفع</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">تاريخ الطلب</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        @php
                            $statusClass = match($subscription->status) {
                                'approved' => 'bg-green-100 text-green-700',
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'refunded' => 'bg-blue-100 text-blue-700',
                                'applied_to_winner' => 'bg-purple-100 text-purple-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-3">
                                <a href="{{ route('auctions.show', $subscription->auction) }}" class="font-bold text-blue-700 hover:underline">
                                    {{ $subscription->auction?->title }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-bold">{{ number_format((float) $subscription->amount, 2) }}</td>
                            <td class="px-4 py-3">{{ $subscription->paymentMethod?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-black {{ $statusClass }}">
                                    {{ $subscription->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ optional($subscription->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">لا توجد اشتراكات حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $subscriptions->links() }}</div>
</section>
@endsection
