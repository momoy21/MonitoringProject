<?php

use App\Models\SpecRabDetail;
use App\Models\SpesifikasiRAB;

describe('SpecRabDetail Model', function () {
    describe('Scopes', function () {
        beforeEach(function () {
            SpecRabDetail::factory()->create(['status' => 'A']);
            SpecRabDetail::factory()->create(['status' => 'A']);
            SpecRabDetail::factory()->create(['status' => 'N']);
        });

        it('scopeActive returns only active records', function () {
            $result = SpecRabDetail::active()->get();

            expect($result)->toHaveCount(2);
            expect($result->every->status === 'A')->toBeTrue();
        });

        it('scopeInactive returns only inactive records', function () {
            $result = SpecRabDetail::inactive()->get();

            expect($result)->toHaveCount(1);
            expect($result->every->status === 'N')->toBeTrue();
        });
    });

    describe('Accessors', function () {
        it('status_label returns Aktif for A', function () {
            $detail = SpecRabDetail::factory()->create(['status' => 'A']);

            expect($detail->status_label)->toBe('Aktif');
        });

        it('status_label returns Non Aktif for N', function () {
            $detail = SpecRabDetail::factory()->create(['status' => 'N']);

            expect($detail->status_label)->toBe('Non Aktif');
        });

        it('kategori returns from parent spesifikasiRab', function () {
            $spec = SpesifikasiRAB::factory()->create(['kategori' => 'PDP']);
            $detail = SpecRabDetail::factory()->create(['id_spec' => $spec->id_spec]);

            expect($detail->kategori)->toBe('PDP');
        });
    });

    describe('Relationships', function () {
        it('has spesifikasiRab relationship', function () {
            $detail = SpecRabDetail::factory()->create();

            expect($detail->spesifikasiRab())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
        });
    });

    describe('Static Methods', function () {
        it('getIdSpecByCostElement returns id_spec for existing cost element', function () {
            $spec = SpesifikasiRAB::factory()->create(['id_spec' => '0001']);
            $detail = SpecRabDetail::factory()->create([
                'id_spec' => $spec->id_spec,
                'cost_element' => 'CE-0001',
            ]);

            $result = SpecRabDetail::getIdSpecByCostElement('CE-0001');

            expect($result)->toBe('0001');
        });

        it('getIdSpecByCostElement returns null for non-existing', function () {
            $result = SpecRabDetail::getIdSpecByCostElement('CE-9999');

            expect($result)->toBeNull();
        });

        it('getKategoriByCostElement returns kategori for existing cost element', function () {
            $spec = SpesifikasiRAB::factory()->create(['kategori' => 'HPP']);
            $detail = SpecRabDetail::factory()->create([
                'id_spec' => $spec->id_spec,
                'cost_element' => 'CE-0002',
            ]);

            $result = SpecRabDetail::getKategoriByCostElement('CE-0002');

            expect($result)->toBe('HPP');
        });

        it('getKategoriByCostElement returns null for non-existing', function () {
            $result = SpecRabDetail::getKategoriByCostElement('CE-9999');

            expect($result)->toBeNull();
        });
    });
});
