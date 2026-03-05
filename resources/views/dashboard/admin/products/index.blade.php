@extends('dashboard.layouts.master')

@section('pageTitle')
    {{ $pageTitle ?? trans('dashboard/admin.product.products') }}
@endsection

@push('css')
<style>

/* ====== Mobile polish (أفضل للجوال) ====== */
 
/* مساحة ومظهر الكارد على الشاشات الصغيرة */
@media (max-width: 576px){
  #kt_content_container{ padding-left: 12px; padding-right: 12px; }
  .card{ border-radius: 14px; }
  .card-body{ padding: 12px !important; }
}
 
/* الهيدر: ترتيب عمودي + زر أسهل للمس */
@media (max-width: 992px){
  .card-header.product-header{
    flex-direction: column;
    align-items: stretch !important;
    gap: 12px !important;
  }
 
  .card-title{
    align-items: center !important;
    width: 100%;
  }
 
  .btn-add-product{
    min-height: 48px;              /* touch target */
    padding: 12px 16px;
    font-size: 15px;
  }
  .btn-add-product i{ font-size: 1.2rem !important; }
}
 
/* الجدول: خطوط ومسافات أخف على الجوال */
@media (max-width: 992px){
  #products-table{
    font-size: 13px;
  }
  #products-table thead th{
    white-space: nowrap;
    font-size: 12px;
    padding: 10px 10px !important;
  }
  #products-table tbody td{
    white-space: nowrap;            /* يمنع التكسير الغريب */
    padding: 10px 10px !important;
  }
}
 
/* لو عندك أعمدة فيها صور/أزرار */
@media (max-width: 992px){
  #products-table img{ max-width: 44px; height: auto; border-radius: 8px; }
  #products-table .btn{ padding: 6px 10px; font-size: 12px; }
}
 
/* شريط التمرير يكون ألطف */
.table-wrap{
  border-radius: 12px;
}
/* ====== Header (Mobile-first) ====== */
.product-header{
    background: linear-gradient(135deg,#f8fbff,#eef4ff,#ffffff);
    border-bottom: 1px solid rgba(230,230,255,.7);
    box-shadow: 0 6px 22px rgba(0,0,0,.06);
    border-radius: 14px 14px 0 0;
}

.text-gradient{
    background: linear-gradient(90deg,#0d6efd,#00c6ff);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

/* ====== Add button ====== */
.btn-add-product{
    background: linear-gradient(135deg,#00c6ff,#0072ff);
    color:#fff !important;
    border:none;
    border-radius: 999px;
    padding: 12px 18px;
    font-weight: 800;
    letter-spacing:.3px;
    box-shadow: 0 10px 24px rgba(0,114,255,.35);
    transition: transform .2s ease, box-shadow .2s ease;
    position:relative;
    overflow:hidden;
    display:inline-flex;
    align-items:center;
    gap:10px;
    white-space:nowrap;
}
.btn-add-product::before{
    content:"";
    position:absolute;
    inset:-60%;
    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.35), transparent 65%);
    transform: translateX(-60%) rotate(25deg);
    opacity:0;
}
.btn-add-product:hover{
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(0,114,255,.45);
}
.btn-add-product:hover::before{
    opacity:1;
    animation: shineMove 1.1s ease forwards;
}
@keyframes shineMove{
    from{ transform: translateX(-60%) rotate(25deg); opacity:0; }
    25%{ opacity:1; }
    to{ transform: translateX(60%) rotate(25deg); opacity:0; }
}
.btn-add-product:active{ transform: scale(.98); }

/* ====== Table usability on mobile ====== */
.table-wrap{
    overflow-x:auto;
    -webkit-overflow-scrolling: touch;
}
.table-wrap::-webkit-scrollbar{ height:8px; }
.table-wrap::-webkit-scrollbar-thumb{ background:#d9e2ff; border-radius:999px; }

/* أخفي الأعمدة الثقيلة على الجوال فقط */
@media (max-width: 992px){
    #products-table thead th:nth-child(4),
    #products-table tbody td:nth-child(4),
    #products-table thead th:nth-child(5),
    #products-table tbody td:nth-child(5),
    #products-table thead th:nth-child(6),
    #products-table tbody td:nth-child(6){
        display:none !important;
    }

    .product-header{
        padding: 16px !important;
    }
    .product-title{
        font-size: 1.15rem !important;
        text-align:center;
    }
    .product-subtitle{
        text-align:center;
        display:block;
    }
    .btn-add-product{
        width: 100%;
        justify-content: center;
    }
}

/* اخفاء أزرار DataTables */
div.dt-buttons{ display:none !important; }
</style>
@endpush

@section('content')
    @include('dashboard.layouts.common._partial.messages')

    <div id="kt_content_container" class="container-xxl">
        <div class="card card-xxl-stretch mb-8 shadow-sm border-0">

            <div class="card-header product-header border-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="card-title align-items-start flex-column m-0">
                    <h3 class="fw-bolder mb-1 text-gradient product-title">{{ $pageTitle ?? 'المنتجات' }}</h3>
                    <span class="text-muted fw-bold fs-7 product-subtitle">
                        {{ $pageTitle ?? trans('dashboard/admin.product.products') }} ( {{ $productsCount ?? \App\Models\Product::count() }} )
                    </span>
                </div>

                @php $group = $group ?? null; @endphp
                <div class="d-flex flex-wrap gap-2 w-100 w-lg-auto">
                    @if(in_array($group, ['accounts','charge','codes']))
                        <form method="POST" action="{{ route('admin.products.bulk_delete', $group) }}" class="w-100 w-lg-auto bulk-delete-form">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100 w-lg-auto">
                                حذف {{ $pageTitle ?? 'المنتجات' }} دفعة واحدة
                            </button>
                        </form>
                    @endif

                    @if($group === 'charge')
                        <a href="{{ route('admin.products.create_charge') }}" class="btn btn-add-product">
                            <i class="bi bi-gem fs-4"></i>
                            <span>إضافة باقة شحن</span>
                        </a>
                    @elseif($group === 'codes')
                        <a href="{{ route('admin.diamond_codes.create') }}" class="btn btn-add-product">
                            <i class="bi bi-plus-circle-fill fs-4"></i>
                            <span>إضافة أكواد</span>
                        </a>
                    @else
                        <a href="{{ route('admin.products.create') }}" class="btn btn-add-product">
                            <i class="bi bi-plus-circle-fill fs-4"></i>
                            <span>إضافة منتج جديد</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body py-4">
                <div class="mb-5">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-bolder text-dark">روابط سريعة للأدمن</div>
                                <div class="text-muted small">اختصارات لإدارة الدفع اليدوي ومخزون الأكواد بسرعة.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.manual_payments.index') }}"
                                   class="btn btn-sm btn-light-primary fw-bolder">
                                    <i class="fas fa-clipboard-check me-1"></i>
                                    طلبات الدفع اليدوي
                                </a>
                                <a href="{{ route('admin.diamond_codes.index') }}"
                                   class="btn btn-sm btn-light-info fw-bolder">
                                    <i class="fas fa-ticket-alt me-1"></i>
                                    مخزون أكواد ملابس
                                </a>
                                <a href="{{ route('admin.diamond_codes.create') }}"
                                   class="btn btn-sm btn-light-success fw-bolder">
                                    <i class="fas fa-plus-circle me-1"></i>
                                    إضافة أكواد
                                </a>
                                <a href="{{ route('admin.products.create_charge') }}"
                                   class="btn btn-sm btn-light-warning fw-bolder">
                                    <i class="fas fa-bolt me-1"></i>
                                    إضافة منتج شحن
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @if(($group ?? null) === 'accounts')
                    <div class="mb-4">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="input-group w-100 w-lg-400px">
                                <span class="input-group-text">بحث</span>
                                <input type="text"
                                       class="form-control"
                                       id="accounts-search"
                                       placeholder="ابحث عن الحساب (ID / الاسم / slug / SKU)"
                                       autocomplete="off" />
                                <button class="btn btn-primary" type="button" id="accounts-search-btn">بحث</button>
                                <button class="btn btn-light" type="button" id="accounts-search-clear">مسح</button>
                            </div>
                            <div class="text-muted small">
                                سيتم البحث داخل أسماء الحسابات (الترجمات) + رقم المنتج + slug + SKU.
                            </div>
                        </div>
                    </div>
                @endif

                <div class="table-wrap">
                    {!! $dataTable->table(['id' => 'products-table', 'class' => 'table table-striped table-row-bordered gy-5 gs-7 align-middle text-center w-100']) !!}
                </div>
            </div>

        </div>
    </div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

{!! $dataTable->scripts() !!}

<script>
$(function () {
    // يمسك نفس الجدول (بدون إعادة تهيئة)
    const table = $('#products-table').DataTable();

    // Search UX for accounts list
    const isAccounts = @json(($group ?? null) === 'accounts');
    if (isAccounts) {
        const $input = $('#accounts-search');
        const $btn = $('#accounts-search-btn');
        const $clear = $('#accounts-search-clear');
        let t = null;

        const doSearch = () => {
            const v = ($input.val() || '').toString();
            table.search(v).draw();
        };

        $btn.on('click', doSearch);
        $clear.on('click', function () {
            $input.val('');
            table.search('').draw();
            $input.trigger('focus');
        });
        $input.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                doSearch();
            }
        });
        $input.on('input', function () {
            clearTimeout(t);
            t = setTimeout(doSearch, 300);
        });
    }

    $(document).on('submit', '.bulk-delete-form', function (e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: 'تأكيد الحذف',
            html: 'سيتم حذف المنتجات غير المرتبطة بطلبات/سلة/مبيعات فقط.<br><b>هذا الإجراء لا يمكن التراجع عنه.</b>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');

        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'سيتم حذف هذا المنتج نهائيًا!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، احذفه!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                beforeSend: () => {
                    Swal.fire({
                        title: 'جاري الحذف...',
                        text: 'يرجى الانتظار قليلًا',
                        icon: 'info',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                },
                success: (response) => {
                    Swal.close();
                    if (response && response.success) {
                        toastr.success('تم حذف المنتج بنجاح');
                        table.ajax.reload(null, false);
                    } else {
                        toastr.error('حدث خطأ أثناء الحذف');
                    }
                },
                error: () => {
                    Swal.close();
                    toastr.error('فشل في الحذف، حاول لاحقًا');
                }
            });
        });
    });
});
</script>
@endpush