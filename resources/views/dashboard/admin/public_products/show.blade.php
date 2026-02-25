@extends('dashboard.layouts.master')

@section('pageTitle')
    {{ $pageTitle }}
@endsection

@section('content')
    @php
        $status = (string) ($product->status ?? 'draft');
        $statusLabel = match ($status) {
            'published' => ['منشور', 'badge-light-success'],
            'archived' => ['مرفوض', 'badge-light-danger'],
            default => ['قيد المراجعة', 'badge-light-warning'],
        };
        $reasonsSelected = (array) ($product->review_reject_reasons ?? []);
    @endphp

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $pageTitle }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('admin.public_products.index') }}" class="btn btn-sm btn-light">رجوع</a>
                <form method="POST" action="{{ route('admin.public_products.destroy', $product) }}" class="d-inline-block ms-2">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="confirm" value="DELETE">
                    <button type="button" class="btn btn-sm btn-danger"
                            onclick="const v=prompt('اكتب DELETE لتأكيد حذف هذا الطلب'); if(v==='DELETE'){ this.form.submit(); }">
                        حذف
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-4 border rounded">
                        <div class="text-muted">الاسم</div>
                        <div class="fw-bold">{{ $product->name ?? '—' }}</div>

                        <div class="text-muted mt-3">السعر</div>
                        <div class="fw-bold text-success">{{ number_format((float) ($product->price ?? 0), 2) }} ر.س</div>

                        <div class="text-muted mt-3">رقم الزبون (واتساب)</div>
                        <div class="fw-bold">{{ $product->client_number ?? '—' }}</div>

                        <div class="text-muted mt-3">بريد الزبون</div>
                        <div class="fw-bold">{{ $product->client_email ?? '—' }}</div>

                        <div class="text-muted mt-3">الحالة</div>
                        <div><span class="badge {{ $statusLabel[1] }}">{{ $statusLabel[0] }}</span></div>

                        <div class="text-muted mt-3">تاريخ الطلب</div>
                        <div class="fw-bold">{{ $product->created_at?->format('Y-m-d H:i') }}</div>

                        @if(!empty($product->review_note))
                            <div class="text-muted mt-3">ملاحظة الإدارة</div>
                            <div class="fw-bold" style="white-space: pre-line">{{ $product->review_note }}</div>
                        @endif

                        @if(($product->status ?? '') === 'archived' && !empty($product->review_reject_reasons))
                            <div class="text-muted mt-3">أسباب الرفض</div>
                            <ul class="mb-0">
                                @foreach((array) $product->review_reject_reasons as $rr)
                                    <li class="fw-bold">{{ $rr }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="text-muted mt-3">رابط المتابعة للزبون</div>
                        <div class="fw-bold" style="word-break: break-all">
                            <a href="{{ route('public.products.track', ['slug' => $product->slug]) }}" target="_blank">
                                {{ route('public.products.track', ['slug' => $product->slug]) }}
                            </a>
                        </div>

                        @if($status === 'published')
                            <div class="text-muted mt-3">رابط الإعلان</div>
                            <div class="fw-bold" style="word-break: break-all">
                                <a href="{{ route('website.product.show', $product) }}" target="_blank">
                                    {{ route('website.product.show', $product) }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-4 border rounded">
                        <div class="fw-bold mb-3">الصور</div>

                        @php
                            $slides = [];
                            if (!empty($mainImage)) {
                                $slides[] = [
                                    'label' => 'الصورة الرئيسية',
                                    'url' => $mainImage,
                                ];
                            }
                            if (!empty($galleryImages) && count($galleryImages) > 0) {
                                foreach ($galleryImages as $idx => $img) {
                                    $slides[] = [
                                        'label' => 'صورة المعرض #' . ((int) $idx + 1),
                                        'url' => (string) ($img['original'] ?? ''),
                                    ];
                                }
                            }
                        @endphp

                        @if(count($slides) > 0)
                            <div id="publicProductImagesCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach($slides as $i => $s)
                                        <button type="button"
                                                data-bs-target="#publicProductImagesCarousel"
                                                data-bs-slide-to="{{ $i }}"
                                                class="{{ $i === 0 ? 'active' : '' }}"
                                                aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                                                aria-label="Slide {{ $i + 1 }}"></button>
                                    @endforeach
                                </div>

                                <div class="carousel-inner rounded border bg-light" style="min-height: 280px;">
                                    @foreach($slides as $i => $s)
                                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                            <a href="{{ $s['url'] }}" target="_blank" class="d-block w-100">
                                                <img src="{{ $s['url'] }}"
                                                     class="d-block w-100"
                                                     alt="Slide"
                                                     style="max-height: 520px; object-fit: contain; background: #f8f9fa;"
                                                     onerror="this.onerror=null;this.style.display='none';">
                                            </a>
                                            <div class="carousel-caption d-none d-md-block">
                                                <span class="badge badge-light-dark">{{ $s['label'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if(count($slides) > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#publicProductImagesCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#publicProductImagesCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                @endif
                            </div>

                            <div class="mt-3 d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-light"
                                        onclick="try{ document.querySelector('#publicProductImagesCarousel .carousel-control-prev')?.click(); }catch(e){}">
                                    السابق
                                </button>
                                <button type="button" class="btn btn-sm btn-light"
                                        onclick="try{ document.querySelector('#publicProductImagesCarousel .carousel-control-next')?.click(); }catch(e){}">
                                    التالي
                                </button>
                            </div>
                        @else
                            <div class="text-muted">لا توجد صور.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-5 p-4 border rounded">
                <div class="fw-bold mb-3">قرار الإدارة</div>

                <form method="POST" action="{{ route('admin.public_products.approve', $product) }}" class="d-inline-block me-2"
                      onsubmit="return confirm('تأكيد الموافقة ونشر الحساب؟');">
                    @csrf
                    <input type="text" name="review_note" class="form-control mb-2" placeholder="ملاحظة (اختياري)" value="{{ old('review_note', $product->review_note) }}">
                    <button class="btn btn-success btn-sm" {{ $status !== 'draft' ? 'disabled' : '' }}>موافقة (نشر)</button>
                </form>

                <form method="POST" action="{{ route('admin.public_products.reject', $product) }}" class="mt-4"
                      onsubmit="return confirm('تأكيد رفض الطلب؟');">
                    @csrf

                    <div class="mb-2 text-muted">اختر الشروط/الأسباب الخاطئة (مطلوب)</div>
                    <div class="row">
                        @foreach($reasons as $r)
                            <div class="col-md-6 mb-2">
                                <label class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="review_reject_reasons[]"
                                           value="{{ $r }}"
                                           {{ in_array($r, old('review_reject_reasons', $reasonsSelected), true) ? 'checked' : '' }}
                                           {{ $status !== 'draft' ? 'disabled' : '' }}>
                                    <span class="form-check-label">{{ $r }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold">ملاحظة منك للزبون (اختياري)</label>
                        <textarea class="form-control" name="review_note" rows="3" placeholder="اكتب ملاحظة توضيحية..."
                                  {{ $status !== 'draft' ? 'disabled' : '' }}>{{ old('review_note') }}</textarea>
                    </div>

                    <button class="btn btn-danger btn-sm mt-3" {{ $status !== 'draft' ? 'disabled' : '' }}>رفض</button>

                    @if($status !== 'draft')
                        <div class="text-muted mt-2">لا يمكن تنفيذ الموافقة/الرفض إلا مرة واحدة عندما يكون الطلب قيد المراجعة.</div>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection

