<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ auth()->user()->hasRole('Super Admin') ? route('konsumen.index') : route('dataproyek.index') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/logo/logo_kit.jpg') }}" alt="Logo" style="width: 180px; height: 100px;" />
            </span>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        @role('Super Admin')
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Master Data</span>
        </li>

        <!-- Konsumen -->
        <li class="menu-item {{ request()->routeIs('konsumen.*') ? 'active' : '' }}">
            <a href="{{ route('konsumen.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div>Konsumen</div>
            </a>
        </li>

        <!-- Bidang Jasa -->
        <li class="menu-item {{ request()->routeIs('bidangjasa.*') ? 'active' : '' }}">
            <a href="{{ route('bidangjasa.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-briefcase"></i>
                <div>Bidang Jasa</div>
            </a>
        </li>

        <!-- Master Manager -->
        <li class="menu-item {{ request()->routeIs('mastermanager.*') ? 'active' : '' }}">
            <a href="{{ route('mastermanager.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-check"></i>
                <div>Master Manager</div>
            </a>
        </li>

        <!-- Master divisi -->
        <li class="menu-item {{ request()->routeIs('masterdivisi.*') ? 'active' : '' }}">
            <a href="{{ route('masterdivisi.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-check"></i>
                <div>Master divisi</div>
            </a>
        </li>

        <!-- jenis proyek -->
        <li class="menu-item {{ request()->routeIs('jenisproyek.*') ? 'active' : '' }}">
            <a href="{{ route('jenisproyek.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-task"></i>
                <div>Jenis Proyek</div>
            </a>
        </li>

        <!-- Kondisi Proyek -->
        <li class="menu-item {{ request()->routeIs('kondisiproyek.*') ? 'active' : '' }}">
            <a href="{{ route('kondisiproyek.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-task"></i>
                <div>Kondisi Proyek</div>
            </a>
        </li>

        <!-- Spesifikasi RAB -->
        <li class="menu-item {{ request()->routeIs('spesifikasirab.*') ? 'active' : '' }}">
            <a href="{{ route('spesifikasirab.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-list-check"></i>
                <div>Spesifikasi RAB</div>
            </a>
        </li>

        <!-- Summary RAB -->
        <li class="menu-item {{ request()->routeIs('summaryrab.*') ? 'active' : '' }}">
            <a href="{{ route('summaryrab.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
                <div>Summary RAB</div>
            </a>
        </li>

        <!--Detail RAB -->
        <li class="menu-item {{ request()->routeIs('specrabdetail.*') ? 'active' : '' }}">
            <a href="{{ route('specrabdetail.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div>RAB Detail</div>
            </a>
        </li>

        <!-- Master Karyawan -->
        <li class="menu-item {{ request()->routeIs('karyawan.*') ? 'active' : '' }}">
            <a href="{{ route('karyawan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-id-card"></i>
                <div>Master Karyawan</div>
            </a>
        </li>
        
        @endrole

        @role('Project Manager')
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Master Data</span>
        </li>

        <!-- Konsumen - Read Only for PM -->
        <li class="menu-item {{ request()->routeIs('konsumen.*') ? 'active' : '' }}">
            <a href="{{ route('konsumen.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div>Konsumen</div>
            </a>
        </li>
        @endrole

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Transaksi/Operasional</span>
        </li>

        <!-- Data Peluang -->
        <li class="menu-item {{ request()->routeIs('datapeluang.*') ? 'active' : '' }}">
            <a href="{{ route('datapeluang.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-radar"></i>
                <div>Data Peluang</div>
            </a>
        </li>

        <!-- Data Proyek -->
        <li class="menu-item {{ request()->routeIs('dataproyek.*') ? 'active' : '' }}">
            <a href="{{ route('dataproyek.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-folder-open"></i>
                <div>Data Proyek</div>
            </a>
        </li>

         <!-- Upload RAB -->
        <li class="menu-item {{ request()->routeIs('rab.*') ? 'active' : '' }}">
            <a href="{{ route('rab.upload') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-upload"></i>
                <div>Upload RAB</div>
            </a>
        </li>

         <!-- Pengajuan RAB -->
        <li class="menu-item {{ request()->routeIs('pengajuanrab.*') ? 'active' : '' }}">
            <a href="{{ route('pengajuanrab.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file-find"></i>
                <div>Pengajuan RAB</div>
            </a>
        </li>

        <!-- Pencatatan Pleno RAB -->
        <li class="menu-item {{ request()->routeIs('pencatatanpleno.*') ? 'active' : '' }}">
            <a href="{{ route('pencatatanpleno.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-edit"></i>
                <div>Pencatatan Pleno RAB</div>
            </a>
        </li>

        <!-- Progress Project -->
        <li class="menu-item {{ request()->routeIs('progressproyek.*') ? 'active' : '' }}">
            <a href="{{ route('progressproyek.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                <div>Progress Proyek</div>
            </a>
        </li>

        <!-- Pendapatan Proyek -->
        <li class="menu-item {{ request()->routeIs('pendapatan.*') ? 'active' : '' }}">
            <a href="{{ route('pendapatan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div>Pendapatan Proyek</div>
            </a>
        </li>

        
    <li class="menu-header small text-uppercase">
            <span class="menu-header-text">laporan proyek</span>
        </li>

         <!-- laporan progres proyek -->
         <li class="menu-item {{ request()->is('laporan-progress-proyek*') ? 'active' : '' }}">
            <a href="{{ url('laporan-progress-proyek') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                <div>Laporan Progress Proyek</div>
            </a>
        </li>
        
        <!-- Laporan Hasil Pleno RAB -->
        <li class="menu-item {{ request()->routeIs('laporanhasilplenorab.*') ? 'active' : '' }}">
            <a href="{{ route('laporanhasilplenorab.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file-find"></i>
                <div>Laporan Hasil Pleno RAB</div>
            </a>
        </li>
    </ul>
</aside>
