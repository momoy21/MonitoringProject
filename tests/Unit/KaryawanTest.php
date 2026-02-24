<?php

use App\Models\Karyawan;

describe('Karyawan Model', function () {
    describe('Constants', function () {
        it('has STATUS_OPTIONS constant', function () {
            expect(Karyawan::STATUS_OPTIONS)->toBe([
                'T' => 'Karyawan Tetap',
                'K' => 'Karyawan Kontrak',
                'J' => 'Karyawan JO',
            ]);
        });

        it('has AKTIF_OPTIONS constant', function () {
            expect(Karyawan::AKTIF_OPTIONS)->toBe([
                'Y' => 'Ya',
                'T' => 'Tidak',
            ]);
        });
    });

    describe('Scopes', function () {
        beforeEach(function () {
            Karyawan::factory()->create(['aktif' => 'Y']);
            Karyawan::factory()->create(['aktif' => 'Y']);
            Karyawan::factory()->create(['aktif' => 'T']);
        });

        it('scopeActive returns only active records', function () {
            $result = Karyawan::active()->get();

            expect($result)->toHaveCount(2);
            expect($result->every->aktif === 'Y')->toBeTrue();
        });

        it('scopeSearch finds by nik', function () {
            $karyawan = Karyawan::factory()->create(['nik' => '001']);

            $result = Karyawan::search('001')->get();

            expect($result->first()->nik)->toBe('001');
        });

        it('scopeSearch finds by nama', function () {
            Karyawan::factory()->create(['nama' => 'John Doe']);
            Karyawan::factory()->create(['nama' => 'Jane Smith']);

            $result = Karyawan::search('John')->get();

            expect($result)->toHaveCount(1);
            expect($result->first()->nama)->toContain('John');
        });
    });

    describe('Accessors', function () {
        it('status_label returns correct label for tetap', function () {
            $karyawan = Karyawan::factory()->create(['status' => 'T']);

            expect($karyawan->status_label)->toBe('Karyawan Tetap');
        });

        it('status_label returns correct label for kontrak', function () {
            $karyawan = Karyawan::factory()->create(['status' => 'K']);

            expect($karyawan->status_label)->toBe('Karyawan Kontrak');
        });

        it('status_label returns correct label for JO', function () {
            $karyawan = Karyawan::factory()->create(['status' => 'J']);

            expect($karyawan->status_label)->toBe('Karyawan JO');
        });

        it('status_label returns original value for unknown status', function () {
            $karyawan = Karyawan::factory()->create(['status' => 'X']);

            expect($karyawan->status_label)->toBe('X');
        });

        it('aktif_label returns Ya for Y', function () {
            $karyawan = Karyawan::factory()->create(['aktif' => 'Y']);

            expect($karyawan->aktif_label)->toBe('Ya');
        });

        it('aktif_label returns Tidak for T', function () {
            $karyawan = Karyawan::factory()->create(['aktif' => 'T']);

            expect($karyawan->aktif_label)->toBe('Tidak');
        });

        it('aktif_label returns original value for unknown', function () {
            $karyawan = Karyawan::factory()->create(['aktif' => 'X']);

            expect($karyawan->aktif_label)->toBe('X');
        });
    });

    describe('Methods', function () {
        it('isActive returns true when aktif is Y', function () {
            $karyawan = Karyawan::factory()->create(['aktif' => 'Y']);

            expect($karyawan->isActive())->toBeTrue();
        });

        it('isActive returns false when aktif is T', function () {
            $karyawan = Karyawan::factory()->create(['aktif' => 'T']);

            expect($karyawan->isActive())->toBeFalse();
        });
    });

    describe('Route Key', function () {
        it('uses nik as route key', function () {
            $karyawan = Karyawan::factory()->create();

            expect($karyawan->getRouteKeyName())->toBe('nik');
        });
    });
});
