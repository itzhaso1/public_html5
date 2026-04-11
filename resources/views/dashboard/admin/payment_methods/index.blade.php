@extends('dashboard.layouts.master')

@section('pageTitle')
    {{ $pageTitle ?? 'طرق الدفع' }}
@endsection

@section('content')
    @include('dashboard.layouts.common._partial.messages')

    <div id="kt_content_container" class="container-xxl">
        <div class="card card-xxl-stretch mb-8 shadow-sm border-0">
            <div class="card-header border-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="card-title align-items-start flex-column m-0">
                    <h3 class="fw-bolder mb-1">{{ $pageTitle ?? 'طرق الدفع' }}</h3>
                    <span class="text-muted fw-bold fs-7">تحكم في طرق الدفع اليدوي التي تظهر للزبائن.</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.payment_methods.create') }}" class="btn btn-primary">
                        إضافة طريقة دفع
                    </a>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table table-striped table-row-bordered gy-5 gs-7 align-middle">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>#</th>
                            <th>Key</th>
                            <th>العنوان</th>
                            <th>مفعلة</th>
                            <th>تظهر في الشحن</th>
                            <th>ترتيب</th>
                            <th>إجراءات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($methods as $m)
                            <tr>
                                <td>{{ $m->id }}</td>
                                <td class="font-monospace">{{ $m->key }}</td>
                                <td class="fw-bold">{{ $m->title }}</td>
                                <td>
                                    @if($m->enabled)
                                        <span class="badge badge-light-success">نعم</span>
                                    @else
                                        <span class="badge badge-light-danger">لا</span>
                                    @endif
                                </td>
                                <td>
                                    @if($m->allowed_for_charge)
                                        <span class="badge badge-light-success">نعم</span>
                                    @else
                                        <span class="badge badge-light-danger">لا</span>
                                    @endif
                                </td>
                                <td>{{ (int) $m->sort_order }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.payment_methods.edit', $m) }}" class="btn btn-sm btn-light btn-active-primary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.payment_methods.destroy', $m) }}" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('تأكيد حذف طريقة الدفع؟');">
                                            حذف
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-6">
                                    لا توجد طرق دفع بعد.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

