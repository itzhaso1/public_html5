@extends('dashboard.layouts.master')

@section('pageTitle')
{{ $PageTitle }}
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">{{ $PageTitle }}</h3>
        <a href="{{ route('admin.auctions.payment_methods.create') }}" class="btn btn-primary">إضافة طريقة دفع</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-bordered align-middle">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th>#</th>
                        <th>الاسم</th>
                        <th>النوع</th>
                        <th>البنك</th>
                        <th>الحساب</th>
                        <th>الحالة</th>
                        <th class="text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($methods as $method)
                        <tr>
                            <td>{{ $method->id }}</td>
                            <td>{{ $method->name }}</td>
                            <td>{{ $method->type }}</td>
                            <td>{{ $method->bank_name ?: '-' }}</td>
                            <td>{{ $method->account_number ?: '-' }}</td>
                            <td>
                                @if($method->is_active)
                                    <span class="badge badge-light-success">مفعلة</span>
                                @else
                                    <span class="badge badge-light-danger">متوقفة</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.auctions.payment_methods.edit', $method) }}" class="btn btn-sm btn-light-primary">تعديل</a>
                                <form action="{{ route('admin.auctions.payment_methods.destroy', $method) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-light-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10">لا توجد طرق دفع بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $methods->links() }}</div>
    </div>
</div>
@endsection
