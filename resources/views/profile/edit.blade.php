<x-layout title="Profile">
    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Profile</h4>
                <p class="mb-0">Kelola informasi profil dan keamanan akun Anda</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <!-- Super Admin Menu -->
            @if(auth()->user()->hasRole('Super Admin'))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Menu Super Admin</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('register.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bx bx-user-plus me-2"></i>
                                Kelola Project Manager
                            </a>
                            <p class="text-muted mt-2 mb-0">
                                <small>Daftarkan dan kelola akun Project Manager dengan filter bidang jasa</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Update Profile Information -->
            <div class="card mb-4">
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Update Password -->
            <div class="card mb-4">
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account -->
            <div class="card">
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>
    <script>
        @if(session('status') == 'profile-updated')
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil berhasil diperbarui!',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
    </script>
    @endpush
</x-layout>
