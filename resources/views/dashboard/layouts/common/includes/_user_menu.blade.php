<!--begin::User-->
<div class="d-flex align-items-stretch" id="kt_header_user_menu_toggle">
    <!--begin::Menu wrapper-->
    <div class="px-3 cursor-pointer topbar-item symbol px-lg-5 me-n3 me-lg-n5 symbol-30px symbol-md-35px"
        data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end"
        data-kt-menu-flip="bottom">
        <img src="{{ asset('dashboard/assets/media/avatars/150-2.jpg') }}" alt="{{ get_user_data()?->name }}" />
    </div>
    <!--begin::Menu-->
    <div class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold fs-6 w-275px"
        data-kt-menu="true">
        <div class="px-3 menu-item">
            <div class="px-3 menu-content d-flex align-items-center">
                <div class="symbol symbol-50px me-5">
                    <img alt="{{ get_user_data()?->name }}"
                        src="{{ asset('dashboard/assets/media/avatars/150-26.jpg') }}" />
                </div>
                <div class="d-flex flex-column">
                    <div class="fw-bolder d-flex align-items-center fs-5">{{ get_user_data()?->name }}
                        <span class="px-2 py-1 badge badge-light-success fw-bolder fs-8 ms-2">
                            {{ get_user_data()?->type }}
                        </span>
                    </div>
                    <a href="#" class="fw-bold text-muted text-hover-primary fs-7">{{ get_user_data()?->email }}</a>
                </div>
            </div>
        </div>

        <div class="my-2 separator"></div>

        <div class="px-5 menu-item" data-kt-menu-trigger="hover" data-kt-menu-placement="{{ leftStartDirectionClass() }}">
            <a href="#" class="px-5 menu-link">
                <span class="menu-title position-relative">
                    Languages
                    <span class="px-3 py-2 rounded fs-8 bg-light position-absolute translate-middle-y top-50 end-0">
                        {{ LaravelLocalization::getCurrentLocaleNative() }}
                        @if (App::getLocale() == 'ar')
                            <img class="w-15px h-15px rounded-1 ms-2"
                                src="{{ asset('dashboard/assets/media/flags/egypt.svg') }}" />
                        @elseif(App::getLocale() == 'en')
                            <img class="w-15px h-15px rounded-1 ms-2"
                                src="{{ asset('dashboard/assets/media/flags/united-states.svg') }}" />
                        @endif
                    </span>
                </span>
            </a>
            <div class="py-4 menu-sub menu-sub-dropdown w-175px">
                @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                    <div class="px-3 menu-item">
                        <a class="px-5 menu-link d-flex active" rel="alternate" hreflang="{{ $localeCode }}"
                            href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                            <span class="symbol symbol-20px me-4">
                                @if ($properties['native'] == 'العربية')
                                    <img class="rounded-1" src="{{ asset('dashboard/assets/media/flags/egypt.svg') }}" />
                                @elseif($properties['native'] == 'English')
                                    <img class="rounded-1" src="{{ asset('dashboard/assets/media/flags/united-states.svg') }}" />
                                @endif
                            </span>
                            {{ $properties['native'] }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="px-5 menu-item">
            @if (admin_guard()->check())
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <a href="{{ route('admin.logout') }}"
                        onclick="event.preventDefault(); this.closest('form').submit();" class="px-5 menu-link">
                        تسجيل الخروج
                    </a>
                </form>
            @endif
            @if (manager_guard()->check())
                <form method="POST" action="{{ route('manager.logout') }}">
                    @csrf
                    <a href="{{ route('manager.logout') }}"
                        onclick="event.preventDefault(); this.closest('form').submit();" class="px-5 menu-link">
                        تسجيل الخروج
                    </a>
                </form>
            @endif
        </div>
    </div>
    <!--end::Menu-->
    <!--end::Menu wrapper-->
</div>
<!--end::User -->

