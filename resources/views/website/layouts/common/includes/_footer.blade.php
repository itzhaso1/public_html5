<footer class="relative mt-14 border-t border-white/10 bg-gradient-to-b from-zinc-950 via-black to-zinc-950 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 sm:py-10">
    <div class="rounded-3xl border border-white/10 bg-white/[0.03] backdrop-blur-sm shadow-[0_20px_60px_rgba(0,0,0,0.35)] p-3 sm:p-5">
      <div class="overflow-x-auto">
        <div class="min-w-[920px] grid grid-cols-3 gap-3 sm:gap-4">
          <!-- قسم الدفع -->
          <div class="rounded-2xl border border-white/10 bg-gradient-to-br from-white/10 to-white/[0.02] p-4 sm:p-5">
            <h2 class="font-extrabold text-base sm:text-lg mb-3 tracking-wide text-yellow-300">طرق الدفع</h2>
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
          <div class="rounded-2xl border border-white/10 bg-gradient-to-br from-white/10 to-white/[0.02] p-4 sm:p-5">
            <h2 class="font-extrabold text-base sm:text-lg mb-3 tracking-wide text-yellow-300">روابط مهمة</h2>
            <ul class="grid grid-cols-2 gap-y-2 gap-x-3 text-sm text-gray-200">
              <li><a href="{{ route('home') }}" class="hover:text-yellow-300 transition">الرئيسية</a></li>
              <li><a href="{{ route('shop.index') }}" class="hover:text-yellow-300 transition">المتجر</a></li>
              <li><a href="{{ route('about') }}" class="hover:text-yellow-300 transition">من نحن</a></li>
              <li><a href="{{ route('contact') }}" class="hover:text-yellow-300 transition">اتصل بنا</a></li>
              <li><a href="{{ route('privacy') }}" class="hover:text-yellow-300 transition">الخصوصية</a></li>
              <li><a href="{{ route('refund_policy') }}" class="hover:text-yellow-300 transition">الاسترداد</a></li>
              <li><a href="{{ route('terms_and_conditions') }}" class="hover:text-yellow-300 transition">الشروط</a></li>
              <li><a href="{{ route('delivery_policy') }}" class="hover:text-yellow-300 transition">التسليم</a></li>
            </ul>
          </div>

          <!-- تواصل معنا -->
          <div class="rounded-2xl border border-white/10 bg-gradient-to-br from-white/10 to-white/[0.02] p-4 sm:p-5">
            <h2 class="font-extrabold text-base sm:text-lg mb-3 tracking-wide text-yellow-300">تواصل معنا</h2>
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
      </div>

      <div class="text-center text-xs text-gray-400 mt-5 border-t border-white/10 pt-4 px-2">
        جميع الحقوق محفوظة © 2025 <span class="font-semibold text-white">متجر الممالك</span>
      </div>
    </div>
  </div>
</footer>
