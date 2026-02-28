@extends('dashboard.layouts.master')

@section('pageTitle')
    {{ $pageTitle ?? 'طلبات التجار' }}
@endsection

@section('content')
    @include('dashboard.layouts.common._partial.messages')

    <div id="kt_content_container" class="container-xxl">
        <div class="card card-xxl-stretch mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3">طلبات التجار</span>
                    <span class="text-muted mt-1 fw-bold fs-7">مراجعة طلبات الحصول على سعر أفضل للجواهر.</span>
                </h3>
            </div>

            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table align-middle gs-0 gy-4">
                        <thead>
                        <tr class="fw-bolder text-muted">
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>الاسم</th>
                            <th>واتساب</th>
                            <th>الحالة</th>
                            <th>تاريخ الطلب</th>
                            <th class="text-end">إجراءات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($requests as $r)
                            <tr>
                                <td>{{ $r->id }}</td>
                                <td>
                                    @if($r->user)
                                        <div class="fw-bold">{{ $r->user->email }}</div>
                                        <div class="text-muted fs-7">UID: {{ $r->user->id }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $r->name ?: '—' }}</td>
                                <td class="font-monospace">{{ $r->phone ?: '—' }}</td>
                                <td>
                                    @php
                                        $badge = [
                                            'pending' => 'badge badge-light-warning',
                                            'approved' => 'badge badge-light-success',
                                            'rejected' => 'badge badge-light-danger',
                                        ][$r->status] ?? 'badge badge-light';
                                    @endphp
                                    <span class="{{ $badge }}">{{ $r->status }}</span>
                                </td>
                                <td>{{ $r->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($r->status !== 'approved')
                                            <form method="POST" action="{{ route('admin.merchant_requests.approve', $r) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-success" type="submit">موافقة</button>
                                            </form>
                                        @endif
                                        @if($r->status !== 'rejected')
                                            <form method="POST" action="{{ route('admin.merchant_requests.reject', $r) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-danger" type="submit">رفض</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if(!empty($r->note))
                                <tr>
                                    <td></td>
                                    <td colspan="6">
                                        <div class="text-muted">
                                            <span class="fw-bold">ملاحظة:</span>
                                            {{ $r->note }}
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

