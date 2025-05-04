# HttpIterator
The `HttpIterator` package provides a PHP iterator that simplifies the processing of large volumes of paginated data from HTTP requests. It's particularly useful for APIs with page-based results, supporting retries in case of transient failures.

---

## 📦 Installation
```bash
composer require husail/http-iterator
```

---

## 🔧 Usage Example

### Basic Usage
```php
use Husail\HttpIterator\HttpIterator;
use Illuminate\Support\Facades\Http;

$perPage = 10;
$startPage = 1;
HttpIterator::run($perPage, $startPage, function ($iterator) {
     $response = Http::get('https://example.com/api/transactions', [
        'page' => $iterator->currentPage(),
        'per_page' => $iterator->perPage(),
    ]);

    if (!$iterator->hasInitialized()) {    
        $iterator->setTotalResult($response->json('total_elements'));
    }

    TransactionRepository::sync($response->json('data'))
    //...
       
    $iterator->nextPage();
});
```

### Handling Retries
You can use the `retry()` method to explicitly repeat the current iteration without advancing to the next page. This is useful for handling transient errors such as API throttling (HTTP 429) or temporary network issues.
Retries are limited by the `maxRetries` parameter (default: `3`).  
After reaching this limit, the iterator will automatically stop, unless the retry counter is reset by a successful interaction (e.g., calling `nextPage()`, `toPage()`, or `lastPage()`), or by a manual call to `resetRetries()`.

```php
use Husail\HttpIterator\HttpIterator;
use Illuminate\Support\Facades\Http;

const THROTTLE_DELAY_SECONDS = 60;

$perPage = 10;
$startPage = 1;
HttpIterator::run($perPage, $startPage, function ($iterator) {
     $response = Http::get('https://example.com/api/transactions', [
        'page' => $iterator->currentPage(),
        'per_page' => $iterator->perPage(),
    ]);
  
    if ($response->status() === 429) {
        sleep(THROTTLE_DELAY_SECONDS);
        $iterator->retry();
    }

    if (!$iterator->hasInitialized()) {    
        $iterator->setTotalResult($response->json('total_elements'));
    }

    TransactionRepository::sync($response->json('data'))
    //...
       
    $iterator->nextPage();
});
```

---

## 🧰 API Reference
- `HttpIterator::run(int $perPage, int $currentPage, callable $callableRun, ?callable $callableException = null, int $maxRetries = 3)`: Execute the iterator with the provided callables.
- `nextPage()`: Move to the next page and reset retry count.
- `lastPage()`: Jump to the last page.
- `toPage(int $page)`: Jump to a specific page.
- `setPerPage(int $length)`: Set the page size.
- `setTotalResult(int $length)`: Set the total number of results.
- `currentPage()`: Get the current page number.
- `perPage()`: Get the per-page count.
- `totalResult()`: Get the total number of results.
- `totalPages()`: Get the total number of pages.
- `finish()`: Mark the iteration as finished.
- `hasInitialized()`: Returns whether total result count has been set.
- `hasFinished()`: Returns whether the iteration was manually finished.
- `hasNextPage()`: Returns whether more pages are available.
- `retry()`: Retry the current iteration (increments retry count and throws).

---

## 🤝 Contributing
We welcome contributions! Feel free to submit issues or pull requests to help improve the package.

---

## 📜 License
This package is open-source software licensed under the MIT License. See the [LICENSE](LICENSE.md) file for details.
