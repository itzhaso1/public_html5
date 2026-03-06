@php $isEdit = isset($method); @endphp

<div class="card mb-5">
    <div class="card-body">
        <div class="row g-5">
            <div class="col-md-6">
                <label class="form-label required">اسم طريقة الدفع</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $method->name ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required">النوع</label>
                <select name="type" class="form-select" required>
                    @php $type = old('type', $method->type ?? 'bank_transfer'); @endphp
                    <option value="bank_transfer" {{ $type === 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                    <option value="ewallet" {{ $type === 'ewallet' ? 'selected' : '' }}>محفظة إلكترونية</option>
                    <option value="money_transfer" {{ $type === 'money_transfer' ? 'selected' : '' }}>شركة تحويل</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">اسم البنك</label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $method->bank_name ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">رقم الحساب</label>
                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $method->account_number ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">IBAN</label>
                <input type="text" name="iban" class="form-control" value="{{ old('iban', $method->iban ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">اسم صاحب الحساب</label>
                <input type="text" name="account_holder" class="form-control" value="{{ old('account_holder', $method->account_holder ?? '') }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">تعليمات الدفع</label>
                <textarea name="instructions" class="form-control" rows="4">{{ old('instructions', $method->instructions ?? '') }}</textarea>
            </div>
            <div class="col-md-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" {{ old('is_active', $method->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label">مفعلة</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-3">
    <button type="submit" class="btn btn-primary">حفظ</button>
    <a href="{{ route('admin.auctions.payment_methods.index') }}" class="btn btn-light">إلغاء</a>
</div>
