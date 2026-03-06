@php
    $isEdit = isset($auction);
@endphp

<div class="card mb-5">
    <div class="card-body">
        <div class="row g-5">
            <div class="col-md-6">
                <label class="form-label required">عنوان المزاد</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $auction->title ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required">اسم اللعبة</label>
                <input type="text" name="game_name" class="form-control" value="{{ old('game_name', $auction->game_name ?? '') }}" required>
            </div>

            <div class="col-md-12">
                <label class="form-label required">وصف الحساب</label>
                <textarea name="description" class="form-control" rows="5" required>{{ old('description', $auction->description ?? '') }}</textarea>
            </div>

            <div class="col-md-3">
                <label class="form-label required">السعر الابتدائي</label>
                <input type="number" step="0.01" min="1" name="starting_price" class="form-control" value="{{ old('starting_price', $auction->starting_price ?? '') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label required">رسوم الاشتراك</label>
                <input type="number" step="0.01" min="0" name="subscription_fee" class="form-control" value="{{ old('subscription_fee', $auction->subscription_fee ?? '') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label required">أقل زيادة للمزايدة</label>
                <input type="number" step="0.01" min="1" name="bid_increment" class="form-control" value="{{ old('bid_increment', $auction->bid_increment ?? 1) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label required">الحد الأدنى للمشاركين</label>
                <input type="number" min="1" name="min_participants" class="form-control" value="{{ old('min_participants', $auction->min_participants ?? 15) }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label required">وقت البداية</label>
                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', isset($auction) && $auction->starts_at ? $auction->starts_at->format('Y-m-d\TH:i') : '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label required">وقت النهاية</label>
                <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', isset($auction) && $auction->ends_at ? $auction->ends_at->format('Y-m-d\TH:i') : '') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    @php $status = old('status', $auction->status ?? 'scheduled'); @endphp
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>مجدول</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="paused" {{ $status === 'paused' ? 'selected' : '' }}>موقوف</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الظهور</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_visible" value="1" {{ old('is_visible', $auction->is_visible ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label">مرئي</label>
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label">صور الحساب (متعدد)</label>
                <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
            </div>

            @if($isEdit && $auction->images->count())
                <div class="col-md-12">
                    <label class="form-label">الصور الحالية</label>
                    <div class="row g-3">
                        @foreach($auction->images as $image)
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <img src="{{ asset('storage/'.$image->path) }}" class="img-fluid rounded mb-2" style="height: 120px; object-fit: cover; width: 100%;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="delete_image_ids[]" value="{{ $image->id }}" id="delete_image_{{ $image->id }}">
                                        <label class="form-check-label text-danger" for="delete_image_{{ $image->id }}">حذف</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="d-flex gap-3">
    <button type="submit" class="btn btn-primary">حفظ</button>
    <a href="{{ route('admin.auctions.index') }}" class="btn btn-light">إلغاء</a>
</div>
