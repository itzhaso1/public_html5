<?php
 
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
 
use App\Http\Controllers\Website;
use App\Http\Controllers\Website\Customer;
use App\Http\Controllers\PublicProductController;
use App\Models\Product;
use App\Models\Setting;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Website\WalletPointsPaymentController;
use App\Http\Controllers\Website\Customer\WalletController as CustomerWalletController;
 
Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => [
            'localeSessionRedirect',
            'localizationRedirect',
            'localeViewPath'
        ]
    ],
    function () {
        // ===============================
        // Clear cache
        // ===============================
        if (app()->environment('local')) {
            Route::get('clear-cache', function () {
                Artisan::call('optimize:clear');
                return 'Cache cleared!';
            });
        }
 
        // ===============================
        // Auth
        // ===============================
        Route::get('login', [Website\AuthController::class, 'showLoginForm'])->name('auth.login');
        Route::post('login', [Website\AuthController::class, 'login'])->name('auth.login.submit');
        Route::get('register', [Website\AuthController::class, 'showRegisterForm'])->name('auth.register');
        Route::post('register', [Website\AuthController::class, 'register'])->name('auth.register.submit');
        Route::post('logout', [Website\AuthController::class, 'logout'])->middleware('auth')->name('auth.logout');
 
        // ===============================
        // Diamonds Sections ✅ (مصحح ومحمي)
        // ===============================
        Route::get('diamonds/charge', function () {
            try {
                if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'charge_enabled')) {
                    $s = Cache::get('app_settings') ?: Setting::query()->latest()->first();
                    if (! (bool) ($s?->charge_enabled ?? true)) {
                        return redirect()->route('home')->with('error', 'قسم الشحن غير متاح حالياً.');
                    }
                }
            } catch (\Throwable $e) {}

            $locale = app()->getLocale();
            $products = Cache::remember("diamonds.charge.$locale", 60 * 5, function () {
                return Product::query()
                    ->where('service_type', 'gems')
                    ->with(['media', 'translations'])
                    ->get();
            });

            return view('website.diamonds.charge', compact('products'));
        })->name('website.diamonds.charge');
 
        Route::get('diamonds/codes', function () {
            try {
                if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'codes_enabled')) {
                    $s = Cache::get('app_settings') ?: Setting::query()->latest()->first();
                    if (! (bool) ($s?->codes_enabled ?? true)) {
                        return redirect()->route('home')->with('error', 'قسم الأكواد غير متاح حالياً.');
                    }
                }
            } catch (\Throwable $e) {}

            $locale = app()->getLocale();
            $products = Cache::remember("diamonds.codes.$locale", 60 * 5, function () {
                return Product::query()
                    ->where('service_type', 'codes')
                    ->withCount([
                        'diamondCodes as available_codes_count' => function ($q) {
                            $q->where('status', 'available');
                        },
                        'manualPaymentRequests as pending_manual_requests_count' => function ($q) {
                            $q->where('status', 'pending');
                        },
                    ])
                    // Use a correlated WHERE (more compatible than HAVING for SQLite).
                    ->whereRaw(
                        '(select count(*) from diamond_codes dc where dc.product_id = products.id and dc.status = ?) >
                         (select count(*) from manual_payment_requests mpr where mpr.product_id = products.id and mpr.status = ?)',
                        ['available', 'pending']
                    )
                    ->with(['media', 'translations', 'codeThumbnail'])
                    ->get();
            });

            return view('website.diamonds.codes', compact('products'));
        })->name('website.diamonds.codes');

        // أنت وحظك (Lucky draw codes)
        Route::get('diamonds/lucky-codes', function () {
            try {
                if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'codes_enabled')) {
                    $s = Cache::get('app_settings') ?: Setting::query()->latest()->first();
                    if (! (bool) ($s?->codes_enabled ?? true)) {
                        return redirect()->route('home')->with('error', 'قسم الأكواد غير متاح حالياً.');
                    }
                }
            } catch (\Throwable $e) {}

            $product = Product::query()
                ->where('service_type', 'codes')
                ->where('is_lucky_draw_codes', true)
                ->with(['media', 'translations'])
                ->latest('id')
                ->first();

            if (! $product) {
                return redirect()
                    ->route('website.diamonds.codes')
                    ->withErrors(['error' => 'قسم (انت وحظك) غير جاهز بعد. أنشئ منتج أكواد وضع عليه خيار “انت وحظك” ثم أضف أكواد.']);
            }

            $samples = \App\Models\DiamondCode::query()
                ->where('product_id', $product->id)
                ->whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->where('status', 'available')
                ->latest('id')
                ->limit(12)
                ->get(['id', 'image_path', 'luck_label']);

            return view('website.diamonds.lucky_codes', [
                'product' => $product,
                'samples' => $samples,
                'pageTitle' => 'انت وحظك - أكواد ملابس فري فاير',
            ]);
        })->name('website.diamonds.lucky_codes');

        // Manual bank transfer flow (upload receipt, pending approval)
        Route::get('diamonds/{product}/manual-payment', [Website\ManualPaymentController::class, 'create'])
            ->middleware('auth')
            ->name('website.diamonds.manual_payment.create');
        Route::post('diamonds/{product}/manual-payment', [Website\ManualPaymentController::class, 'store'])
            ->middleware('auth')
            ->name('website.diamonds.manual_payment.store');

        // Wallet points payment (create a pending request paid with points)
        Route::get('diamonds/{product}/points-payment', [WalletPointsPaymentController::class, 'create'])
            ->middleware('auth')
            ->name('website.diamonds.points_payment.create');
        Route::post('diamonds/{product}/points-payment', [WalletPointsPaymentController::class, 'store'])
            ->middleware('auth')
            ->name('website.diamonds.points_payment.store');
        Route::post('diamonds/check-player', [Website\ManualPaymentController::class, 'checkPlayerName'])
            ->middleware(['auth', 'throttle:5,1'])
            ->name('website.diamonds.check_player');
        Route::get('diamonds/manual-payment/thanks/{reference}', [Website\ManualPaymentController::class, 'thanks'])
            ->name('website.diamonds.manual_payment.thanks');

        // ===============================
        // Cash Exchange (استبدل رصيدك كاش)
        // ===============================
        Route::get('cash-exchange', [Website\CashExchangeController::class, 'index'])
            ->middleware('auth')
            ->name('website.cash_exchange.index');
        Route::post('cash-exchange', [Website\CashExchangeController::class, 'store'])
            ->middleware('auth')
            ->name('website.cash_exchange.store');
        Route::get('cash-exchange/thanks/{reference}', [Website\CashExchangeController::class, 'thanks'])
            ->middleware('auth')
            ->name('website.cash_exchange.thanks');
        Route::get('cash-exchange/requests/{reference}', [Website\CashExchangeController::class, 'show'])
            ->middleware('auth')
            ->name('website.cash_exchange.show');

        // ===============================
        // Money Exchange (تحويل الأموال / تبادل العملات)
        // ===============================
        Route::get('money-exchange', [Website\MoneyExchangeController::class, 'index'])
            ->middleware('auth')
            ->name('website.money_exchange.index');
        Route::post('money-exchange', [Website\MoneyExchangeController::class, 'store'])
            ->middleware('auth')
            ->name('website.money_exchange.store');
        Route::get('money-exchange/thanks/{reference}', [Website\MoneyExchangeController::class, 'thanks'])
            ->middleware('auth')
            ->name('website.money_exchange.thanks');
 
        // ===============================
        // Website pages
        // ===============================
        Route::get('/', Website\WebsiteController::class)->name('home');
        Route::get('about-us', Website\AboutController::class)->name('about');
        Route::get('contact-us', Website\ContactUsController::class)->name('contact');
        Route::get('privacy-policy', Website\PrivacyController::class)->name('privacy');
        Route::view('refund-policy', 'website.pages.refund_policy', [
            'pageTitle' => 'سياسة الإرجاع والاسترداد',
        ])->name('refund_policy');
        Route::view('terms-and-conditions', 'website.pages.terms_and_conditions', [
            'pageTitle' => 'الشروط والأحكام',
        ])->name('terms_and_conditions');
        Route::view('delivery-policy', 'website.pages.delivery_policy', [
            'pageTitle' => 'سياسة التسليم',
        ])->name('delivery_policy');
 
        // ===============================
        // Shop
        // ===============================
        Route::get('shop', [Website\ShopController::class, 'index'])->name('shop.index');
        // Legacy publish URL used by some users: /ar/product
        Route::get('product', [PublicProductController::class, 'create'])->name('public.products.legacy_create');
        Route::post('product', [PublicProductController::class, 'store'])->name('public.products.legacy_store');
        Route::get('product/{product}', [Website\WebsiteController::class, 'show'])->name('website.product.show');
        Route::post('product/{productId}/unlock-client', [Website\ShopController::class, 'unlockClientNumber'])->name('product.unlock.client');
 
        // ===============================
        // Publish product
        // ===============================
        Route::get('publish-product', [PublicProductController::class, 'create'])->name('public.products.create');
        Route::post('publish-product', [PublicProductController::class, 'store'])->name('public.products.store');
        Route::get('publish-product-admin', [PublicProductController::class, 'createAdmin'])->name('public.products.create_admin');
        Route::post('publish-product-admin', [PublicProductController::class, 'storeAdmin'])->name('public.products.store_admin');
        Route::get('publish-product/requests/{slug}', [PublicProductController::class, 'track'])->name('public.products.track');

        // ===============================
        // Merchant requests (Diamonds charge)
        // ===============================
        Route::get('merchant/apply', [Website\MerchantController::class, 'create'])
            ->middleware('auth')
            ->name('website.merchant.apply');
        Route::post('merchant/apply', [Website\MerchantController::class, 'store'])
            ->middleware('auth')
            ->name('website.merchant.apply.store');

        // ===============================
        // Password reset (website users)
        // ===============================
        Route::middleware('guest')->group(function () {
            Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
            Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
            Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
            Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
        });
 
        // ===============================
        // Customer dashboard
        // ===============================
        Route::group(['middleware' => 'auth', 'prefix' => 'customer', 'as' => 'customer.'], function () {
            Route::get('/', [Customer\DashboardController::class, 'index'])->name('dashboard');
            Route::get('orders/{status?}', [Customer\DashboardController::class, 'ordersByStatus'])->name('orders_by_status');
            Route::get('orders/show/{order}', [Customer\DashboardController::class, 'showPartial'])->name('orders.partial');
            Route::get('track', [Customer\DashboardController::class, 'trackOrder'])->name('track.order');
            Route::get('purchases', [Customer\PurchasesController::class, 'index'])->name('purchases');
            Route::post('purchases/{manualPaymentRequest}/refresh-shop2topup', [Customer\PurchasesController::class, 'refreshShop2Topup'])
                ->middleware('throttle:10,1')
                ->name('purchases.refresh_shop2topup');
            Route::get('diamond-codes/{diamondCode}/image', [Customer\DiamondCodeController::class, 'image'])
                ->name('diamond_codes.image');
            Route::get('profile', [Customer\ProfileController::class, 'edit'])->name('profile');
            Route::post('profile', [Customer\ProfileController::class, 'update'])->name('profile.update');
            Route::post('profile/password', [Customer\ProfileController::class, 'updatePassword'])->name('profile.password');

            // Wallet (points)
            Route::get('wallet', [CustomerWalletController::class, 'index'])->name('wallet.index');
            Route::get('wallet/topup', [CustomerWalletController::class, 'createTopup'])->name('wallet.topup');
            Route::post('wallet/topup', [CustomerWalletController::class, 'storeTopup'])->name('wallet.topup.store');
            Route::get('wallet/topups/{walletTopupRequest}/receipt', [CustomerWalletController::class, 'receipt'])
                ->name('wallet.topups.receipt');

            // Money exchange tracking
            Route::get('money-exchange', [Website\MoneyExchangeController::class, 'list'])->name('money_exchange.index');
            Route::get('money-exchange/{reference}', [Website\MoneyExchangeController::class, 'show'])->name('money_exchange.show');
            
            
        });
    }
);