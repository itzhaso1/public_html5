<script>
    const menuButton = document.getElementById('mobile-menu-button');
const mobileMenu = document.getElementById('mobile-menu');
menuButton.addEventListener('click', () => { mobileMenu.classList.toggle('hidden'); });
</script>

<!-- Country / Currency picker modal -->
<div id="countryPickerModal" class="hidden fixed inset-0 z-[9999] bg-black/60">
  <div class="min-h-full flex items-center justify-center p-4" dir="rtl">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-200 p-5">
      <div class="text-center">
        <div class="text-3xl">🌍</div>
        <h3 class="mt-2 text-xl font-extrabold text-gray-900">اختر بلدك</h3>
        <p class="mt-1 text-sm text-gray-600">سيتم حفظ الاختيار لتعديل الأسعار تلقائياً.</p>
      </div>

      <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
        <button type="button" data-pick-country="SA" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-bold hover:bg-gray-50">السعودية</button>
        <button type="button" data-pick-country="JO" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-bold hover:bg-gray-50">الأردن</button>
        <button type="button" data-pick-country="US" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-bold hover:bg-gray-50">دولار</button>
      </div>

      <div class="mt-4 text-[11px] text-gray-500">
        يمكنك تغيير البلد/العملة لاحقاً من صفحة الدفع أو الملف الشخصي.
      </div>
    </div>
  </div>
</div>
<!-- سكربت العملات -->
  <!-- سكربت العملات -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll(".currency-btn");
  const CACHE_KEY = "user_country_code";
  const CACHE_TTL = 6 * 60 * 60 * 1000;

  const normalizeCountryToCurrencyCountry = (code) => {
    const c = String(code || '').toUpperCase().trim();
    if (c === 'SA') return 'SA';
    if (c === 'JO') return 'JO';
    return 'US'; // default for all other countries
  };

  const writeCachedCountry = (countryCode) => {
    try {
      localStorage.setItem(CACHE_KEY, JSON.stringify({ code: countryCode, ts: Date.now() }));
    } catch (e) {}
  };

  buttons.forEach(btn => {
    btn.addEventListener("click", () => {
      const symbol = btn.dataset.symbol;
      const rate = parseFloat(btn.dataset.rate);
      const country = btn.dataset.country || "";

      document.querySelectorAll(".product-price").forEach(p => {
        const current = p.querySelector(".current-price");
        const old = p.querySelector(".old-price");
        const base = parseFloat(p.dataset.basePrice);
        const baseOld = parseFloat(p.dataset.baseOld);

        if (current && !isNaN(base)) {
          const converted = (base * rate).toFixed(2).replace(/\.00$/, "");
          current.textContent = `${symbol} ${converted}`;
        }

        if (old && !isNaN(baseOld)) {
          const convertedOld = (baseOld * rate).toFixed(2).replace(/\.00$/, "");
          old.textContent = convertedOld;
        }
      });

      // إبراز الزر النشط
      buttons.forEach(b => b.classList.remove("ring-2", "ring-yellow-500"));
      btn.classList.add("ring-2", "ring-yellow-500");

      if (country) {
        writeCachedCountry(country);
      }
    });
  });

  const applyCurrency = (countryCode) => {
    const btn = document.querySelector(`.currency-btn[data-country="${countryCode}"]`)
      || document.querySelector(`.currency-btn[data-country="SA"]`)
      || buttons[0];
    if (btn) btn.click();
  };

  const readCachedCountry = () => {
    try {
      const cached = JSON.parse(localStorage.getItem(CACHE_KEY) || "null");
      if (cached && cached.code && (Date.now() - cached.ts) < CACHE_TTL) return cached.code;
    } catch (e) {}
    return null;
  };

  const detectCountryFromBrowser = () => {
    try {
      const lang = String(navigator.language || '').toLowerCase();
      if (lang.includes('ar-sa') || lang.endsWith('-sa')) return 'SA';
      if (lang.includes('ar-jo') || lang.endsWith('-jo')) return 'JO';
    } catch (e) {}
    try {
      const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
      if (tz === 'Asia/Riyadh') return 'SA';
      if (tz === 'Asia/Amman') return 'JO';
    } catch (e) {}
    return null;
  };

  // Modal open/close helpers (manual)
  const modal = document.getElementById('countryPickerModal');
  const openModal = () => { if (modal) modal.classList.remove('hidden'); };
  const closeModal = () => { if (modal) modal.classList.add('hidden'); };

  // Allow pages to open the modal explicitly
  document.querySelectorAll('[data-open-country-picker]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      openModal();
    });
  });

  // Pick buttons inside modal
  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });

    modal.querySelectorAll('[data-pick-country]').forEach(el => {
      el.addEventListener('click', () => {
        const code = normalizeCountryToCurrencyCountry(el.getAttribute('data-pick-country'));
        writeCachedCountry(code);
        closeModal();
        if (buttons.length) applyCurrency(code);
      });
    });
  }

  // Auto-detect if no cached selection:
  // SA => SAR, JO => JOD, otherwise USD.
  const cachedCode = readCachedCountry();
  if (buttons.length) {
    if (cachedCode) {
      applyCurrency(normalizeCountryToCurrencyCountry(cachedCode));
    } else {
      const guessed = detectCountryFromBrowser();
      const detected = normalizeCountryToCurrencyCountry(guessed);
      writeCachedCountry(detected);
      applyCurrency(detected);
    }
  }
});
</script>

<!-- Copy to clipboard for payment info -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const toastId = 'copyToast';
  const showToast = (msg) => {
    let t = document.getElementById(toastId);
    if (!t) {
      t = document.createElement('div');
      t.id = toastId;
      t.style.position = 'fixed';
      t.style.left = '50%';
      t.style.bottom = '24px';
      t.style.transform = 'translateX(-50%)';
      t.style.zIndex = '99999';
      t.style.padding = '10px 14px';
      t.style.borderRadius = '12px';
      t.style.background = 'rgba(0,0,0,0.85)';
      t.style.color = '#fff';
      t.style.fontSize = '13px';
      t.style.fontWeight = '700';
      t.style.boxShadow = '0 10px 25px rgba(0,0,0,0.25)';
      t.style.opacity = '0';
      t.style.transition = 'opacity 160ms ease-in-out';
      document.body.appendChild(t);
    }
    t.textContent = msg || 'تم النسخ';
    t.style.opacity = '1';
    clearTimeout(window.__copyToastTimer);
    window.__copyToastTimer = setTimeout(() => { t.style.opacity = '0'; }, 900);
  };

  const fallbackCopy = (text) => {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.top = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
  };

  document.addEventListener('click', async (e) => {
    const el = e.target && e.target.closest
      ? (e.target.closest('.copy-trigger') || e.target.closest('.select-all'))
      : null;
    if (!el) return;
    const raw = (el.getAttribute('data-copy-text') || el.textContent || '').trim();
    if (!raw) return;
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(raw);
      } else {
        fallbackCopy(raw);
      }
      showToast('تم نسخ النص');
    } catch (err) {
      fallbackCopy(raw);
      showToast('تم النسخ');
    }
  });
});
</script>

    <!-- سكربت الشارات -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.product').forEach(p => {
                const status = p.dataset.status;
                const discount = p.dataset.discount;

                if (status === "مباع") {
                    const badge = document.createElement('div');
                    badge.className = "badge badge-sold";
                    badge.textContent = "مباع";
                    p.prepend(badge);
                    const btn = p.querySelector('button');
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add("bg-gray-400", "cursor-not-allowed");
                        btn.textContent = "مباع";
                    }
                }

                if (discount && !status) {
                    const badge = document.createElement('div');
                    badge.className = "badge badge-sale";
                    badge.textContent = `خصم ${discount}%`;
                    p.prepend(badge);

                    const priceEl = p.querySelector('[data-base-price]');
                    if (!priceEl) return;
                    const base = parseFloat(priceEl.getAttribute('data-base-price') || '0');
                    if (!base) return;
                    const newPrice = base - (base * (parseFloat(discount) / 100));
                    priceEl.textContent = `ر.س ${newPrice.toFixed(2)} (بدلاً من ${base})`;
                }
            });
        });
    </script>

    <!-- ترتيب المنتجات -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const productsContainer = document.querySelector('[data-sort-by-base-price]');
            if (!productsContainer) return;

            const products = Array.from(productsContainer.children || []);
            if (products.length < 2) return;

            const getPrice = (el) => {
                const priceEl = el && el.querySelector ? el.querySelector('.product-price[data-base-price]') : null;
                if (!priceEl) return 0;
                const raw = String(priceEl.getAttribute('data-base-price') || '').trim();
                const n = parseFloat(raw);
                return isNaN(n) ? 0 : n;
            };

            products.sort((a, b) => getPrice(b) - getPrice(a));
            products.forEach(p => productsContainer.appendChild(p));
        });
    </script>

    <!-- سلايدر Swiper -->
    <script>
        (function () {
            const hasSwiperElements = () => !!document.querySelector('.swiper-container, .swiper');
            if (!hasSwiperElements()) return;

            const initHeroSwipers = () => {
                document.querySelectorAll('.swiper-container').forEach((el) => {
                    if (el && el.swiper) return;
                    try {
                        new Swiper(el, {
                            loop: true,
                            autoplay: { delay: 3000 },
                            slidesPerView: 1,
                            spaceBetween: 0
                        });
                    } catch (e) {}
                });
            };

            const initReviewsSwiper = () => {
                const el = document.querySelector('.reviewsSwiper');
                if (!el || el.swiper) return;
                try {
                    const paginationEl = el.querySelector('.swiper-pagination');
                    new Swiper(el, {
                        loop: true,
                        autoplay: { delay: 3000, disableOnInteraction: false },
                        slidesPerView: 1.2,
                        spaceBetween: 12,
                        centeredSlides: true,
                        speed: 600,
                        effect: "slide",
                        pagination: paginationEl ? { el: paginationEl, clickable: true } : undefined,
                        breakpoints: {
                            480: { slidesPerView: 1.4 },
                            640: { slidesPerView: 2 },
                            1024: { slidesPerView: 3 },
                        },
                    });
                } catch (e) {}
            };

            const initHomeFeaturedAccountsSwiper = () => {
                document.querySelectorAll('.home-featured-accounts-swiper').forEach((el) => {
                    if (!el || el.swiper) return;
                    const slidesCount = el.querySelectorAll('.swiper-slide').length;
                    if (!slidesCount) return;
                    const paginationEl = el.querySelector('.swiper-pagination');
                    try {
                        new Swiper(el, {
                            loop: slidesCount > 1,
                            autoplay: slidesCount > 1 ? { delay: 3200, disableOnInteraction: false } : false,
                            centeredSlides: true,
                            watchOverflow: true,
                            grabCursor: true,
                            slidesPerView: 1,
                            spaceBetween: 10,
                            speed: 550,
                            breakpoints: {
                                640: { centeredSlides: true, slidesPerView: 1.3, spaceBetween: 14 },
                                768: { centeredSlides: false, slidesPerView: 1.9, spaceBetween: 16 },
                                1024: { centeredSlides: false, slidesPerView: 2.5, spaceBetween: 18 },
                            },
                            pagination: paginationEl ? { el: paginationEl, clickable: true } : undefined,
                        });
                    } catch (e) {}
                });
            };

            const initAll = () => {
                if (!window.Swiper) return;
                initHeroSwipers();
                initReviewsSwiper();
                initHomeFeaturedAccountsSwiper();
            };

            const loadSwiperOnce = (cb) => {
                if (window.Swiper) return cb();
                if (window.__swiperLoading) {
                    window.__swiperQueue = window.__swiperQueue || [];
                    window.__swiperQueue.push(cb);
                    return;
                }
                window.__swiperLoading = true;
                window.__swiperQueue = window.__swiperQueue || [];
                window.__swiperQueue.push(cb);

                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js';
                script.async = true;
                script.onload = () => {
                    window.__swiperLoading = false;
                    const q = window.__swiperQueue || [];
                    window.__swiperQueue = [];
                    q.forEach(fn => { try { fn(); } catch (e) {} });
                };
                script.onerror = () => { window.__swiperLoading = false; window.__swiperQueue = []; };
                document.head.appendChild(script);
            };

            const start = () => loadSwiperOnce(initAll);
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', start);
            } else {
                start();
            }
        })();
    </script>
    <!-- header style two End -->
    @stack('js')
    </body>
</html>
