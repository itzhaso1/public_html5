@extends('dashboard.layouts.master')

@section('pageTitle')
{{ $PageTitle }}
@endsection

@section('content')
<div class="row g-5">
    <div class="col-xl-8">
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">تفاصيل الطلب</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>المزاد:</strong> {{ $subscription->auction?->title }}</div>
                    <div class="col-md-6"><strong>المستخدم:</strong> {{ $subscription->user?->name }} ({{ $subscription->user?->email }})</div>
                    <div class="col-md-6"><strong>الهاتف:</strong> {{ $subscription->user?->phone ?: '-' }}</div>
                    <div class="col-md-6"><strong>المبلغ:</strong> {{ number_format((float) $subscription->amount, 2) }}</div>
                    <div class="col-md-6"><strong>طريقة الدفع:</strong> {{ $subscription->paymentMethod?->name ?: '-' }}</div>
                    <div class="col-md-6"><strong>الحالة:</strong> {{ $subscription->status }}</div>
                    <div class="col-md-12"><strong>ملاحظات الإدارة:</strong> {{ $subscription->admin_notes ?: '-' }}</div>
                </div>
            </div>
        </div>

        @if($subscription->status === 'pending')
            <div class="card">
                <div class="card-header"><h3 class="card-title">قرار الإدارة</h3></div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('admin.auctions.subscriptions.approve', $subscription) }}">
                                @csrf
                                <label class="form-label">ملاحظات (اختياري)</label>
                                <textarea name="admin_notes" class="form-control mb-3" rows="3"></textarea>
                                <button class="btn btn-success w-100">قبول الدفع وتفعيل الاشتراك</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('admin.auctions.subscriptions.reject', $subscription) }}">
                                @csrf
                                <label class="form-label">سبب الرفض (إجباري)</label>
                                <textarea name="admin_notes" class="form-control mb-3" rows="3" required></textarea>
                                <button class="btn btn-danger w-100">رفض الطلب</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">إيصال التحويل</h3></div>
            <div class="card-body">
                @if($subscription->receipt_path)
                    <img src="{{ asset('storage/'.$subscription->receipt_path) }}" class="img-fluid rounded">
                @else
                    <div class="text-muted">لا يوجد إيصال مرفوع.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
