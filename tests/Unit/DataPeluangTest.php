<?php

use App\Models\DataPeluang;
use App\Models\Konsumen;

describe('DataPeluang Model', function () {
    describe('ID Generation', function () {
        it('returns 0001 when table is empty', function () {
            $id = DataPeluang::generateIdDataPeluang();

            expect($id)->toBe('0001');
        });

        it('increments correctly from existing records', function () {
            DataPeluang::factory()->create(['id_datapeluang' => '0001']);
            DataPeluang::factory()->create(['id_datapeluang' => '0002']);

            $id = DataPeluang::generateIdDataPeluang();

            expect($id)->toBe('0003');
        });

        it('auto-generates ID when creating without ID', function () {
            $dataPeluang = DataPeluang::factory()->create();

            expect($dataPeluang->id_datapeluang)->not->toBeNull();
            expect($dataPeluang->id_datapeluang)->toMatch('/^\d{4}$/');
        });
    });

    describe('Accessors', function () {
        it('status_label returns New for N', function () {
            $dataPeluang = DataPeluang::factory()->create(['status' => 'N']);

            expect($dataPeluang->status_label)->toBe('New');
        });

        it('status_label returns In Progress for I', function () {
            $dataPeluang = DataPeluang::factory()->create(['status' => 'I']);

            expect($dataPeluang->status_label)->toBe('In Progress');
        });

        it('status_label returns Close for D', function () {
            $dataPeluang = DataPeluang::factory()->create(['status' => 'D']);

            expect($dataPeluang->status_label)->toBe('Close');
        });

        it('status_label returns Cancel for C', function () {
            $dataPeluang = DataPeluang::factory()->create(['status' => 'C']);

            expect($dataPeluang->status_label)->toBe('Cancel');
        });

        it('status_label returns Unknown for unknown status', function () {
            $dataPeluang = DataPeluang::factory()->create(['status' => 'X']);

            expect($dataPeluang->status_label)->toBe('Unknown');
        });

        it('status_badge returns correct badge class for N', function () {
            $dataPeluang = DataPeluang::factory()->create(['status' => 'N']);

            expect($dataPeluang->status_badge)->toContain('badge bg-info');
        });

        it('status_badge returns correct badge class for I', function () {
            $dataPeluang = DataPeluang::factory()->create(['status' => 'I']);

            expect($dataPeluang->status_badge)->toContain('badge bg-primary');
        });

        it('status_badge returns correct badge class for D', function () {
            $dataPeluang = DataPeluang::factory()->create(['status' => 'D']);

            expect($dataPeluang->status_badge)->toContain('badge bg-success');
        });

        it('status_badge returns correct badge class for C', function () {
            $dataPeluang = DataPeluang::factory()->create(['status' => 'C']);

            expect($dataPeluang->status_badge)->toContain('badge bg-danger');
        });
    });

    describe('Default Values', function () {
        it('has N as default status', function () {
            $dataPeluang = DataPeluang::factory()->create();

            expect($dataPeluang->status)->toBe('N');
        });
    });

    describe('Route Key', function () {
        it('uses id_datapeluang as route key', function () {
            $dataPeluang = DataPeluang::factory()->create();

            expect($dataPeluang->getRouteKeyName())->toBe('id_datapeluang');
        });
    });

    describe('Relationships', function () {
        it('has konsumen relationship', function () {
            $dataPeluang = DataPeluang::factory()->create();

            expect($dataPeluang->konsumen())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
        });
    });

    describe('Scopes', function () {
        it('scopeSearch finds by peluang', function () {
            DataPeluang::factory()->create(['peluang' => 'Proyek Pengembangan Sistem']);
            DataPeluang::factory()->create(['peluang' => 'Proyek Lain']);

            $result = DataPeluang::search('Pengembangan')->get();

            expect($result)->toHaveCount(1);
            expect($result->first()->peluang)->toContain('Pengembangan');
        });
    });
});
