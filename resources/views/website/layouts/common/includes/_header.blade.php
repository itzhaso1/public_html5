<nav class="bg-[#070b1b] text-white sticky top-0 z-50 border-b border-blue-900/60 shadow-xl" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                @if(!empty($logo))
                    <img class="h-8 w-auto rounded" src="{{ $logo }}" alt="{{ $settings?->name }}">
                @endif
                <span class="font-black tracking-wide text-sm sm:text-base">
                    {{ $settings?->name ?? 'GameBid Pro' }}
                </span>
            </a>

            <div class="hidden md:flex items-center gap-6 text-sm font-bold">
                <a href="{{ route('home') }}" class="hover:text-yellow-300">الصفحة الرئيسية</a>
                <a href="{{ route('auctions.index') }}" class="hover:text-yellow-300">المزادات</a>
                <a href="{{ route('subscriptions.index') }}" class="hover:text-yellow-300">الاشتراكات</a>
            </div>

            <div class="hidden md:flex items-center gap-3">
                @guest
                    <a href="{{ route('auth.login') }}" class="rounded-lg bg-white/10 px-3 py-2 text-sm font-bold hover:bg-white/20">
                        تسجيل الدخول
                    </a>
                    <a href="{{ route('auth.register') }}" class="rounded-lg bg-yellow-400 px-3 py-2 text-sm font-black text-black hover:bg-yellow-300">
                        التسجيل
                    </a>
                @endguest

                @auth
                    <details class="relative">
                        <summary class="list-none cursor-pointer rounded-lg bg-white/10 px-3 py-2 text-sm font-bold hover:bg-white/20">
                            {{ auth()->user()->name }}
                        </summary>
                        <div class="absolute left-0 mt-2 w-48 rounded-xl border border-gray-200 bg-white text-gray-800 shadow-xl p-2">
                            <a href="{{ route('customer.profile') }}" class="block rounded px-3 py-2 text-sm hover:bg-gray-100">الملف الشخصي</a>
                            <a href="{{ route('auctions.my') }}" class="block rounded px-3 py-2 text-sm hover:bg-gray-100">مزاداتي</a>
                            <a href="{{ route('subscriptions.index') }}" class="block rounded px-3 py-2 text-sm hover:bg-gray-100">الاشتراكات</a>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-right rounded px-3 py-2 text-sm hover:bg-gray-100">
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </details>
                @endauth
            </div>

            <button id="mobile-menu-button" class="md:hidden rounded-lg border border-white/20 p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="md:hidden hidden px-4 pb-4 space-y-2" id="mobile-menu">
        <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 hover:bg-white/10">الصفحة الرئيسية</a>
        <a href="{{ route('auctions.index') }}" class="block rounded-lg px-3 py-2 hover:bg-white/10">المزادات</a>
        <a href="{{ route('subscriptions.index') }}" class="block rounded-lg px-3 py-2 hover:bg-white/10">الاشتراكات</a>

        @auth
            <div class="mt-2 rounded-lg border border-white/10 p-2">
                <div class="px-2 pb-2 text-xs text-gray-300">مرحباً، {{ auth()->user()->name }}</div>
                <a href="{{ route('customer.profile') }}" class="block rounded-lg px-2 py-2 hover:bg-white/10">الملف الشخصي</a>
                <a href="{{ route('auctions.my') }}" class="block rounded-lg px-2 py-2 hover:bg-white/10">مزاداتي</a>
                <a href="{{ route('subscriptions.index') }}" class="block rounded-lg px-2 py-2 hover:bg-white/10">الاشتراكات</a>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-right rounded-lg px-2 py-2 hover:bg-white/10">
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        @else
            <a href="{{ route('auth.login') }}" class="block rounded-lg px-3 py-2 bg-white/10 hover:bg-white/20">تسجيل الدخول</a>
            <a href="{{ route('auth.register') }}" class="block rounded-lg px-3 py-2 bg-yellow-400 text-black font-black hover:bg-yellow-300">التسجيل</a>
        @endauth
    </div>
</nav>
