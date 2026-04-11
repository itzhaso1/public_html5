<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'الرسائل للمستخدمين' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<main class="max-w-7xl mx-auto p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">{{ $pageTitle ?? 'الرسائل للمستخدمين' }}</h1>
            <p class="text-sm text-gray-600 mt-1">رسالة جماعية لناشري الحسابات + رسالة فردية لأي مستخدم.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center justify-center rounded-xl bg-black px-4 py-2 text-sm font-bold text-white hover:bg-gray-800 transition">
            العودة للوحة التحكم
        </a>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    @php
        $readyTemplates = [
            'price_update' => [
                'title' => 'الرجاء تعديل سعر حسابك',
                'message' => "مرحباً،\nنرجو منك تعديل سعر حسابك المنشور لدينا خلال 24 ساعة.\nفي حال عدم التعديل قد يتم إيقاف عرض الحساب مؤقتًا.\n\nشكراً لتعاونك.",
            ],
            'contact_us' => [
                'title' => 'الرجاء تواصل معنا',
                'message' => "مرحباً،\nنرجو التواصل معنا عبر واتساب الدعم لإكمال مراجعة حسابك.\n\nفريق متجر الممالك.",
            ],
            'high_price' => [
                'title' => 'السعر الحالي مرتفع',
                'message' => "مرحباً،\nبعد المراجعة تبيّن أن السعر الحالي للحساب مرتفع مقارنة بالسوق.\nالرجاء تعديل السعر إلى قيمة مناسبة لضمان استمرار نشر الحساب.",
            ],
            'images_issue' => [
                'title' => 'صور الحساب غير مناسبة',
                'message' => "مرحباً،\nتمت ملاحظة أن الصور المرفوعة غير واضحة أو غير مناسبة.\nالرجاء رفع صور واضحة ومطابقة لمحتوى الحساب.\n\nشكراً لتعاونك.",
            ],
            'final_warning' => [
                'title' => 'تنبيه نهائي قبل حذف الحساب',
                'message' => "مرحباً،\nهذا تنبيه نهائي: في حال عدم تعديل السعر/البيانات المطلوبة خلال 24 ساعة سيتم حذف الحساب من المنصة.",
            ],
        ];
    @endphp

    <div class="mt-6 grid grid-cols-1 xl:grid-cols-2 gap-4">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
            <h2 class="text-lg font-extrabold">رسالة جماعية لناشري الحسابات</h2>
            <p class="mt-1 text-xs text-gray-500">
                سيتم الاستهداف بناءً على بيانات طلبات نشر الحساب من صفحة publish-product
                (عدد الطلبات: {{ number_format((int) ($publishersCount ?? 0)) }}).
            </p>
            <p class="mt-1 text-xs text-blue-700">
                ملاحظة: سيتم إرفاق رابط إعلان كل حساب تلقائياً داخل الرسالة.
            </p>

            <form method="POST" action="{{ route('admin.user_messages.broadcast_publishers') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-extrabold mb-1">رسائل جاهزة</label>
                    <select id="broadcastTemplate"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10">
                        <option value="">-- اختر رسالة جاهزة (اختياري) --</option>
                        <option value="price_update">الرجاء تعديل سعر حسابك</option>
                        <option value="contact_us">الرجاء تواصل معنا</option>
                        <option value="high_price">السعر الحالي مرتفع</option>
                        <option value="images_issue">صور الحساب غير مناسبة</option>
                        <option value="final_warning">تنبيه نهائي قبل حذف الحساب</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-1">العنوان</label>
                    <input type="text" id="broadcastTitle" name="title" value="{{ old('title') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                           placeholder="عنوان الرسالة" required>
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-1">نص الرسالة</label>
                    <textarea id="broadcastMessage" name="message" rows="5"
                              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                              placeholder="اكتب رسالتك هنا..." required>{{ old('message') }}</textarea>
                </div>
                <div>
                    <div class="text-sm font-extrabold mb-1">قنوات الإرسال</div>
                    <label class="inline-flex items-center gap-2 ml-4">
                        <input type="checkbox" name="channels[]" value="site" checked>
                        <span>داخل الموقع</span>
                    </label>
                    <label class="inline-flex items-center gap-2 ml-4">
                        <input type="checkbox" name="channels[]" value="email" checked>
                        <span>البريد الإلكتروني</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="channels[]" value="whatsapp" checked>
                        <span>الرقم (واتساب)</span>
                    </label>
                </div>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                    إرسال الرسالة الجماعية
                </button>
            </form>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
            <h2 class="text-lg font-extrabold">رسالة فردية لمستخدم</h2>
            <p class="mt-1 text-xs text-gray-500">اختر المستخدم ثم أرسل عبر القنوات المطلوبة.</p>

            <form method="POST" action="{{ route('admin.user_messages.single') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-extrabold mb-1">بحث عن مستخدم</label>
                    <input type="text" id="singleUserSearch"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                           placeholder="اكتب الاسم أو البريد أو رقم المعرّف...">
                    <div id="singleUserSearchCount" class="mt-1 text-xs text-gray-500"></div>
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-1">المستخدم</label>
                    <select id="singleUserSelect" name="user_id"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                            required>
                        <option value="">-- اختر مستخدم --</option>
                        @foreach(($users ?? []) as $u)
                            <option value="{{ $u->id }}" @selected((string) old('user_id') === (string) $u->id)>
                                #{{ $u->id }} — {{ $u->name ?? 'بدون اسم' }} — {{ $u->email ?? 'بدون بريد' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-1">رسائل جاهزة</label>
                    <select id="singleTemplate"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10">
                        <option value="">-- اختر رسالة جاهزة (اختياري) --</option>
                        <option value="price_update">الرجاء تعديل سعر حسابك</option>
                        <option value="contact_us">الرجاء تواصل معنا</option>
                        <option value="high_price">السعر الحالي مرتفع</option>
                        <option value="images_issue">صور الحساب غير مناسبة</option>
                        <option value="final_warning">تنبيه نهائي قبل حذف الحساب</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-1">العنوان</label>
                    <input type="text" id="singleTitle" name="title" value="{{ old('title') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                           placeholder="عنوان الرسالة" required>
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-1">نص الرسالة</label>
                    <textarea id="singleMessage" name="message" rows="5"
                              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                              placeholder="اكتب رسالتك هنا..." required>{{ old('message') }}</textarea>
                </div>
                <div>
                    <div class="text-sm font-extrabold mb-1">قنوات الإرسال</div>
                    <label class="inline-flex items-center gap-2 ml-4">
                        <input type="checkbox" name="channels[]" value="site" checked>
                        <span>داخل الموقع</span>
                    </label>
                    <label class="inline-flex items-center gap-2 ml-4">
                        <input type="checkbox" name="channels[]" value="email" checked>
                        <span>البريد الإلكتروني</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="channels[]" value="whatsapp" checked>
                        <span>الرقم (واتساب)</span>
                    </label>
                </div>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                    إرسال الرسالة الفردية
                </button>
            </form>
        </section>
    </div>
</main>
<script>
    (function () {
        const templates = @json($readyTemplates);

        const applyTemplate = (templateKey, titleId, messageId) => {
            if (!templateKey || !templates[templateKey]) return;
            const titleEl = document.getElementById(titleId);
            const msgEl = document.getElementById(messageId);
            if (titleEl) titleEl.value = templates[templateKey].title || '';
            if (msgEl) msgEl.value = templates[templateKey].message || '';
        };

        const broadcastTemplate = document.getElementById('broadcastTemplate');
        if (broadcastTemplate) {
            broadcastTemplate.addEventListener('change', function () {
                applyTemplate(this.value, 'broadcastTitle', 'broadcastMessage');
            });
        }

        const singleTemplate = document.getElementById('singleTemplate');
        if (singleTemplate) {
            singleTemplate.addEventListener('change', function () {
                applyTemplate(this.value, 'singleTitle', 'singleMessage');
            });
        }

        const searchInput = document.getElementById('singleUserSearch');
        const userSelect = document.getElementById('singleUserSelect');
        const counter = document.getElementById('singleUserSearchCount');
        if (searchInput && userSelect) {
            const allOptions = Array.from(userSelect.options);
            const updateCount = () => {
                if (!counter) return;
                const visible = allOptions.filter((opt, idx) => idx === 0 || !opt.hidden).length - 1;
                counter.textContent = visible > 0
                    ? `نتائج البحث: ${visible} مستخدم`
                    : 'لا توجد نتائج مطابقة';
            };

            searchInput.addEventListener('input', function () {
                const q = (this.value || '').toString().trim().toLowerCase();
                allOptions.forEach((opt, idx) => {
                    if (idx === 0) {
                        opt.hidden = false;
                        return;
                    }
                    const text = (opt.textContent || '').toLowerCase();
                    opt.hidden = q !== '' && !text.includes(q);
                });

                const selectedOption = userSelect.options[userSelect.selectedIndex];
                if (selectedOption && selectedOption.hidden) {
                    userSelect.value = '';
                }
                updateCount();
            });

            updateCount();
        }
    })();
</script>
</body>
</html>

