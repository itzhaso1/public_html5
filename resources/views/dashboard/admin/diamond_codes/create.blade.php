<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'إضافة كود' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<main class="max-w-3xl mx-auto p-4 sm:p-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">{{ $pageTitle ?? 'إضافة كود' }}</h1>
            <p class="text-sm text-gray-600 mt-1">أضف كود + صورة (اختياري).</p>
        </div>
        <a href="{{ route('admin.diamond_codes.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
            رجوع
        </a>
    </div>

    @if($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <form class="mt-5 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4"
          method="POST" enctype="multipart/form-data" action="{{ route('admin.diamond_codes.store') }}">
        @csrf

        <div>
            <label class="block text-sm font-extrabold mb-2">اختر المنتج أو أنشئ منتج جديد</label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="rounded-2xl border border-gray-200 bg-white p-4 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="radio" name="product_mode" value="existing" class="accent-black"
                               @checked(old('product_mode', 'existing') === 'existing')>
                        <span class="font-extrabold text-sm">اختيار منتج موجود</span>
                    </div>
                    <div class="mt-3">
                        <select id="existingProductSelect" name="product_id" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                            <option value="">-- اختر المنتج --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}"
                                        data-name="{{ $p->name }}"
                                        data-price="{{ (float) $p->price }}"
                                        @selected(old('product_id') == $p->id)>
                                    {{ $p->name }} (ID: {{ $p->id }})
                                </option>
                            @endforeach
                        </select>

                        <div id="existingProductMeta" class="mt-3 hidden rounded-xl border border-gray-100 bg-gray-50 p-3 text-xs text-gray-700">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <div class="text-gray-500">المنتج المحدد</div>
                                    <div class="font-extrabold" id="metaName">-</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-gray-500">السعر</div>
                                    <div class="font-extrabold" id="metaPrice">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <button type="button" id="btnQuickEditProduct"
                                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-extrabold hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                تعديل الاسم والسعر
                            </button>
                            <button type="button" id="btnDeleteProduct"
                                    class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-extrabold text-red-700 hover:bg-red-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                حذف المنتج
                            </button>
                            <a id="btnFullEditProduct"
                               href="#"
                               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-extrabold hover:bg-gray-50 transition pointer-events-none opacity-50">
                                تعديل كامل
                            </a>
                        </div>

                        <div class="text-xs text-gray-500 mt-2">إذا القائمة فاضية، استخدم “منتج جديد”.</div>
                    </div>
                </label>

                <label class="rounded-2xl border border-gray-200 bg-white p-4 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="radio" name="product_mode" value="new" class="accent-black"
                               @checked(old('product_mode') === 'new')>
                        <span class="font-extrabold text-sm">منتج جديد (اسم على مزاجك)</span>
                    </div>
                    <div class="mt-3 space-y-2">
                        <input type="text" name="product_name" value="{{ old('product_name') }}"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                               placeholder="مثال: أكواد رقصات / أكواد سكن / أكواد سكاكين">
                        <input type="number" step="0.01" name="product_price" value="{{ old('product_price') }}"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                               placeholder="السعر (اختياري)">
                        <label class="flex items-center gap-2 text-xs font-extrabold text-gray-800">
                            <input type="checkbox" name="is_lucky_draw_codes" value="1" class="accent-black"
                                   @checked((bool) old('is_lucky_draw_codes'))>
                            هذا المنتج هو قسم (انت وحظك) - قرعة أكواد
                        </label>
                        <div class="text-xs text-gray-500">سيتم إنشاء منتج أكواد جديد تلقائيًا.</div>
                    </div>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-extrabold mb-1">نسبة/وزن القرعة (Luck Weight)</label>
                <input type="number" min="1" name="luck_weight" value="{{ old('luck_weight', 1) }}"
                       class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                       placeholder="1">
                <div class="text-xs text-gray-500 mt-1">كلما زاد الرقم زادت فرصة فوز هذا الكود (لـ “انت وحظك”).</div>
            </div>
            <div>
                <label class="block text-sm font-extrabold mb-1">وصف/تصنيف (اختياري)</label>
                <input type="text" name="luck_label" value="{{ old('luck_label') }}"
                       class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                       placeholder="مثال: أفضل / متوسط / رخيص">
                <div class="text-xs text-gray-500 mt-1">للإدارة فقط (يساعدك ترتّب الأكواد).</div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-extrabold mb-1">كود واحد (اختياري)</label>
            <textarea name="code" rows="2"
                      class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-mono"
                      placeholder="ضع كود واحد هنا">{{ old('code') }}</textarea>
            <div class="text-xs text-gray-500 mt-1">إذا بدك تضيف دفعة أكواد، استخدم الحقل اللي تحت.</div>
        </div>

        <div>
            <label class="block text-sm font-extrabold mb-1">صورة للكود الواحد (اختياري)</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                   class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm">
            <div class="text-xs text-gray-500 mt-1">حتى 5MB</div>
        </div>

        <div class="pt-2 border-t border-gray-100">
            <label class="block text-sm font-extrabold mb-1">دفعة أكواد (اختياري)</label>
            <textarea name="codes" rows="6"
                      class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-mono"
                      placeholder="ضع كل كود بسطر&#10;CODE-1&#10;CODE-2&#10;CODE-3">{{ old('codes') }}</textarea>
            <div class="text-xs text-gray-500 mt-1">
                تقدر تضيف 5 أو 10 أكواد دفعة واحدة. سيتم تجاهل الأسطر الفارغة والتكرارات.
            </div>
        </div>

        <div>
            <label class="block text-sm font-extrabold mb-1">صور متعددة (اختياري)</label>
            <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple
                   class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm">
            <div class="text-xs text-gray-500 mt-1">
                إذا رفعت صور متعددة، سيتم ربط كل صورة بالكود حسب ترتيب السطور (الصورة الأولى للكود الأول...).
            </div>
        </div>

        <button class="w-full rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
            حفظ
        </button>
    </form>

    <!-- Quick edit modal (existing product) -->
    <div id="editProductModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative mx-auto mt-20 w-[92%] max-w-lg rounded-2xl bg-white shadow-xl border border-gray-200">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">تعديل سريع</div>
                    <div class="text-lg font-extrabold">تعديل اسم وسعر المنتج</div>
                </div>
                <button type="button" id="btnCloseEditModal"
                        class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-extrabold hover:bg-gray-50">
                    إغلاق
                </button>
            </div>
            <form id="quickEditProductForm" class="p-5 space-y-3" method="POST" action="#">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-extrabold mb-1">الاسم</label>
                    <input type="text" name="name" id="editProductName"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                           placeholder="اسم المنتج">
                </div>
                <div>
                    <label class="block text-xs font-extrabold mb-1">السعر</label>
                    <input type="number" step="0.01" min="0" name="price" id="editProductPrice"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"
                           placeholder="0.00">
                </div>

                <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-3 text-xs text-yellow-900">
                    ملاحظة: هذا التعديل مخصص لمنتجات “أكواد ملابس” فقط.
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                    حفظ التعديل
                </button>
            </form>
        </div>
    </div>

    <form id="deleteProductForm" method="POST" action="#" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</main>

<script>
  (function () {
    const select = document.getElementById('existingProductSelect');
    const metaBox = document.getElementById('existingProductMeta');
    const metaName = document.getElementById('metaName');
    const metaPrice = document.getElementById('metaPrice');

    const btnQuickEdit = document.getElementById('btnQuickEditProduct');
    const btnDelete = document.getElementById('btnDeleteProduct');
    const btnFullEdit = document.getElementById('btnFullEditProduct');

    const modal = document.getElementById('editProductModal');
    const btnCloseModal = document.getElementById('btnCloseEditModal');
    const quickEditForm = document.getElementById('quickEditProductForm');
    const deleteForm = document.getElementById('deleteProductForm');

    const inputName = document.getElementById('editProductName');
    const inputPrice = document.getElementById('editProductPrice');

    const updateTpl = @json(route('admin.diamond_codes.product.update', ['product' => 0]));
    const deleteTpl = @json(route('admin.diamond_codes.product.destroy', ['product' => 0]));
    const fullEditTpl = @json(route('admin.products.edit', ['product' => 0]));

    function setDisabled(disabled) {
      btnQuickEdit.disabled = disabled;
      btnDelete.disabled = disabled;
      if (disabled) {
        btnFullEdit.classList.add('pointer-events-none', 'opacity-50');
        btnFullEdit.setAttribute('href', '#');
      } else {
        btnFullEdit.classList.remove('pointer-events-none', 'opacity-50');
      }
    }

    function updateMeta() {
      const opt = select?.selectedOptions?.[0];
      const id = opt && opt.value ? opt.value : '';

      if (!id) {
        metaBox?.classList.add('hidden');
        metaName.textContent = '-';
        metaPrice.textContent = '-';
        setDisabled(true);
        return;
      }

      const name = opt.getAttribute('data-name') || '-';
      const price = opt.getAttribute('data-price') || '0';
      metaBox?.classList.remove('hidden');
      metaName.textContent = name;
      metaPrice.textContent = `ر.س ${Number(price).toFixed(2)}`;

      quickEditForm.action = updateTpl.replace(/\/0$/, `/${id}`);
      deleteForm.action = deleteTpl.replace(/\/0$/, `/${id}`);
      btnFullEdit.setAttribute('href', fullEditTpl.replace(/\/0\/edit$/, `/${id}/edit`));
      setDisabled(false);

      // Pre-fill modal inputs
      inputName.value = name;
      inputPrice.value = Number(price);
    }

    function openModal() {
      if (!modal) return;
      modal.classList.remove('hidden');
    }

    function closeModal() {
      if (!modal) return;
      modal.classList.add('hidden');
    }

    select?.addEventListener('change', updateMeta);
    btnQuickEdit?.addEventListener('click', () => {
      if (btnQuickEdit.disabled) return;
      openModal();
    });
    btnCloseModal?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => {
      if (e.target === modal.firstElementChild) closeModal();
    });

    btnDelete?.addEventListener('click', () => {
      if (btnDelete.disabled) return;
      const opt = select?.selectedOptions?.[0];
      const name = opt?.getAttribute('data-name') || 'هذا المنتج';
      const ok = confirm(`هل أنت متأكد من حذف المنتج: ${name} ؟\\nقد يؤثر ذلك على المخزون.`);
      if (!ok) return;
      deleteForm.submit();
    });

    // initialize with old value
    updateMeta();
  })();
</script>
</body>
</html>

