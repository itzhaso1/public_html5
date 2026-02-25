@extends('dashboard.layouts.master')

@section('pageTitle')
    {{ $pageTitle ?? 'تعديل صورة السلايدر' }}
@endsection

@section('content')
    @include('dashboard.layouts.common._partial.messages')

    @php
        $locales = (array) config('laravellocalization.supportedLocales', []);
        if (empty($locales)) {
            $locales = [
                'ar' => ['native' => 'العربية'],
                'en' => ['native' => 'English'],
            ];
        }
        $img = $slider?->getMediaUrl('slider', $slider, null, 'media', 'slider');
    @endphp

    <div id="kt_content_container" class="container-xxl">
        <div class="mb-5 card card-xxl-stretch mb-xl-8">
            <div class="pt-5 border-0 card-header">
                <h3 class="card-title align-items-start flex-column">
                    <span class="mb-1 card-label fw-bolder fs-3">{{ $pageTitle ?? 'تعديل صورة السلايدر' }}</span>
                </h3>
            </div>

            <div class="py-3 card-body">
                <form action="{{ route('admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-5 hover-scroll-x">
                        <div class="d-grid">
                            <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                @foreach($locales as $key => $lang)
                                    <li class="nav-item">
                                        <a class="nav-link @if($loop->first) active @endif"
                                           data-bs-toggle="tab" href="#tab-{{ $key }}">
                                            {{ $lang['native'] ?? $key }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="tab-content">
                        @foreach($locales as $key => $lang)
                            <div class="tab-pane fade @if($loop->first) show active @endif" id="tab-{{ $key }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">العنوان / {{ $lang['native'] ?? $key }}</label>
                                        <input type="text" name="{{ $key }}[name]" class="form-control"
                                               value="{{ old($key.'.name', optional($slider->translate($key))->name) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">الوصف / {{ $lang['native'] ?? $key }}</label>
                                        <textarea name="{{ $key }}[description]" rows="3"
                                                  class="form-control">{{ old($key.'.description', optional($slider->translate($key))->description) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-6">

                    <div class="row">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">الصورة</label>
                            <input class="form-control" type="file" name="slider" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">يدعم: JPG / PNG / WEBP</div>
                            @error('slider')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                            @if($img)
                                <div class="mt-3">
                                    <img src="{{ $img }}" alt="slider" style="max-height:120px;border-radius:8px;">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="btn btn-success w-100">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

