<!-- شريط التحذير -->
<div class="bg-red-600 text-white py-2 overflow-hidden relative">
    <div class="marquee flex whitespace-nowrap">
        <span class="mx-4">تحذير: لا يوجد أرقام أو صفحات أو مواقع غير هذا. رقمنا: +9620777515306 | صفحة انستا:
            KING2GAME.COM | متجرنا: KING2GAME.COM</span>
        <span class="mx-4">تحذير: لا يوجد أرقام أو صفحات أو مواقع غير هذا. رقمنا: +9620777515306 | صفحة انستا:
            KING2GAME.COM | متجرنا: KING2GAME.COM</span>
    </div>
</div>

<!-- الناف بار -->
<nav class="bg-black text-white sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-row-reverse justify-between items-center h-10">
            <div class="flex-shrink-0 flex items-center">
               <a href="{{route('home')}}" >
                <img class="h-8 w-auto" src="{{ $logo}}" alt="{{ $settings?->name }}">
                      <a>
             
             
            </div>
            <div class="hidden md:flex space-x-4 items-center">
                <a href="{{route('home')}}" class="text-white hover:text-yellow-400 font-medium">الرئيسية</a>
                <a href="#" class="text-white hover:text-yellow-400 font-medium">المنتجات</a>
                <a href="#" class="text-white hover:text-yellow-400 font-medium">العروض</a>
                @auth
                    <a href="{{ route('customer.purchases') }}" class="text-white hover:text-yellow-400 font-medium">مشترياتي</a>
                    <a href="{{ route('customer.wallet.index') }}" class="text-white hover:text-yellow-400 font-medium">
                        محفظتي ({{ number_format((int)(auth()->user()?->wallet_points_balance ?? 0)) }})
                    </a>
                    <a href="{{ route('customer.profile') }}" class="text-white hover:text-yellow-400 font-medium">ملفي الشخصي</a>
                @endauth
                <a href="https://chat.whatsapp.com/LiEKm0hQPlB9yeToyetcbh" class="text-white hover:text-yellow-400 font-medium">تواصل معنا</a>
                @guest
                    <a href="{{ route('auth.login') }}" class="text-white hover:text-yellow-400 font-medium">تسجيل الدخول</a>
                @endguest
                @auth
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="text-white hover:text-yellow-400 font-medium">
                            تسجيل الخروج
                        </button>
                    </form>
                @endauth
            </div>
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-button" class="text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div class="md:hidden hidden px-2 pt-2 pb-3 space-y-1" id="mobile-menu">
        <a href="#" class="block text-white px-3 py-2 rounded hover:bg-gray-700">الرئيسية</a>
        <a href="#" class="block text-white px-3 py-2 rounded hover:bg-gray-700">المنتجات</a>
        <a href="#" class="block text-white px-3 py-2 rounded hover:bg-gray-700">العروض</a>
        @auth
            <a href="{{ route('customer.purchases') }}" class="block text-white px-3 py-2 rounded hover:bg-gray-700">مشترياتي</a>
            <a href="{{ route('customer.wallet.index') }}" class="block text-white px-3 py-2 rounded hover:bg-gray-700">محفظتي (نقاط)</a>
            <a href="{{ route('customer.profile') }}" class="block text-white px-3 py-2 rounded hover:bg-gray-700">ملفي الشخصي</a>
        @endauth
        <a href="https://chat.whatsapp.com/LiEKm0hQPlB9yeToyetcbh" class="block text-white px-3 py-2 rounded hover:bg-gray-700">تواصل معنا</a>
        @guest
            <a href="{{ route('auth.login') }}" class="block text-white px-3 py-2 rounded hover:bg-gray-700">تسجيل الدخول</a>
        @endguest
        @auth
            <form method="POST" action="{{ route('auth.logout') }}" class="px-3 py-2">
                @csrf
                <button type="submit" class="w-full text-right text-white px-0 py-0 rounded hover:text-yellow-400">
                    تسجيل الخروج
                </button>
            </form>
        @endauth
    </div>
</nav>
