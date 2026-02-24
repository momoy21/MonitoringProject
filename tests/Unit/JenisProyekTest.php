<?php

use App\Models\JenisProyek;

describe('JenisProyek Model', function () {
    describe('Scopes', function () {
        beforeEach(function () {
            JenisProyek::factory()->create(['status' => 'A']);
            JenisProyek::factory()->create(['status' => 'A']);
            JenisProyek::factory()->create(['status' => 'N']);
        });

        it('scopeActive returns only active records', function () {
            $result = JenisProyek::active()->get();

            expect($result)->toHaveCount(2);
            expect($result->every->status === 'A')->toBeTrue();
        });
    });

    describe('Default Values', function () {
        it('has required fillable fields', function () {
            $jenisProyek = JenisProyek::factory()->create([
                'kode_jenis' => 'PJ',
                'nama_jenis' => 'Proyek Jaya',
                'status' => 'A',
            ]);

            expect($jenisProyek->kode_jenis)->toBe('PJ');
            expect($jenisProyek->nama_jenis)->toBe('Proyek Jaya');
            expect($jenisProyek->status)->toBe('A');
        });
    });
});
