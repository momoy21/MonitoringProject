<?php

use App\Models\SpesifikasiRAB;

describe('SpesifikasiRAB Model', function () {
    describe('ID Generation', function () {
        it('returns 0001 when table is empty', function () {
            $id = SpesifikasiRAB::generateNextIdSpec();

            expect($id)->toBe('0001');
        });

        it('increments correctly from existing records', function () {
            SpesifikasiRAB::factory()->create(['id_spec' => '0001']);
            SpesifikasiRAB::factory()->create(['id_spec' => '0002']);

            $id = SpesifikasiRAB::generateNextIdSpec();

            expect($id)->toBe('0003');
        });

        it('auto-generates ID when creating without ID', function () {
            $spec = SpesifikasiRAB::factory()->create();

            expect($spec->id_spec)->not->toBeNull();
            expect($spec->id_spec)->toMatch('/^\d{4}$/');
        });
    });

    describe('Scopes', function () {
        beforeEach(function () {
            SpesifikasiRAB::factory()->create(['status' => 'A']);
            SpesifikasiRAB::factory()->create(['status' => 'A']);
            SpesifikasiRAB::factory()->create(['status' => 'N']);
        });

        it('scopeActive returns only active records', function () {
            $result = SpesifikasiRAB::active()->get();

            expect($result)->toHaveCount(2);
            expect($result->every->status === 'A')->toBeTrue();
        });

        it('scopeInactive returns only inactive records', function () {
            $result = SpesifikasiRAB::inactive()->get();

            expect($result)->toHaveCount(1);
            expect($result->every->status === 'N')->toBeTrue();
        });

        it('scopeOrdered returns ordered results', function () {
            SpesifikasiRAB::factory()->create(['id_spec' => '0003', 'norutspec' => 3]);
            SpesifikasiRAB::factory()->create(['id_spec' => '0001', 'norutspec' => 1]);
            SpesifikasiRAB::factory()->create(['id_spec' => '0002', 'norutspec' => 2]);

            $result = SpesifikasiRAB::ordered()->get();

            expect($result->first()->id_spec)->toBe('0001');
        });
    });

    describe('Accessors', function () {
        it('kategori_label returns Pendapatan for PDP', function () {
            $spec = SpesifikasiRAB::factory()->create(['kategori' => 'PDP']);

            expect($spec->kategori_label)->toBe('Pendapatan');
        });

        it('kategori_label returns Harga Pokok Penjualan for HPP', function () {
            $spec = SpesifikasiRAB::factory()->create(['kategori' => 'HPP']);

            expect($spec->kategori_label)->toBe('Harga Pokok Penjualan');
        });

        it('kategori_label returns original for unknown', function () {
            $spec = SpesifikasiRAB::factory()->create(['kategori' => 'X']);

            expect($spec->kategori_label)->toBe('X');
        });

        it('status_label returns Aktif for A', function () {
            $spec = SpesifikasiRAB::factory()->create(['status' => 'A']);

            expect($spec->status_label)->toBe('Aktif');
        });

        it('status_label returns Non Aktif for N', function () {
            $spec = SpesifikasiRAB::factory()->create(['status' => 'N']);

            expect($spec->status_label)->toBe('Non Aktif');
        });
    });

    describe('Default Values', function () {
        it('has A as default status', function () {
            $spec = SpesifikasiRAB::factory()->create();

            expect($spec->status)->toBe('A');
        });
    });
});
