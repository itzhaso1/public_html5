@extends('dashboard.layouts.master')

@section('pageTitle')
    {{ $pageTitle ?? 'سجل النقاط' }}
@endsection

@section('content')
    @include('dashboard.layouts.common._partial.messages')

    <div id="kt_content_container" class="container-xxl">
        <div class="card card-xxl-stretch mb-8 shadow-sm border-0">
            <div class="card-header border-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="card-title align-items-start flex-column m-0">
                    <h3 class="fw-bolder mb-1">سجل نقاط: {{ $user->name }}</h3>
                    <div class="text-muted fw-bold fs-7">
                        الرصيد الحالي: <span class="badge badge-light-success">{{ number_format((int) ($user->wallet_points_balance ?? 0)) }}</span>
                        <span class="ms-2">مجموع الشحن: <span class="badge badge-light-primary">{{ number_format((int) ($totalDeposited ?? 0)) }}</span></span>
                        <span class="ms-2">إجمالي الزيادات: <span class="badge badge-light-warning">{{ number_format((int) ($totalCredited ?? 0)) }}</span></span>
                        <span class="ms-2 font-monospace">ID: {{ $user->id }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-light">تعديل المستخدم</a>
                    <a href="{{ route('admin.user.index') }}" class="btn btn-light">رجوع</a>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 mb-6">
                    <h4 class="fw-bolder mb-3">تعديل النقاط (مع سجل)</h4>
                    <form method="POST" action="{{ route('admin.user.wallet.adjust', $user) }}" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-12 col-lg-2">
                            <label class="form-label fw-bold">النوع</label>
                            <select name="mode" class="form-select">
                                <option value="delta">زيادة/خصم</option>
                                <option value="set">ضبط الرصيد</option>
                            </select>
                        </div>
                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bold">القيمة</label>
                            <input type="number" step="1" name="value" class="form-control" placeholder="مثال: 130 أو -130">
                            <div class="form-text">في “زيادة/خصم”: استخدم + أو - . في “ضبط الرصيد”: اكتب الرصيد النهائي.</div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <label class="form-label fw-bold">سبب (اختياري)</label>
                            <input type="text" name="note" class="form-control" placeholder="مثال: تصحيح رصيد / تعويض / شحن يدوي">
                        </div>
                        <div class="col-12 col-lg-2">
                            <button class="btn btn-primary w-100" type="submit">حفظ</button>
                        </div>
                    </form>
                </div>

                <div class="row g-6">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-bolder mb-3">سجل الحركات</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-row-bordered gy-5 gs-7 align-middle">
                                <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>الوقت</th>
                                    <th>النوع</th>
                                    <th>التغيير</th>
                                    <th>قبل</th>
                                    <th>بعد</th>
                                    <th>تفاصيل</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($transactions as $t)
                                    <tr>
                                        <td class="text-nowrap">{{ $t->created_at?->format('Y-m-d H:i') }}</td>
                                        <td class="font-monospace">{{ $t->type }}</td>
                                        <td class="fw-bolder {{ $t->points_delta >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $t->points_delta >= 0 ? '+' : '' }}{{ (int) $t->points_delta }}
                                        </td>
                                        <td>{{ number_format((int) $t->balance_before) }}</td>
                                        <td>{{ number_format((int) $t->balance_after) }}</td>
                                        <td class="small text-muted">
                                            @if(is_array($t->meta) && count($t->meta))
                                                <pre class="m-0" style="max-width:520px;white-space:pre-wrap">{{ json_encode($t->meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-6">لا توجد حركات بعد.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $transactions->links() }}
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <h4 class="fw-bolder mb-3">طلبات إيداع النقاط</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-row-bordered gy-4 gs-6 align-middle">
                                <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>التاريخ</th>
                                    <th>النقاط</th>
                                    <th>الحالة</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($topups as $r)
                                    <tr>
                                        <td class="text-nowrap">{{ $r->created_at?->format('Y-m-d') }}</td>
                                        <td class="fw-bolder">{{ number_format((int) $r->points) }}</td>
                                        <td>
                                            <span class="badge {{ $r->status === 'approved' ? 'badge-light-success' : ($r->status === 'rejected' ? 'badge-light-danger' : 'badge-light-warning') }}">
                                                {{ $r->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-6">لا يوجد طلبات.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $topups->appends(['topups_page' => request('topups_page')])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

