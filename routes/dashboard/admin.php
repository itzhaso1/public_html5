<?php
 
use App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
 
/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
*/
 
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {
    Route::group(['middleware' => 'auth:admin', 'prefix' => 'admin', 'as' => 'admin.'], function () {
        
        Route::resource('admins', Dashboard\AdminController::class);
        Route::get('/link-password', [Dashboard\AdminController::class, 'showForm'])->name('link_password.form');
        Route::post('/link-password', [Dashboard\AdminController::class, 'verify'])->name('link_password.verify');
        
        Route::controller(Dashboard\MainSettingsController::class)->prefix('mainSettings')->as('mainSettings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('store', 'store')->name('store');
            Route::get('histories', 'history')->name('histories');
        });
        
        // =======================================================
        // ✅ (هام) تم إضافة رابط إضافة الشحن هنا (قبل products)
        // =======================================================
        Route::get('charge-items/create', [Dashboard\ProductController::class, 'createChargeProduct'])->name('products.create_charge');
        Route::post('charge-items/store', [Dashboard\ProductController::class, 'storeChargeProduct'])->name('products.store_charge');
        Route::post('charge-items/sync-offers', [Dashboard\ProductController::class, 'syncChargeOffers'])->name('products.sync_charge_offers');
        Route::post('charge-items/sync-offers-global', [Dashboard\ProductController::class, 'syncChargeOffersGlobal'])->name('products.sync_charge_offers_global');
        Route::post('charge-items/delete-non-global', [Dashboard\ProductController::class, 'deleteNonGlobalChargeOffers'])->name('products.delete_non_global_charge_offers');
        Route::post('charge-items/balance', [Dashboard\ProductController::class, 'chargeWalletBalance'])->name('products.charge_balance');
 
        // صفحات منفصلة لقوائم المنتجات (حسب النوع)
        // IMPORTANT: must be before Route::resource('products') so it doesn't match products/{product}
        Route::get('products/accounts', [Dashboard\ProductController::class, 'accounts'])->name('products.accounts');
        Route::get('products/charge', [Dashboard\ProductController::class, 'charge'])->name('products.charge');
        Route::get('products/codes', [Dashboard\ProductController::class, 'codes'])->name('products.codes');
        Route::post('products/bulk-delete/{group}', [Dashboard\ProductController::class, 'bulkDeleteByGroup'])
            ->whereIn('group', ['accounts', 'charge', 'codes'])
            ->name('products.bulk_delete');

        // الروابط الأصلية للمنتجات
        Route::resource('products', Dashboard\ProductController::class);
        Route::post('products/import', [Dashboard\ProductController::class, 'import'])->name('products.import');
        Route::post('products/test-erp-connection', [Dashboard\ProductController::class, 'exportProductsToERP'])->name('test-erp-connection');

        // طلبات نشر الحسابات من صفحة /publish-product (تحتاج موافقة قبل النشر)
        Route::prefix('public-products')->as('public_products.')->group(function () {
            Route::get('/', [Dashboard\PublicProductRequestController::class, 'index'])->name('index');
            Route::get('{product}', [Dashboard\PublicProductRequestController::class, 'show'])->name('show');
            Route::post('{product}/approve', [Dashboard\PublicProductRequestController::class, 'approve'])->name('approve');
            Route::post('{product}/reject', [Dashboard\PublicProductRequestController::class, 'reject'])->name('reject');
            Route::delete('{product}', [Dashboard\PublicProductRequestController::class, 'destroy'])->name('destroy');
        });

        // Merchant requests (diamonds traders)
        Route::prefix('merchant-requests')->as('merchant_requests.')->group(function () {
            Route::get('/', [Dashboard\MerchantRequestController::class, 'index'])->name('index');
            Route::post('{merchantRequest}/approve', [Dashboard\MerchantRequestController::class, 'approve'])->name('approve');
            Route::post('{merchantRequest}/reject', [Dashboard\MerchantRequestController::class, 'reject'])->name('reject');
        });

        // التصنيفات (الأقسام)
        Route::resource('categories', Dashboard\CategoryController::class);
        Route::post('categories/import', [Dashboard\CategoryController::class, 'import'])->name('categories.import');

        // أقسام الصفحة الرئيسية (Sections)
        Route::resource('sections', Dashboard\SectionController::class);

        Route::resource('users', Dashboard\UserController::class)->names('user')->only(['index', 'edit', 'update', 'destroy']);

        Route::prefix('manual-payments')->as('manual_payments.')->group(function () {
            Route::get('/', [Dashboard\ManualPaymentController::class, 'index'])->name('index');
            Route::post('bulk-delete', [Dashboard\ManualPaymentController::class, 'bulkDelete'])->name('bulk_delete');
            Route::post('delete-all', [Dashboard\ManualPaymentController::class, 'deleteAll'])->name('delete_all');
            Route::get('{manualPaymentRequest}', [Dashboard\ManualPaymentController::class, 'show'])->name('show');
            Route::get('{manualPaymentRequest}/receipt', [Dashboard\ManualPaymentController::class, 'receipt'])->name('receipt');
            Route::post('{manualPaymentRequest}/transaction', [Dashboard\ManualPaymentController::class, 'checkTransaction'])->name('transaction');
            Route::post('{manualPaymentRequest}/approve', [Dashboard\ManualPaymentController::class, 'approve'])->name('approve');
            Route::post('{manualPaymentRequest}/reject', [Dashboard\ManualPaymentController::class, 'reject'])->name('reject');
            Route::delete('{manualPaymentRequest}', [Dashboard\ManualPaymentController::class, 'destroy'])->name('destroy');
        });

        // Wallet points orders (separated from manual bank transfer)
        Route::prefix('wallet-points-orders')->as('wallet_points_orders.')->group(function () {
            Route::get('/', [Dashboard\WalletPointsOrderController::class, 'index'])->name('index');
            Route::get('{manualPaymentRequest}', [Dashboard\WalletPointsOrderController::class, 'show'])->name('show');
            Route::post('{manualPaymentRequest}/refresh', [Dashboard\WalletPointsOrderController::class, 'refreshTransaction'])->name('refresh');
            Route::delete('{manualPaymentRequest}', [Dashboard\WalletPointsOrderController::class, 'destroy'])->name('destroy');
        });

        // Wallet top-ups (points deposits)
        Route::prefix('wallet-topups')->as('wallet_topups.')->group(function () {
            Route::get('/', [Dashboard\WalletTopupRequestController::class, 'index'])->name('index');
            Route::get('{walletTopupRequest}/receipt', [Dashboard\WalletTopupRequestController::class, 'receipt'])->name('receipt');
            Route::post('{walletTopupRequest}/approve', [Dashboard\WalletTopupRequestController::class, 'approve'])->name('approve');
            Route::post('{walletTopupRequest}/reject', [Dashboard\WalletTopupRequestController::class, 'reject'])->name('reject');
        });

        Route::prefix('diamond-codes')->as('diamond_codes.')->group(function () {
            Route::get('/', [Dashboard\DiamondCodeController::class, 'index'])->name('index');
            Route::get('create', [Dashboard\DiamondCodeController::class, 'create'])->name('create');
            Route::post('/', [Dashboard\DiamondCodeController::class, 'store'])->name('store');
            Route::get('{diamondCode}/image', [Dashboard\DiamondCodeController::class, 'image'])->name('image');
            Route::delete('{diamondCode}', [Dashboard\DiamondCodeController::class, 'destroy'])->name('destroy');

            // Manage "codes" products quickly from the codes inventory screen
            Route::patch('product/{product}', [Dashboard\DiamondCodeController::class, 'updateProduct'])->name('product.update');
            Route::delete('product/{product}', [Dashboard\DiamondCodeController::class, 'destroyProduct'])->name('product.destroy');
        });

        // Cash Exchange (استبدل رصيدك كاش)
        Route::prefix('cash-exchange')->as('cash_exchange.')->group(function () {
            Route::get('offers', [Dashboard\CashExchangeOfferController::class, 'index'])->name('offers.index');
            Route::get('offers/create', [Dashboard\CashExchangeOfferController::class, 'create'])->name('offers.create');
            Route::post('offers', [Dashboard\CashExchangeOfferController::class, 'store'])->name('offers.store');
            Route::get('offers/{offer}/edit', [Dashboard\CashExchangeOfferController::class, 'edit'])->name('offers.edit');
            Route::put('offers/{offer}', [Dashboard\CashExchangeOfferController::class, 'update'])->name('offers.update');
            Route::delete('offers/{offer}', [Dashboard\CashExchangeOfferController::class, 'destroy'])->name('offers.destroy');

            Route::get('requests', [Dashboard\CashExchangeRequestController::class, 'index'])->name('requests.index');
            Route::post('requests/bulk-delete', [Dashboard\CashExchangeRequestController::class, 'bulkDelete'])->name('requests.bulk_delete');
            Route::post('requests/delete-all', [Dashboard\CashExchangeRequestController::class, 'deleteAll'])->name('requests.delete_all');
            Route::get('requests/{cashExchangeRequest}', [Dashboard\CashExchangeRequestController::class, 'show'])->name('requests.show');
            Route::post('requests/{cashExchangeRequest}/note', [Dashboard\CashExchangeRequestController::class, 'updateNote'])->name('requests.note');
            Route::post('requests/{cashExchangeRequest}/complete', [Dashboard\CashExchangeRequestController::class, 'complete'])->name('requests.complete');
            Route::post('requests/{cashExchangeRequest}/reject', [Dashboard\CashExchangeRequestController::class, 'reject'])->name('requests.reject');
            Route::delete('requests/{cashExchangeRequest}', [Dashboard\CashExchangeRequestController::class, 'destroy'])->name('requests.destroy');
        });

        // Money Exchange (SAR ↔ USDT)
        Route::prefix('money-exchange')->as('money_exchange.')->group(function () {
            Route::get('settings', [Dashboard\MoneyExchangeSettingController::class, 'edit'])->name('settings.edit');
            Route::post('settings', [Dashboard\MoneyExchangeSettingController::class, 'update'])->name('settings.update');

            Route::get('requests', [Dashboard\MoneyExchangeRequestController::class, 'index'])->name('requests.index');
            Route::post('requests/bulk-delete', [Dashboard\MoneyExchangeRequestController::class, 'bulkDelete'])->name('requests.bulk_delete');
            Route::post('requests/delete-all', [Dashboard\MoneyExchangeRequestController::class, 'deleteAll'])->name('requests.delete_all');
            Route::get('requests/{moneyExchangeRequest}', [Dashboard\MoneyExchangeRequestController::class, 'show'])->name('requests.show');
            Route::post('requests/{moneyExchangeRequest}/complete', [Dashboard\MoneyExchangeRequestController::class, 'complete'])->name('requests.complete');
            Route::post('requests/{moneyExchangeRequest}/reject', [Dashboard\MoneyExchangeRequestController::class, 'reject'])->name('requests.reject');
            Route::delete('requests/{moneyExchangeRequest}', [Dashboard\MoneyExchangeRequestController::class, 'destroy'])->name('requests.destroy');
        });
        
        Route::get('dashboard', Dashboard\DashboardController::class)->name('dashboard');
    });
});