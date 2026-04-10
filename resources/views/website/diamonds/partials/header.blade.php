@php
    /** @var string $title */
    /** @var string $subtitle */
    /** @var string $active */
    $title = $title ?? 'الدايموند';
    $subtitle = $subtitle ?? '';
    $active = $active ?? '';
@endphp

<section class="max-w-7xl mx-auto px-4 pt-6 pb-4" dir="rtl">
    <div class="rounded-3xl overflow-hidden border border-gray-200 shadow-sm bg-gradient-to-l from-gray-950 to-black text-white">
        <div class="p-6 sm:p-10">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white/90">
                        <span>متجر الممالك</span>
                        <span class="opacity-60">•</span>
                        <span>تنفيذ سريع</span>
                    </div>
                    <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold tracking-tight">
                        {{ $title }}
                    </h1>
                    @if($subtitle)
                        <p class="mt-2 text-sm sm:text-base text-white/80 max-w-2xl leading-relaxed">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>

                <div class="w-full lg:w-auto">
                    <div class="bg-white/10 rounded-2xl p-2 flex gap-2 flex-wrap">
                        <a href="{{ route('website.diamonds.charge') }}"
                           class="flex-1 lg:flex-none text-center rounded-xl px-4 py-2 text-sm font-extrabold transition
                                  {{ $active === 'charge' ? 'bg-yellow-400 text-black' : 'bg-white/10 text-white hover:bg-white/15' }}">
                            💎 شحن الجواهر
                        </a>
                        <a href="{{ route('website.diamonds.codes') }}"
                           class="flex-1 lg:flex-none text-center rounded-xl px-4 py-2 text-sm font-extrabold transition
                                  {{ $active === 'codes' ? 'bg-blue-500 text-white' : 'bg-white/10 text-white hover:bg-white/15' }}">
                            🎟️ أكواد ملابس
                        </a>
                        <a href="{{ route('website.freefire_accounts') }}"
                           class="flex-1 lg:flex-none text-center rounded-xl px-4 py-2 text-sm font-extrabold transition
                                  {{ $active === 'accounts' ? 'bg-amber-500 text-black' : 'bg-white/10 text-white hover:bg-white/15' }}">
                            🎮 حسابات فري فاير
                        </a>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-white/75">
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1">🔒 دفع آمن</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1">⚡ تنفيذ سريع</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1">💬 دعم عربي</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

