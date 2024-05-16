# HttpIterator
The `HttpIterator` package provides a PHP iterator that simplifies the processing of large volumes of data from Http requests, particularly useful in scenarios with page-based results limitation.

### Installation
```bash
composer require husail/http-iterator
```

### Basic Usage
```php
use Husail\HttpIterator\HttpIterator;
use Illuminate\Support\Facades\Http;

HttpIterator::run(10, 1, function ($iterator) {
    $response = Http::get('http://example.com');
    if (!$iterator->hasInitialized()) {    
        $iterator->setTotalResult($response->json('total_elements'));
    }

    TransactionRepository::sync($response->json('data'))
    //...
       
    $iterator->nextPage();
});
```

### API Reference
- `run(int $perPage, int $currentPage, callable $callableRun, callable $callableException)`: Execute the iterator with the provided callables.
- `nextPage()`: Move to the next page.
- `lastPage()`: Move to the last page.
- `toPage(int $page)`: Move to a specific page.
- `setPerPage(int $length)`: Set the number of results per page.
- `setTotalResult(int $length)`: Set the total number of results.
- `currentPage()`: Get the current page.
- `perPage()`: Get the number of results per page.
- `totalResult()`: Get the total number of results.
- `totalPages()`: Get the total number of pages.
- `finish()`: Mark the iterator as finished.
- `initialized()`: Check if the iterator has been initialized.
- `hasFinished()`: Check if the iterator has finished iterating.
- `hasNextPage()`: Check if there is a next page.

### License
This package is open-source software licensed under the MIT License - see the LICENSE file for details.
