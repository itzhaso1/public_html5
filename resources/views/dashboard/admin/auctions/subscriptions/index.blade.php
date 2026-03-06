@extends('dashboard.layouts.master')

@section('pageTitle')
{{ $PageTitle }}
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">{{ $PageTitle }}</h3>
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">كل الحالات</option>
                @foreach(['pending','approved','rejected','refunded','applied_to_winner'] as $st)
                    <option value="{{ $st }}" {{ ($status === $st) ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-light-primary">تصفية</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-bordered align-middle">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th>#</th>
                        <th>المزاد</th>
                        <th>المستخدم</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th class="text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->id }}</td>
                            <td>{{ $subscription->auction?->title }}</td>
                            <td>{{ $subscription->user?->name }}</td>
                            <td>{{ number_format((float) $subscription->amount, 2) }}</td>
                            <td>{{ $subscription->paymentMethod?->name ?? '-' }}</td>
                            <td><span class="badge badge-light-primary">{{ $subscription->status }}</span></td>
                            <td>{{ optional($subscription->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.auctions.subscriptions.show', $subscription) }}" class="btn btn-sm btn-light-info">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10">لا توجد طلبات.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $subscriptions->links() }}</div>
    </div>
</div>
@endsection
