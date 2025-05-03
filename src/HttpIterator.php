<?php

namespace Husail\HttpIterator;

use Exception;

class HttpIterator
{
    private int $perPage;
    private int $currentPage;
    private int $totalResult;
    private bool $initialized;
    private bool $finished;

    private function __construct(int $perPage, int $currentPage)
    {
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->totalResult = 0;
        $this->initialized = false;
        $this->finished = false;
    }

    public static function run(int $perPage, int $currentPage, callable $callableRun, ?callable $callableException = null): void
    {
        $iteratorHttp = new HttpIterator($perPage, $currentPage);
        do {
            try {
                $callableRun($iteratorHttp);
            } catch (Exception $exception) {
                if ($callableException) {
                    $callableException($exception);
                }
            }
        } while ($iteratorHttp->hasNextPage() && !$iteratorHttp->hasFinished());
    }

    public function hasFinished(): bool
    {
        return $this->finished;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage <= $this->totalPages() && !$this->hasFinished();
    }

    public function nextPage(): self
    {
        $this->currentPage += 1;

        return $this;
    }

    public function lastPage(): self
    {
        $this->currentPage = $this->totalPages();

        return $this;
    }

    public function toPage(int $page): self
    {
        $this->currentPage = $page;

        return $this;
    }

    public function setPerPage(int $length): self
    {
        $this->perPage = $length;

        return $this;
    }

    public function setTotalResult(int $length): self
    {
        $this->totalResult = $length;
        $this->initialized = true;

        return $this;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function totalResult(): int
    {
        return $this->totalResult;
    }

    public function totalPages(): int
    {
        return (int) ceil($this->totalResult / $this->perPage);
    }

    public function finish(): self
    {
        $this->finished = true;

        return $this;
    }

    public function hasInitialized(): bool
    {
        return $this->initialized;
    }
}
