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

    private int $retryCount;
    private int $maxRetries;

    private function __construct(int $perPage, int $currentPage, $maxRetries)
    {
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->totalResult = 0;
        $this->initialized = false;
        $this->finished = false;
        $this->retryCount = 0;
        $this->maxRetries = $maxRetries;
    }

    public static function run(int $perPage, int $currentPage, callable $callableRun, ?callable $callableException = null, int $maxRetries = 3): void
    {
        $iteratorHttp = new HttpIterator($perPage, $currentPage, $maxRetries);
        do {
            try {
                $callableRun($iteratorHttp);
            } catch (SkipIterationException) {
            } catch (Exception $exception) {
                $iteratorHttp->incrementRetry();
                if ($callableException) {
                    $callableException($exception);
                }
            }
        } while ($iteratorHttp->canRetry() || ($iteratorHttp->hasNextPage() && !$iteratorHttp->hasFinished()));
    }

    public function hasInitialized(): bool
    {
        return $this->initialized;
    }

    public function hasFinished(): bool
    {
        return $this->finished;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage() <= $this->totalPages();
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
        return (int) ceil($this->totalResult() / $this->perPage());
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

    public function nextPage(): self
    {
        $this->currentPage += 1;
        $this->resetRetries();

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

    public function finish(): self
    {
        $this->finished = true;

        return $this;
    }

    /**
     * @return never
     * @throws SkipIterationException
     */
    public function retry(): never
    {
        $this->incrementRetry();
        throw new SkipIterationException();
    }

    public function incrementRetry(): self
    {
        $this->retryCount++;

        return $this;
    }

    public function resetRetries(): self
    {
        $this->retryCount = 0;
        return $this;
    }

    public function canRetry(): bool
    {
        return $this->retryCount < $this->maxRetries;
    }
}
