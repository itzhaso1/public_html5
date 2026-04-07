<footer class="bg-black text-white py-8">
  <div class="max-w-7xl mx-auto px-6 sm:px-12 flex flex-row flex-wrap justify-center sm:justify-between items-start gap-10 text-center sm:text-right">

    <!-- قسم الدفع -->
    <div class="flex flex-col items-center sm:items-start">
      <h2 class="font-bold text-lg mb-3 tracking-wide">طرق الدفع</h2>
      <div class="flex justify-center sm:justify-start gap-4">
        <!-- Visa -->
        <svg class="h-6 w-10 text-white hover:text-blue-400 transition" viewBox="0 0 48 16" fill="currentColor">
          <rect width="48" height="16" rx="2" fill="currentColor" />
          <text x="50%" y="65%" text-anchor="middle" font-size="8" fill="black" font-family="Arial Black">VISA</text>
        </svg>
        <!-- MasterCard -->
        <div class="flex items-center -space-x-2">
          <div class="h-6 w-6 bg-red-600 rounded-full opacity-90"></div>
          <div class="h-6 w-6 bg-yellow-400 rounded-full opacity-90"></div>
        </div>
        <!-- PayPal -->
        <svg class="h-6 w-10 text-blue-400 hover:text-blue-300 transition" viewBox="0 0 48 16" fill="currentColor">
          <rect width="48" height="16" rx="2" fill="currentColor" />
          <text x="50%" y="65%" text-anchor="middle" font-size="7" fill="white" font-family="Arial Black">PayPal</text>
        </svg>
      </div>
    </div>

    <!-- روابط مهمة -->
    <div class="flex flex-col items-center sm:items-start">
      <h2 class="font-bold text-lg mb-3 tracking-wide">روابط مهمة</h2>
      <ul class="space-y-2 text-sm text-gray-300">
        <li><a href="{{ route('home') }}" class="hover:text-yellow-400 transition">الرئيسية</a></li>
        <li><a href="{{ route('shop.index') }}" class="hover:text-yellow-400 transition">المتجر</a></li>
        <li><a href="{{ route('about') }}" class="hover:text-yellow-400 transition">من نحن</a></li>
        <li><a href="{{ route('contact') }}" class="hover:text-yellow-400 transition">اتصل بنا</a></li>
        <li><a href="{{ route('privacy') }}" class="hover:text-yellow-400 transition">سياسة الخصوصية</a></li>
        <li><a href="{{ route('refund_policy') }}" class="hover:text-yellow-400 transition">سياسة الإرجاع والاسترداد</a></li>
        <li><a href="{{ route('terms_and_conditions') }}" class="hover:text-yellow-400 transition">الشروط والأحكام</a></li>
        <li><a href="{{ route('delivery_policy') }}" class="hover:text-yellow-400 transition">سياسة التسليم</a></li>
      </ul>
    </div>

    <!-- قسم التواصل -->
    <div class="flex flex-col items-center sm:items-start">
      <h2 class="font-bold text-lg mb-3 tracking-wide">تواصل معنا</h2>
      <ul class="space-y-2 text-sm text-gray-300">
        <li>
          <a href="https://wa.me/9620777515306" class="flex items-center justify-center sm:justify-start gap-2 hover:text-green-400 transition">
            <svg class="h-4 w-4 text-green-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.52 3.48A11.77 11.77 0 0 0 12 0C5.38 0 0 5.24 0 11.71a11.6 11.6 0 0 0 1.59 5.93L0 24l6.58-1.68a11.84 11.84 0 0 0 5.42 1.34h.01c6.63 0 12-5.25 12-11.71a11.5 11.5 0 0 0-3.49-8.15zM12 21.07a9.9 9.9 0 0 1-5.07-1.37l-.36-.21-3.9 1L3.9 16.8l-.23-.37a9.74 9.74 0 0 1-1.47-5.12c0-5.33 4.45-9.66 9.9-9.66a9.9 9.9 0 0 1 7.01 2.82 9.52 9.52 0 0 1 2.92 6.84c0 5.33-4.45 9.66-9.9 9.66z"/>
              <path d="M17.27 14.34c-.29-.14-1.74-.86-2.01-.96s-.47-.14-.67.14-.77.96-.94 1.16-.35.22-.64.07a7.93 7.93 0 0 1-2.34-1.4 8.9 8.9 0 0 1-1.64-2 2.25 2.25 0 0 1-.48-1.18c0-.13.09-.28.19-.42s.29-.35.38-.47a1.36 1.36 0 0 0 .19-.35.43.43 0 0 0 0-.42c-.08-.14-.67-1.6-.91-2.19-.24-.58-.49-.5-.67-.5h-.57a1.1 1.1 0 0 0-.8.37A3.29 3.29 0 0 0 5 8.41a5.73 5.73 0 0 0 1.18 3.01 13.18 13.18 0 0 0 5.05 4.35c.71.3 1.26.48 1.69.61a4 4 0 0 0 1.84.11 3.08 3.08 0 0 0 2.07-1.47 2.52 2.52 0 0 0 .19-1.46c-.08-.14-.26-.22-.45-.3z"/>
            </svg>
            واتساب
          </a>
        </li>
        <li>
          <a href="https://instagram.com/king2game.com" class="flex items-center justify-center sm:justify-start gap-2 hover:text-pink-400 transition">
            <svg class="h-4 w-4 text-pink-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.16c3.2 0 3.584.013 4.85.07 1.366.062 2.633.347 3.608 1.322.975.975 1.26 2.242 1.322 3.608.057 1.266.07 1.65.07 4.85s-.013 3.584-.07 4.85c-.062 1.366-.347 2.633-1.322 3.608-.975.975-2.242 1.26-3.608 1.322-1.266.057-1.65.07-4.85.07s-3.584-.013-4.85-.07c-1.366-.062-2.633-.347-3.608-1.322-.975-.975-1.26-2.242-1.322-3.608C2.173 15.584 2.16 15.2 2.16 12s.013-3.584.07-4.85c.062-1.366.347-2.633 1.322-3.608.975-.975 2.242-1.26 3.608-1.322C8.416 2.173 8.8 2.16 12 2.16z"/>
              <circle cx="12" cy="12" r="3.5"/>
            </svg>
            إنستغرام
          </a>
        </li>
        <li>
          <a href="mailto:king2game.com@gmail.com" class="flex items-center justify-center sm:justify-start gap-2 hover:text-yellow-400 transition">
            📧 king2game.com@gmail.com
          </a>
        </li>
      </ul>
    </div>

  </div>

  <!-- الحقوق -->
  <div class="text-center text-xs text-gray-400 mt-8 border-t border-white/10 pt-3 px-4">
    جميع الحقوق محفوظة © 2025 <span class="font-semibold text-white">متجر الممالك</span>
  </div>
</footer>
