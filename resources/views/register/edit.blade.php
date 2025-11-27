<x-layout title="Edit Project Manager">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Kelola PM', 'url' => route('register.index')],
            ['name' => 'Edit PM']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Project Manager</h4>
                <p class="mb-0">Update informasi Project Manager: {{ $user->name }}</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('register.index') }}" class="btn btn-outline-secondary" onclick="if(window.StateManagers?.kelolaPM) window.StateManagers.kelolaPM.markForRestore();">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="registerForm" method="POST" action="{{ route('register.update', $user->id) }}">
                @csrf
                @method('PUT')

                <!-- Informasi Akun -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Akun</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       placeholder="Masukkan nama lengkap"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="user@example.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="Kosongkan jika tidak ingin mengubah password">
                                <small class="text-muted">Minimal 8 karakter. Kosongkan jika tidak ingin mengubah.</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Masukkan password yang sama">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Bidang Jasa -->
                <div class="form-section">
                    <h6 class="mb-3">Filter Bidang Jasa</h6>
                    <p class="text-muted mb-3">
                        <i class="bx bx-info-circle"></i>
                        Pilih bidang jasa yang dapat diakses oleh Project Manager ini.
                        Jika tidak ada yang dipilih, PM dapat mengakses semua bidang jasa.
                    </p>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="selectAll">
                                    <i class="bx bx-check-square"></i> Pilih Semua
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary mb-2" id="deselectAll">
                                    <i class="bx bx-square"></i> Hapus Semua
                                </button>
                            </div>

                            <div class="card border">
                                <div class="card-body">
                                    @forelse($bidangJasas as $bidangJasa)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input bidang-jasa-check"
                                                   type="checkbox"
                                                   name="bidang_jasa_ids[]"
                                                   value="{{ $bidangJasa->id_bidjasa }}"
                                                   id="bidjasa_{{ $bidangJasa->id_bidjasa }}"
                                                   {{
                                                       (is_array(old('bidang_jasa_ids')) && in_array($bidangJasa->id_bidjasa, old('bidang_jasa_ids'))) ||
                                                       (!old('bidang_jasa_ids') && in_array($bidangJasa->id_bidjasa, $selectedBidangJasas))
                                                       ? 'checked' : ''
                                                   }}>
                                            <label class="form-check-label" for="bidjasa_{{ $bidangJasa->id_bidjasa }}">
                                                {{ $bidangJasa->desc_bidjasa }}
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">Tidak ada bidang jasa tersedia</p>
                                    @endforelse
                                </div>
                            </div>

                            @error('bidang_jasa_ids')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('register.index') }}" class="btn btn-outline-secondary" onclick="if(window.StateManagers?.kelolaPM) window.StateManagers.kelolaPM.markForRestore();">
                        <i class="bx bx-x me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner"></span>
                        <i class="bx bx-check me-1" id="submitIcon"></i>
                        <span id="submitText">Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Select/Deselect All Checkboxes
        document.getElementById('selectAll').addEventListener('click', function() {
            document.querySelectorAll('.bidang-jasa-check').forEach(checkbox => {
                checkbox.checked = true;
            });
        });

        document.getElementById('deselectAll').addEventListener('click', function() {
            document.querySelectorAll('.bidang-jasa-check').forEach(checkbox => {
                checkbox.checked = false;
            });
        });

        // Form Submit Handler
        document.getElementById('registerForm').addEventListener('submit', function() {
            // Mark for state restoration (this is edit page, so always restore)
            if (window.StateManagers?.kelolaPM) {
                window.StateManagers.kelolaPM.markForRestore();
            }

            const submitBtn = document.getElementById('submitBtn');
            const submitSpinner = document.getElementById('submitSpinner');
            const submitIcon = document.getElementById('submitIcon');
            const submitText = document.getElementById('submitText');

            submitBtn.disabled = true;
            submitSpinner.classList.remove('d-none');
            submitIcon.classList.add('d-none');
            submitText.textContent = 'Mengupdate...';
        });
    </script>
    @endpush
</x-layout>
