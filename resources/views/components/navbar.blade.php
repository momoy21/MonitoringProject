<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="d-flex align-items-center">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
            </a>
        </div>

        <span class="app-brand-text demo menu-text fw-bolder ms-2 company-name">
            <span class="d-none d-md-inline" style="text-transform:capitalize; font-size:20px;">Monitoring Project System</span>
            <span class="d-inline d-md-none" style="text-transform:capitalize; font-size:20px">PT KIT</span>
        </span>
    </div>

    <div class="navbar-nav-right d-flex align-items-center ms-auto" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <i class="bx bx-user-circle" style="font-size: 40px; color: #00a0d4;"></i>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <i class="bx bx-user-circle" style="font-size: 40px; color: #00a0d4;"></i>
                                    </div>
                                </div>
                                <div class="grow">
                                    <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                                    <small class="text-muted">{{ auth()->user()->getRoleNames()->first() }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    {{-- <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">Profile</span>
                        </a>
                    </li> --}}
                    @if(auth()->user()->hasRole('Super Admin'))
                    <li>
                        <a class="dropdown-item" href="{{ route('register.index') }}">
                            <i class="bx bx-user-plus me-2"></i>
                            <span class="align-middle">Kelola Project Manager</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('sap.index') }}">
                            <i class="bx bx-data me-2"></i>
                            <span class="align-middle">Kelola SAP Import</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('lembur.index') }}">
                            <i class="bx bx-data me-2"></i>
                            <span class="align-middle">Kelola Lembur</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasRole('Project Manager'))
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.change-password') }}">
                            <i class="bx bx-lock me-2"></i>
                            <span class="align-middle">Ubah Password</span>
                        </a>
                    </li>
                    @endif
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bx bx-power-off me-2"></i>
                                <span class="align-middle">Log Out</span>
                            </a>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
