<!--begin::Header-->
<div id="kt_header" style="" class="header align-items-stretch">
    <!--begin::Container-->
    <div class="container-fluid d-flex align-items-stretch justify-content-between">
        <!--begin::Aside mobile toggle-->
        <div class="d-flex align-items-center d-lg-none ms-n3 me-1" title="Show aside menu">
            <div class="btn btn-icon btn-active-color-white" id="kt_aside_mobile_toggle">
                <i class="bi bi-list fs-1"></i>
            </div>
        </div>
        <!--end::Aside mobile toggle-->
        <!--begin::Mobile logo-->
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="{{ route('admin.dashboard') }}" class="d-lg-none">
                <img alt="Logo"
                    src="{{ $logo ?? asset('dashboard/assets/media/logos/logo-demo13-compact.svg') }}"
                    class="h-25px" />
            </a>
        </div>
        <!--end::Mobile logo-->
        <!--begin::Wrapper-->
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
            <!--begin::Navbar-->
            <div class="d-flex align-items-stretch" id="kt_header_nav">
                <!--begin::Menu wrapper-->
                <div class="header-menu align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="header-menu"
                    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
                    data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="end"
                    data-kt-drawer-toggle="#kt_header_menu_mobile_toggle" data-kt-swapper="true"
                    data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
                    <!--begin::Menu-->
                    <div class="my-5 menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-lg-0 align-items-stretch"
                        id="#kt_header_menu" data-kt-menu="true">
                        <!-- Start Dashboard Link -->
                        <div class="menu-item me-lg-1">
                            @if(auth('admin')->check())
                                <a class="py-3 menu-link active" href="{{ route('admin.dashboard') }}">
                                    <span class="menu-title">الرئيسيه - لوحه التحكم</span>
                                </a>
                            @else
                                <a class="py-3 menu-link active" href="{{ route('manager.dashboard') }}">
                                    <span class="menu-title">الرئيسيه - لوحه التحكم</span>
                                </a>
                            @endif
                        </div>
                        <!-- End Dashboard Link -->
                    </div>
                    <!--end::Menu-->
                </div>
                <!--end::Menu wrapper-->
            </div>
            <!--end::Navbar-->
            <!--begin::Topbar-->
            <div class="flex-shrink-0 d-flex align-items-stretch">
                <!--begin::Toolbar wrapper-->
                <div class="flex-shrink-0 topbar d-flex align-items-stretch">
                    <!--begin::Notifications-->
                    <div class="d-flex align-items-stretch">
                        <!-- default is bottom-end in english but in arabic will be as bottom-start -->
                        <div class="px-3 topbar-item position-relative px-lg-5" data-kt-menu-trigger="click"
                            data-kt-menu-attach="parent" data-kt-menu-placement="{{ bottomEndDirectionClass() }}"
                            data-kt-menu-flip="bottom">
                            <i class="bi bi-app-indicator fs-3"></i>
                        </div>
                        <!--begin::Menu-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column notification_menu w-350px w-lg-375px" data-kt-menu="true">
                            <div class="d-flex flex-column bgi-no-repeat rounded-top"
                                style="background-image:url('{{ asset('dashboard/assets/media/misc/pattern-1.jpg') }}')">
                                <h3 class="mt-10 mb-6 text-white fw-bold px-9">الاشعارات</h3>
                                <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-bold px-9">
                                    <li class="nav-item">
                                        <a class="pb-4 text-white opacity-75 nav-link opacity-state-100"
                                            data-bs-toggle="tab" href="#kt_topbar_notifications_1">الطلبات</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content">
                                <div class="tab-pane active" id="kt_topbar_notifications_1" role="tabpanel">
                                    <div class="px-8 my-5 scroll-y mh-325px" id="notifications-list"></div>
                                </div>
                            </div>
                        </div>
                        <!--end::Menu-->
                    </div>
                    <!--end::Notifications-->

                    <!--begin::User-->
                    @include('dashboard.layouts.common.includes._user_menu')
                    <!--end::User -->

                    <!--begin::Heaeder menu toggle-->
                    <div class="px-3 d-flex align-items-stretch d-lg-none me-n3" title="Show header menu">
                        <div class="topbar-item" id="kt_header_menu_mobile_toggle">
                            <i class="bi bi-text-left fs-1"></i>
                        </div>
                    </div>
                    <!--end::Heaeder menu toggle-->
                </div>
                <!--end::Toolbar wrapper-->
            </div>
            <!--end::Topbar-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Container-->
</div>
<!--end::Header-->

