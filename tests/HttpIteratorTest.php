<?php

use Husail\HttpIterator\HttpIterator;

it('creates HttpIterator correctly', function () {
    $iterator = new HttpIterator(20, 3, 3);
    expect($iterator)->toBeInstanceOf(HttpIterator::class)
        ->and($iterator->perPage())->toEqual(20)
        ->and($iterator->currentPage())->toEqual(3)
        ->and($iterator->hasFinished())->toEqual(false)
        ->and($iterator->hasInitialized())->toEqual(false);
});

it('updates perPage and totalResult correctly', function () {
    $iterator = new HttpIterator(30, 2, 3);
    $iterator->setPerPage(20);
    $iterator->setTotalResult(100);
    expect($iterator->perPage())->toEqual(20)
        ->and($iterator->totalResult())->toEqual(100)
        ->and($iterator->totalPages())->toEqual(5);
});

it('navigates pages correctly and resets retry count', function () {
    $iterator = new HttpIterator(15, 2, 3);
    $iterator->setTotalResult(150);

    $iterator->incrementRetry();
    expect($iterator->canRetry())->toBeTrue();

    $iterator->nextPage();
    expect($iterator->currentPage())->toEqual(3)
        ->and($iterator->canRetry())->toBeTrue();

    $iterator->toPage(7)->nextPage();
    expect($iterator->currentPage())->toEqual(8);

    $iterator->lastPage();
    expect($iterator->currentPage())->toEqual(10);
});

it('checks finished and hasNextPage correctly', function () {
    $iterator = new HttpIterator(10, 1, 3);
    $iterator->setTotalResult(50);
    expect($iterator->hasNextPage())->toBeTrue();

    $iterator->nextPage();
    expect($iterator->hasFinished())->toBeFalse();

    $iterator->toPage(4);
    expect($iterator->hasNextPage())->toBeTrue();

    $iterator->finish();
    expect($iterator->hasFinished())->toBeTrue();
});

it('stops retries after maxRetries is exceeded', function () {
    $executed = [];

    HttpIterator::run(10, 1, function ($iterator) use (&$executed) {
        if (!$iterator->hasInitialized()) {
            $iterator->setTotalResult(10);
        }

        $executed[] = $iterator->currentPage();
        $iterator->retry();
    });

    expect($executed)->toHaveCount(3);
});

it('runs callable in run method with retries and skip', function () {
    $executedPages = [];
    $retryCounts = [];

    HttpIterator::run(10, 1, function ($iterator) use (&$executedPages, &$retryCounts) {
        if (!$iterator->hasInitialized()) {
            $iterator->setTotalResult(30);
        }

        $current = $iterator->currentPage();
        $retryCounts[$current] = $retryCounts[$current] ?? 0;
        $executedPages[] = $current;

        if ($current === 2 && $retryCounts[$current] === 0) {
            $retryCounts[$current]++;
            $iterator->retry();
        }

        $iterator->nextPage();
    });

    expect($executedPages)->toEqual([1, 2, 2, 3]);
});
