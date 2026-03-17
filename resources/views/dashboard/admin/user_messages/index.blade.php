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

    <div class="mt-6 grid grid-cols-1 xl:grid-cols-2 gap-4">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
            <h2 class="text-lg font-extrabold">رسالة جماعية لناشري الحسابات</h2>
            <p class="mt-1 text-xs text-gray-500">
                سيتم الاستهداف بناءً على بيانات طلبات نشر الحساب من صفحة publish-product
                (عدد الطلبات: {{ number_format((int) ($publishersCount ?? 0)) }}).
            </p>

            <form method="POST" action="{{ route('admin.user_messages.broadcast_publishers') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-extrabold mb-1">العنوان</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                           placeholder="عنوان الرسالة" required>
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-1">نص الرسالة</label>
                    <textarea name="message" rows="5"
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
                    <label class="block text-sm font-extrabold mb-1">المستخدم</label>
                    <select name="user_id"
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
                    <label class="block text-sm font-extrabold mb-1">العنوان</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                           placeholder="عنوان الرسالة" required>
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-1">نص الرسالة</label>
                    <textarea name="message" rows="5"
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
</body>
</html>

