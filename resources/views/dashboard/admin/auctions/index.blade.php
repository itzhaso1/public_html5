@extends('dashboard.layouts.master')

@section('pageTitle')
{{ $PageTitle }}
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">{{ $PageTitle }}</h3>
        <a href="{{ route('admin.auctions.create') }}" class="btn btn-primary">إضافة مزاد</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-bordered align-middle">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th>#</th>
                        <th>العنوان</th>
                        <th>اللعبة</th>
                        <th>السعر الحالي</th>
                        <th>المشاركون</th>
                        <th>الحالة</th>
                        <th>النهاية</th>
                        <th class="text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auctions as $auction)
                        <tr>
                            <td>{{ $auction->id }}</td>
                            <td>{{ $auction->title }}</td>
                            <td>{{ $auction->game_name }}</td>
                            <td>{{ number_format((float) $auction->current_price, 2) }}</td>
                            <td>{{ $auction->participants_count }}/{{ $auction->min_participants }}</td>
                            <td>
                                <span class="badge badge-light-primary">{{ $auction->status }}</span>
                            </td>
                            <td>{{ optional($auction->ends_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.auctions.show', $auction) }}" class="btn btn-sm btn-light-info">عرض</a>
                                <a href="{{ route('admin.auctions.edit', $auction) }}" class="btn btn-sm btn-light-primary">تعديل</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10">لا توجد مزادات.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $auctions->links() }}</div>
    </div>
</div>
@endsection
