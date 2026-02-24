<?php

use App\Models\BidangJasa;

describe('BidangJasa Model', function () {
    describe('ID Generation', function () {
        it('returns 01 when table is empty', function () {
            $id = BidangJasa::generateNextId();

            expect($id)->toBe('01');
        });

        it('increments correctly from existing records', function () {
            BidangJasa::factory()->create(['id_bidjasa' => '01']);
            BidangJasa::factory()->create(['id_bidjasa' => '02']);

            $id = BidangJasa::generateNextId();

            expect($id)->toBe('03');
        });

        it('auto-generates ID when creating without ID', function () {
            $bidangJasa = BidangJasa::factory()->create();

            expect($bidangJasa->id_bidjasa)->not->toBeNull();
            expect($bidangJasa->id_bidjasa)->toMatch('/^\d{2}$/');
        });
    });

    describe('Scopes', function () {
        beforeEach(function () {
            BidangJasa::factory()->create(['status' => 'A']);
            BidangJasa::factory()->create(['status' => 'A']);
            BidangJasa::factory()->create(['status' => 'N']);
        });

        it('scopeActive returns only active records', function () {
            $result = BidangJasa::active()->get();

            expect($result)->toHaveCount(2);
            expect($result->every->status === 'A')->toBeTrue();
        });

        it('scopeSearch finds by id_bidjasa', function () {
            $bidangJasa = BidangJasa::factory()->create(['id_bidjasa' => '05']);

            $result = BidangJasa::search('05')->get();

            expect($result->first()->id_bidjasa)->toBe('05');
        });

        it('scopeSearch finds by desc_bidjasa', function () {
            BidangJasa::factory()->create(['desc_bidjasa' => 'Jasa Konsultasi IT']);
            BidangJasa::factory()->create(['desc_bidjasa' => 'Jasa Konstruksi']);

            $result = BidangJasa::search('Konsultasi')->get();

            expect($result)->toHaveCount(1);
            expect($result->first()->desc_bidjasa)->toContain('Konsultasi');
        });
    });

    describe('Route Key', function () {
        it('uses id_bidjasa as route key', function () {
            $bidangJasa = BidangJasa::factory()->create();

            expect($bidangJasa->getRouteKeyName())->toBe('id_bidjasa');
        });
    });

    describe('Default Status', function () {
        it('has A as default status', function () {
            $bidangJasa = BidangJasa::factory()->create();

            expect($bidangJasa->status)->toBe('A');
        });
    });
});
