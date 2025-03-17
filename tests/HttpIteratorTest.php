<?php

use Husail\HttpIterator\HttpIterator;

it('creates HttpIterator correctly', function () {
    $iterator = HttpIterator::make(20, 3);
    expect($iterator)->toBeInstanceOf(HttpIterator::class)
        ->and($iterator->perPage())->toEqual(20)
        ->and($iterator->currentPage())->toEqual(3)
        ->and($iterator->hasFinished())->toEqual(false)
        ->and($iterator->hasFinished())->toEqual(false)
        ->and($iterator->hasInitialized())->toEqual(false);
});

it('updates perPage and totalResult correctly', function () {
    $iterator = HttpIterator::make(30, 2);
    $iterator->setPerPage(20);
    $iterator->setTotalResult(100);
    expect($iterator->perPage())->toEqual(20)
        ->and($iterator->totalResult())->toEqual(100)
        ->and($iterator->totalPages())->toEqual(5);
});

it('navigates pages correctly', function () {
    $iterator = HttpIterator::make(15, 2);
    $iterator->setTotalResult(150);

    $iterator->nextPage();
    expect($iterator->currentPage())->toEqual(3);

    $iterator->toPage(7);
    $iterator->nextPage();
    expect($iterator->currentPage())->toEqual(8);

    $iterator->lastPage();
    expect($iterator->currentPage())->toEqual(10);
});

it('checks finished and hasNextPage correctly', function () {
    $iterator = HttpIterator::make(10, 1);
    $iterator->setTotalResult(50);
    expect($iterator->hasNextPage())->toBeTrue();

    $iterator->nextPage();
    expect($iterator->hasFinished())->toBeFalse();

    $iterator->toPage(4);
    expect($iterator->hasNextPage())->toBeTrue();

    $iterator->finish();
    expect($iterator->hasFinished())->toBeTrue()
        ->and($iterator->hasNextPage())->toBeFalse();
});

it('runs callable in run method', function () {
    $executedPages = [];

    HttpIterator::run(10, 1, function ($iterator) use (&$executedPages) {
        // Mock do response
        $responseTotalResult = 50;
        if (!$iterator->hasInitialized()) {
            expect($iterator->totalResult())->toEqual(0);
            $iterator->setTotalResult($responseTotalResult);
            expect($iterator->totalPages())->toEqual(5);
        }

        $executedPages[] = $iterator->currentPage();
        $iterator->nextPage();
    });

    expect($executedPages)->toHaveCount(5)
        ->and($executedPages)->toEqual([1, 2, 3, 4, 5]);
});
