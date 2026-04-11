<div class="sidebar">
    <div class="menu-item">
        <div class="pb-2 menu-content">
            <span class="menu-section text-muted text-uppercase fs-8 ls-1">
                {{ 'ADMIN | ' . ($settings?->name ?? '') }}
            </span>
        </div>
    </div>

    <!-- Dashboard -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.dashboard') }}" href="{{ route('admin.dashboard') }}">
            <span class="menu-icon"><i class="bi bi-house fs-2"></i></span>
            <span class="menu-title">لوحة التحكم</span>
        </a>
    </div>

    <!-- Products (separate from other store tools) -->
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ is_active('admin.products.*') }}">
        <span class="menu-link {{ is_active('admin.products.*') }}">
            <span class="menu-icon"><i class="bi bi-box-seam fs-2"></i></span>
            <span class="menu-title">المنتجات</span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion menu-active-bg">
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.products.accounts') }}" href="{{ route('admin.products.accounts') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">الحسابات</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.products.charge') }}" href="{{ route('admin.products.charge') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">باقات الشحن</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.products.codes') }}" href="{{ route('admin.products.codes') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">منتجات الأكواد</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Add product (separate item, not inside products) -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.products.create') }}" href="{{ route('admin.products.create') }}">
            <span class="menu-icon"><i class="bi bi-plus-square fs-2"></i></span>
            <span class="menu-title">إضافة منتج</span>
        </a>
    </div>

    <!-- Public publish requests (publish-product) -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.public_products.*') }}" href="{{ route('admin.public_products.index') }}">
            <span class="menu-icon"><i class="bi bi-clipboard-check fs-2"></i></span>
            <span class="menu-title">طلبات نشر الحسابات</span>
        </a>
    </div>

    <!-- Merchant requests -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.merchant_requests.*') }}" href="{{ route('admin.merchant_requests.index') }}">
            <span class="menu-icon"><i class="bi bi-person-badge fs-2"></i></span>
            <span class="menu-title">طلبات التجار</span>
        </a>
    </div>

    <!-- Sections (homepage groupings) -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.sections.*') }}" href="{{ route('admin.sections.index') }}">
            <span class="menu-icon"><i class="bi bi-columns-gap fs-2"></i></span>
            <span class="menu-title">الأقسام</span>
        </a>
    </div>

    <!-- Charge products -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.products.create_charge') }}" href="{{ route('admin.products.create_charge') }}">
            <span class="menu-icon"><i class="bi bi-gem fs-2"></i></span>
            <span class="menu-title">إضافة منتج شحن</span>
        </a>
    </div>

    <!-- Manual payments -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.manual_payments.*') }}" href="{{ route('admin.manual_payments.index') }}">
            <span class="menu-icon"><i class="bi bi-receipt fs-2"></i></span>
            <span class="menu-title">طلبات الدفع اليدوي</span>
        </a>
    </div>

    <!-- Payment methods -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.payment_methods.*') }}" href="{{ route('admin.payment_methods.index') }}">
            <span class="menu-icon"><i class="bi bi-credit-card fs-2"></i></span>
            <span class="menu-title">طرق الدفع</span>
        </a>
    </div>

    <!-- Wallet points orders -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.wallet_points_orders.*') }}" href="{{ route('admin.wallet_points_orders.index') }}">
            <span class="menu-icon"><i class="bi bi-lightning-charge fs-2"></i></span>
            <span class="menu-title">طلبات الشحن بالنقاط</span>
        </a>
    </div>

    <!-- Wallet topups -->
    <div class="menu-item">
        <a class="menu-link {{ is_active('admin.wallet_topups.*') }}" href="{{ route('admin.wallet_topups.index') }}">
            <span class="menu-icon"><i class="bi bi-wallet2 fs-2"></i></span>
            <span class="menu-title">طلبات إيداع النقاط</span>
        </a>
    </div>

    <!-- Cash Exchange -->
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ is_active('admin.cash_exchange.*') }}">
        <span class="menu-link {{ is_active('admin.cash_exchange.*') }}">
            <span class="menu-icon"><i class="bi bi-cash-coin fs-2"></i></span>
            <span class="menu-title">استبدل رصيدك كاش</span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion menu-active-bg">
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.cash_exchange.offers.*') }}" href="{{ route('admin.cash_exchange.offers.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">فئات الرصيد</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.cash_exchange.requests.*') }}" href="{{ route('admin.cash_exchange.requests.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">طلبات الاستبدال</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Money Exchange -->
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ is_active('admin.money_exchange.*') }}">
        <span class="menu-link {{ is_active('admin.money_exchange.*') }}">
            <span class="menu-icon"><i class="bi bi-currency-exchange fs-2"></i></span>
            <span class="menu-title">تحويل الأموال (SAR↔USDT)</span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion menu-active-bg">
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.money_exchange.settings.*') }}" href="{{ route('admin.money_exchange.settings.edit') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">الإعدادات</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.money_exchange.requests.*') }}" href="{{ route('admin.money_exchange.requests.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">طلبات التحويل</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Diamond codes -->
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ is_active('admin.diamond_codes.*') }}">
        <span class="menu-link {{ is_active('admin.diamond_codes.*') }}">
            <span class="menu-icon"><i class="bi bi-upc-scan fs-2"></i></span>
            <span class="menu-title">أكواد الملابس</span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion menu-active-bg">
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.diamond_codes.index') }}" href="{{ route('admin.diamond_codes.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">مخزون الأكواد</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.diamond_codes.create') }}" href="{{ route('admin.diamond_codes.create') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">إضافة أكواد</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Users -->
    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ is_active('admin.user.*') }}">
        <span class="menu-link {{ is_active('admin.user.*') }}">
            <span class="menu-icon"><i class="bi bi-people fs-2"></i></span>
            <span class="menu-title">المستخدمين</span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion menu-active-bg">
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.user.index') }}" href="{{ route('admin.user.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">قائمة المستخدمين</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.user_messages.*') }}" href="{{ route('admin.user_messages.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">مراسلة المستخدمين</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Admins -->
    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ is_active('admin.admins.*') }}">
        <span class="menu-link {{ is_active('admin.admins.*') }}">
            <span class="menu-icon"><i class="bi bi-shield-lock fs-2"></i></span>
            <span class="menu-title">المديرين</span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion menu-active-bg">
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.admins.index') }}" href="{{ route('admin.admins.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">قائمة المديرين</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.admins.create') }}" href="{{ route('admin.admins.create') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">إضافة مدير</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ is_active('admin.mainSettings.*') || is_active('admin.sliders.*') }}">
        <span class="menu-link {{ is_active('admin.mainSettings.*') || is_active('admin.sliders.*') }}">
            <span class="menu-icon"><i class="bi bi-gear fs-2"></i></span>
            <span class="menu-title">الإعدادات</span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion menu-active-bg">
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.mainSettings.index') }}" href="{{ route('admin.mainSettings.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">الإعدادات العامة</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ is_active('admin.sliders.*') }}" href="{{ route('admin.sliders.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">سلايدر الصفحة الرئيسية</span>
                </a>
            </div>
        </div>
    </div>
</div>

