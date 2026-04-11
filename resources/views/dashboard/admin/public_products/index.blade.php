@extends('dashboard.layouts.master')

@section('pageTitle')
    {{ $pageTitle }}
@endsection

@section('content')
    <div class="mb-5 card card-xxl-stretch mb-xl-8">
        <div class="pt-5 border-0 card-header">
            <h3 class="card-title align-items-start flex-column">
                <span class="mb-1 card-label fw-bolder fs-3">{{ $pageTitle }}</span>
                <span class="mt-1 text-muted fw-bold fs-7">طلبات نشر الحسابات القادمة من صفحة النشر الخارجي</span>
            </h3>

            <div class="card-toolbar d-flex gap-2">
                <a class="btn btn-sm {{ ($status ?? 'pending') === 'pending' ? 'btn-primary' : 'btn-light' }}"
                   href="{{ route('admin.public_products.index', ['status' => 'pending']) }}">قيد المراجعة</a>
                <a class="btn btn-sm {{ ($status ?? '') === 'approved' ? 'btn-primary' : 'btn-light' }}"
                   href="{{ route('admin.public_products.index', ['status' => 'approved']) }}">منشور</a>
                <a class="btn btn-sm {{ ($status ?? '') === 'rejected' ? 'btn-primary' : 'btn-light' }}"
                   href="{{ route('admin.public_products.index', ['status' => 'rejected']) }}">مرفوض</a>
                <a class="btn btn-sm {{ ($status ?? '') === 'all' ? 'btn-primary' : 'btn-light' }}"
                   href="{{ route('admin.public_products.index', ['status' => 'all']) }}">الكل</a>

                <button type="button" class="btn btn-sm btn-danger" id="public-products-bulk-delete" disabled>
                    حذف المحدد
                </button>

                <form id="public-products-bulk-delete-form" method="POST" action="{{ route('admin.public_products.bulk_delete') }}" class="d-none">
                    @csrf
                    <input type="hidden" name="confirm" id="public-products-bulk-confirm" value="">
                    <span id="public-products-bulk-ids"></span>
                </form>
            </div>
        </div>

        <div class="py-3 card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-row-bordered gy-5 gs-7">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th style="width:32px">
                            <input type="checkbox" class="form-check-input" id="public-products-select-all" />
                        </th>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>السعر</th>
                        <th>رقم الزبون</th>
                        <th>الحالة</th>
                        <th>تاريخ</th>
                        <th>فتح</th>
                        <th>حذف</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($requests as $p)
                        @php
                            $label = match((string) ($p->status ?? 'draft')) {
                                'published' => ['منشور', 'badge-light-success'],
                                'archived' => ['مرفوض', 'badge-light-danger'],
                                default => ['قيد المراجعة', 'badge-light-warning'],
                            };
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox"
                                       class="form-check-input js-public-product-id"
                                       value="{{ $p->id }}"
                                       aria-label="Select {{ $p->id }}" />
                            </td>
                            <td>{{ $p->id }}</td>
                            <td class="fw-bold">{{ $p->name ?? '—' }}</td>
                            <td class="fw-bold text-success">{{ number_format((float) ($p->price ?? 0), 2) }} ر.س</td>
                            <td class="font-monospace">{{ $p->client_number ?? '—' }}</td>
                            <td><span class="badge {{ $label[1] }}">{{ $label[0] }}</span></td>
                            <td>{{ $p->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <a class="btn btn-sm btn-light btn-active-primary" href="{{ route('admin.public_products.show', $p) }}">تفاصيل</a>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.public_products.destroy', $p) }}" class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="confirm" value="DELETE">
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="const v=prompt('اكتب DELETE لتأكيد حذف هذا الطلب'); if(v==='DELETE'){ this.form.submit(); }">
                                        حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-6">لا توجد طلبات.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        (function () {
            const btn = document.getElementById('public-products-bulk-delete');
            const selectAll = document.getElementById('public-products-select-all');
            const idsWrap = document.getElementById('public-products-bulk-ids');
            const confirmInput = document.getElementById('public-products-bulk-confirm');
            const form = document.getElementById('public-products-bulk-delete-form');

            if (!btn || !selectAll || !idsWrap || !confirmInput || !form) return;

            const getChecks = () => Array.from(document.querySelectorAll('.js-public-product-id'));
            const getSelectedIds = () => getChecks().filter(c => c.checked).map(c => c.value);

            const refresh = () => {
                const checks = getChecks();
                const selected = getSelectedIds();
                btn.disabled = selected.length === 0;
                if (checks.length > 0) {
                    selectAll.indeterminate = selected.length > 0 && selected.length < checks.length;
                    selectAll.checked = selected.length > 0 && selected.length === checks.length;
                } else {
                    selectAll.indeterminate = false;
                    selectAll.checked = false;
                }
            };

            document.addEventListener('change', function (e) {
                if (e.target && (e.target.classList?.contains('js-public-product-id') || e.target.id === 'public-products-select-all')) {
                    if (e.target.id === 'public-products-select-all') {
                        const checked = !!e.target.checked;
                        getChecks().forEach(c => { c.checked = checked; });
                    }
                    refresh();
                }
            });

            btn.addEventListener('click', function () {
                const ids = getSelectedIds();
                if (ids.length === 0) {
                    alert('اختر عنصر واحد على الأقل.');
                    return;
                }
                const v = prompt('اكتب DELETE لتأكيد حذف المحدد (' + ids.length + ')');
                if (v !== 'DELETE') return;

                // reset previous ids
                idsWrap.innerHTML = '';
                confirmInput.value = 'DELETE';

                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    idsWrap.appendChild(input);
                });

                form.submit();
            });

            refresh();
        })();
    </script>
@endsection

