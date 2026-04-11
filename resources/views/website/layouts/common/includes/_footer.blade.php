<footer class="relative mt-14 border-t border-white/10 bg-black text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 sm:py-10">
    <div class="rounded-2xl border border-white/10 bg-zinc-950 shadow-[0_18px_45px_rgba(0,0,0,0.45)] p-4 sm:p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        <!-- قسم الدفع -->
        <div class="rounded-xl border border-white/10 bg-zinc-900 p-4">
          <h2 class="font-extrabold text-base sm:text-lg mb-3 text-yellow-300">طرق الدفع</h2>
          <p class="text-xs text-gray-300 mb-3">دفع آمن وسريع لطلباتك الرقمية.</p>
          <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center h-8 w-14 rounded-lg bg-white text-black text-[10px] font-black tracking-wider">VISA</span>
            <span class="inline-flex items-center h-8 px-2 rounded-lg bg-white/90">
              <span class="h-5 w-5 bg-red-600 rounded-full opacity-90"></span>
              <span class="-mr-2 h-5 w-5 bg-yellow-400 rounded-full opacity-90"></span>
            </span>
            <span class="inline-flex items-center justify-center h-8 w-14 rounded-lg bg-blue-500 text-white text-[10px] font-black tracking-wider">PAYPAL</span>
          </div>
        </div>

        <!-- روابط مهمة -->
        <div class="rounded-xl border border-white/10 bg-zinc-900 p-4">
          <h2 class="font-extrabold text-base sm:text-lg mb-3 text-yellow-300">روابط مهمة</h2>
          <ul class="grid grid-cols-2 gap-y-2 gap-x-3 text-sm text-gray-200">
            <li><a href="{{ route('home') }}" class="hover:text-yellow-300 transition">الرئيسية</a></li>
            <li><a href="{{ route('shop.index') }}" class="hover:text-yellow-300 transition">المتجر</a></li>
            <li><a href="{{ route('about') }}" class="hover:text-yellow-300 transition">من نحن</a></li>
            <li><a href="{{ route('contact') }}" class="hover:text-yellow-300 transition">اتصل بنا</a></li>
            @if(\Illuminate\Support\Facades\Route::has('privacy'))
              <li><a href="{{ route('privacy') }}" class="hover:text-yellow-300 transition">الخصوصية</a></li>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('refund_policy'))
              <li><a href="{{ route('refund_policy') }}" class="hover:text-yellow-300 transition">الاسترداد</a></li>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('terms_and_conditions'))
              <li><a href="{{ route('terms_and_conditions') }}" class="hover:text-yellow-300 transition">الشروط</a></li>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('delivery_policy'))
              <li><a href="{{ route('delivery_policy') }}" class="hover:text-yellow-300 transition">التسليم</a></li>
            @endif
          </ul>
        </div>

        <!-- تواصل معنا -->
        <div class="rounded-xl border border-white/10 bg-zinc-900 p-4">
          <h2 class="font-extrabold text-base sm:text-lg mb-3 text-yellow-300">تواصل معنا</h2>
          <ul class="space-y-2.5 text-sm text-gray-200">
            <li>
              <a href="https://wa.me/9620777515306" class="inline-flex items-center gap-2 hover:text-green-400 transition">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-500/15">💬</span>
                واتساب
              </a>
            </li>
            <li>
              <a href="https://instagram.com/king2game.com" class="inline-flex items-center gap-2 hover:text-pink-400 transition">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-pink-500/15">📷</span>
                إنستغرام
              </a>
            </li>
            <li>
              <a href="mailto:king2game.com@gmail.com" class="inline-flex items-center gap-2 hover:text-yellow-300 transition">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-500/15">✉️</span>
                king2game.com@gmail.com
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="text-center text-xs text-gray-400 mt-5 border-t border-white/10 pt-4 px-2">
        جميع الحقوق محفوظة © 2025 <span class="font-semibold text-white">متجر الممالك</span>
      </div>
    </div>
  </div>
</footer>
