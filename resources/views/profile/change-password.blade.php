<x-layout title="Ubah Password">
    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Ubah Password</h4>
                <p class="mb-0">Perbarui password akun Anda</p>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update-password') }}">
                @csrf
                @method('PUT')

                <!-- Informasi Akun (Disabled) -->
                <div class="form-section mb-4">
                    <h6 class="mb-3 border-bottom pb-2">
                        <i class="bx bx-user me-2"></i>Informasi Akun
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text"
                                       class="form-control"
                                       id="name"
                                       value="{{ auth()->user()->name }}"
                                       disabled>
                                <small class="text-muted">Nama tidak dapat diubah</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       value="{{ auth()->user()->email }}"
                                       disabled>
                                <small class="text-muted">Email tidak dapat diubah</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Baru -->
                <div class="form-section">
                    <h6 class="mb-3 border-bottom pb-2">
                        <i class="bx bx-lock me-2"></i>Password Baru
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="Minimal 8 karakter"
                                       required
                                       autofocus>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Masukkan password yang sama"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Perhatian:</strong> Setelah mengubah password, Anda akan tetap login dengan session saat ini.
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                        <i class="bx bx-x me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check me-1"></i> Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
