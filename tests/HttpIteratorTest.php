<?php

use Husail\HttpIterator\HttpIterator;

it('creates HttpIterator correctly', function () {
    $iterator = HttpIterator::make(10, 1);
    expect($iterator)->toBeInstanceOf(HttpIterator::class)
        ->and($iterator->perPage())->toEqual(10)
        ->and($iterator->currentPage())->toEqual(1)
        ->and($iterator->hasFinished())->toEqual(false)
        ->and($iterator->hasFinished())->toEqual(false)
        ->and($iterator->hasInitialized())->toEqual(false);
});

it('updates perPage and totalResult correctly', function () {
    $iterator = HttpIterator::make(10, 1);
    $iterator->setPerPage(20);
    $iterator->setTotalResult(100);
    expect($iterator->perPage())->toEqual(20)
        ->and($iterator->totalResult())->toEqual(100)
        ->and($iterator->totalPages())->toEqual(5);
});

it('navigates pages correctly', function () {
    $iterator = HttpIterator::make(10, 1);
    $iterator->setTotalResult(50);

    $iterator->nextPage();
    expect($iterator->currentPage())->toEqual(2);

    $iterator->toPage(1);
    expect($iterator->currentPage())->toEqual(1);

    $iterator->lastPage();
    expect($iterator->currentPage())->toEqual(5);
});

it('checks finished and hasNextPage correctly', function () {
    $iterator = HttpIterator::make(10, 1);
    $iterator->setTotalResult(30);
    expect($iterator->hasNextPage())->toBeTrue();

    $iterator->nextPage();
    expect($iterator->hasFinished())->toBeFalse();

    $iterator->finish();
    expect($iterator->hasFinished())->toBeTrue()
        ->and($iterator->hasNextPage())->toBeFalse();
});

it('runs callable in run method', function () {
    HttpIterator::run(10, 1, function ($iterator) {
        // http request
        $responseTotalResult = 50;
        if (!$iterator->hasInitialized()) {
            expect($iterator->totalResult())->toEqual(0);
            $iterator->setTotalResult($responseTotalResult);
            expect($iterator->totalResult())->toEqual($responseTotalResult);
        }

        if ($iterator->currentPage() == 3) {
            expect($iterator->hasNextPage())->toBeTrue();
            $iterator->finish();
            expect($iterator->hasFinished())->toBeTrue();
        }

        $iterator->nextPage();
    });
});