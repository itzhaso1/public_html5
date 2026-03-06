@extends('dashboard.layouts.master')

@section('pageTitle')
{{ $PageTitle }}
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.auctions.edit', $auction) }}" class="btn btn-primary">تعديل المزاد</a>

    <form method="POST" action="{{ route('admin.auctions.toggle_status', $auction) }}">
        @csrf
        <input type="hidden" name="action" value="start">
        <button class="btn btn-light-success" type="submit">تشغيل المزاد</button>
    </form>

    <form method="POST" action="{{ route('admin.auctions.toggle_status', $auction) }}">
        @csrf
        <input type="hidden" name="action" value="pause">
        <button class="btn btn-light-warning" type="submit">إيقاف مؤقت</button>
    </form>

    <form method="POST" action="{{ route('admin.auctions.toggle_status', $auction) }}">
        @csrf
        <input type="hidden" name="action" value="end">
        <button class="btn btn-light-danger" type="submit">إنهاء المزاد</button>
    </form>
</div>

<div class="row g-5">
    <div class="col-xl-8">
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">بيانات المزاد</h3></div>
            <div class="card-body">
                <h2 class="mb-1">{{ $auction->title }}</h2>
                <div class="text-muted mb-4">{{ $auction->game_name }}</div>
                <p>{{ $auction->description }}</p>
                <div class="row g-3 mt-2">
                    <div class="col-md-4"><strong>السعر الابتدائي:</strong> {{ number_format((float) $auction->starting_price, 2) }}</div>
                    <div class="col-md-4"><strong>السعر الحالي:</strong> {{ number_format((float) $auction->current_price, 2) }}</div>
                    <div class="col-md-4"><strong>الحالة:</strong> {{ $auction->status }}</div>
                    <div class="col-md-4"><strong>البداية:</strong> {{ optional($auction->starts_at)->format('Y-m-d H:i') }}</div>
                    <div class="col-md-4"><strong>النهاية:</strong> {{ optional($auction->ends_at)->format('Y-m-d H:i') }}</div>
                    <div class="col-md-4"><strong>الحد الأدنى للمشاركين:</strong> {{ $auction->min_participants }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">آخر المزايدات</h3></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered">
                        <thead>
                            <tr>
                                <th>المستخدم</th>
                                <th>القيمة</th>
                                <th>الوقت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lastBids as $bid)
                                <tr>
                                    <td>{{ $bid->user?->name }}</td>
                                    <td>{{ number_format((float) $bid->amount, 2) }}</td>
                                    <td>{{ optional($bid->created_at)->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">لا توجد مزايدات بعد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">المشاركون</h3></div>
            <div class="card-body">
                <div class="fw-bold mb-3">{{ $auction->participants_count }} / {{ $auction->min_participants }}</div>
                <ul class="list-group">
                    @forelse($participants as $participant)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $participant->user?->name }}</span>
                            <span class="badge badge-light-success">مفعل</span>
                        </li>
                    @empty
                        <li class="list-group-item">لا يوجد مشاركون مفعلون</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">صور الحساب</h3></div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($auction->images as $image)
                        <div class="col-6">
                            <img src="{{ asset('storage/'.$image->path) }}" class="img-fluid rounded">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
