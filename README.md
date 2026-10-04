# CheckVin API client

CONTENTS OF THIS FILE
---------------------

 * Updates
 * Description
 * Key packages / extensions
 * Installation
 * Usage
 * Upgrading from 0.x
 
  UPDATES
------------

- **17.03.2023** - published version (<b>v0.1.0</b>) - added an ability to work with AutoCheck, Balance, Carfax.
- **16.08.2023** - published version (<b>v0.2.0</b>) - fixed curl close bug.
- **21.01.2024** - published version (<b>v0.3.0</b>) - added VehicleProvider.
- **2026-10-03** - published version (<b>v1.0.0</b>) - PHP 8.1+, configurable timeouts, client injection, malformed-body safety, dedicated exception, full test suite. See **Upgrading from 0.x** for breaking changes.
 
  DESCRIPTION
------------

CheckVin API client is a package for a convenient working with <a href="https://apicheckvin.xyz">CheckVin</a> API.

  KEY PACKAGES / EXTENSIONS
------------

* php >= 8.1
* ext-curl
* ext-json
* checkvin/vincode ^1.0

 INSTALLATION
------------

Run: composer require jlecter/checkvin-php-api-client

 USAGE
------------

1. Choose provider what do you need. Available now:
- AutocheckDataProvider
- BalanceDataProvider
- CarfaxDataProvider
- VehicleDataProvider

2. Use it by passing inside your API key and calling available methods.

3. Get a response object with such available methods:
- "isSuccess" (returns true/false depends on response)
- "getData" (returns empty array while error response)
- "getError" (returns object while error response, null while success)

**Error object methods (`CheckVin\Api\Http\Data\Error`):**
- `getMessage(): string` — human-readable message (may include flattened `errors` fields)
- `getHttpCode(): int` — the real HTTP status code from the response
- `getErrors(): array` — raw `errors` payload if the API returned an array, otherwise `[]`
- `isMalformedBody(): bool` — `true` when the response body was not a JSON object (e.g. HTML error page, JSON list, empty body)

**VIN validation (client-side, before any HTTP request):**

All VIN-based endpoints validate the supplied VIN before making any HTTP call.
The VIN is trimmed, stripped of dashes and spaces, uppercased and then checked for:
- 17-character length (`LengthNotValidException`)
- Allowed characters — I, O and Q are forbidden (`NotValidLetterException`)
- North-American check digit at position 9 (`CheckSumNotValidException`)

An invalid VIN throws `CheckVin\Api\Exception\InvalidVinCode` (extends `\InvalidArgumentException`, implements `CheckVinApiException`). The original package exception is available via `getPrevious()`.

```php
use CheckVin\Api\Exception\InvalidVinCode;

try {
    $response = $provider->getAutoCheckForVinCode('not-a-vin');
} catch (InvalidVinCode $e) {
    echo $e->getMessage(); // reason from checkvin/vincode
    // $e->getPrevious() is the LengthNotValidException / NotValidLetterException / CheckSumNotValidException
}
```

**Note:** only North-American VINs (with a valid check digit) are accepted. Non-North-American VINs (European, Asian, etc.) will be rejected at this stage.

**Basic usage (default host and timeouts):**

```php
use CheckVin\Api\Provider\Autocheck\AutocheckDataProvider;

$provider = new AutocheckDataProvider('your-api-key');
$response = $provider->checkReportExists('1FM5K7D85HGB31870');

if ($response->isSuccess()) {
    var_dump($response->getData());
} else {
    $error = $response->getError();

    if ($error->isMalformedBody()) {
        // Transport / gateway problem — no API error payload
        echo 'Unexpected response, HTTP ' . $error->getHttpCode();
    } elseif ($error->getHttpCode() === 401) {
        echo 'Invalid API key';
    } elseif ($error->getHttpCode() === 404) {
        echo 'Report not found';
    } elseif ($error->getHttpCode() === 429) {
        echo 'Rate limit exceeded';
    } else {
        // 422 validation error, 5xx, etc.
        echo $error->getMessage();
        // Field-level details if available:
        foreach ($error->getErrors() as $field => $messages) {
            echo $field . ': ' . (is_array($messages) ? implode(', ', $messages) : $messages);
        }
    }
}
```

**Custom host and timeouts:**

```php
use CheckVin\Api\Config\Config;
use CheckVin\Api\Http\Client\Client;
use CheckVin\Api\Provider\Autocheck\AutocheckDataProvider;

$config = new Config(
    host: 'https://apicheckvin.xyz',
    connectTimeoutMs: 1000,   // 1 s connect timeout
    timeoutMs: 5000,          // 5 s total timeout
);
$client = new Client($config);

$provider = new AutocheckDataProvider('your-api-key', $client);
```

**Catching transport failures:**

```php
use CheckVin\Api\Exception\RequestFailed;

try {
    $response = $provider->checkReportExists('1FM5K7D85HGB31870');
} catch (RequestFailed $e) {
    // curl transport error or timeout
    echo $e->getMessage(); // "Request failed (errno N): ..."
}
```

 USING YOUR OWN HTTP CLIENT (PSR-18)
------------

By default the SDK uses its built-in curl client. If you need connection pooling,
retries, a proxy, or any other transport feature, pass any PSR-18-compatible HTTP
client to the provider instead.

**Install the adapter and a PSR-7 factory** (only needed by callers that use
`Psr18Client`; the SDK itself has no runtime dependency on these packages):

```bash
composer require guzzlehttp/guzzle nyholm/psr7
```

**Wire it up:**

```php
use CheckVin\Api\Http\Client\Psr18Client;
use CheckVin\Api\Provider\Balance\BalanceDataProvider;
use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;

// Guzzle 7 implements PSR-18 natively.
// GuzzleHttp\Psr7\HttpFactory also works as the request factory
// (verify the class name against your installed guzzlehttp/psr7 version).
$guzzle = new GuzzleClient([
    'connect_timeout' => 1,   // seconds
    'timeout'         => 10,
]);

$factory = new Psr17Factory(); // nyholm/psr7 — implements RequestFactoryInterface

$client = new Psr18Client($guzzle, $factory);

$provider = new BalanceDataProvider('your-api-key', $client);
$response = $provider->getBalance();
```

**Note:** timeouts, retries, and proxy settings must be configured on the
PSR-18 client directly. `Psr18Client` does not apply any timeouts of its own.

Transport errors thrown by the PSR-18 client (`Psr\Http\Client\ClientExceptionInterface`)
are mapped to `CheckVin\Api\Exception\RequestFailed`, with the original exception
available via `getPrevious()`.

 UPGRADING FROM 0.x
------------

v1.0.0 introduces the following **breaking changes**:

1. **PHP >= 8.1 required.** PHP 7.4 and 8.0 are no longer supported.

2. **`Config` constructor now accepts parameters.**
   Old: `new Config()` — no parameters, uses hard-coded internal defaults.
   New: `new Config(string $host, int $connectTimeoutMs, int $timeoutMs)` — all optional.
   `Config` is now `final`.
   Default timeouts changed: connect timeout is now **10 000 ms** (10 s), total timeout is now **60 000 ms** (60 s). Previously there was no total timeout and the curl default connect timeout applied.
   A trailing slash on `$host` is silently trimmed, so `https://host/` and `https://host` behave identically.
   `connectTimeoutMs <= 0` or `timeoutMs <= 0` throws `CheckVin\Api\Exception\InvalidConfig` (extends `\InvalidArgumentException`).

3. **All four providers accept an optional `ClientInterface` as second constructor argument.**
   Old: `new AutocheckDataProvider('key')`.
   New: `new AutocheckDataProvider('key', ?ClientInterface $client = null)`.
   Existing call sites continue to work unchanged.

4. **`VehicleDataProvider::getInfo()` now requires a `$vinCode` argument.**
   Old (broken — caused a fatal `ArgumentCountError` at runtime): `$provider->getInfo()`.
   New: `$provider->getInfo(string $vinCode): ApiResponse`.
   The matching interface `VehicleDataProviderInterface` is updated accordingly.

5. **Malformed / non-JSON response bodies no longer cause a `TypeError`.**
   Old: a curl response that is not a JSON object (HTML error page, empty body, truncated response) caused a `TypeError` (null passed to array parameter) even on HTTP 200.
   New: those cases return an error `ApiResponse` (`isSuccess() === false`). The error message now includes the HTTP status, e.g. `"Malformed response body (HTTP 200)"`. A 200 response with a non-JSON body is also treated as an error.
   **v1.0.0 also treats a JSON list (e.g. `[1,2,3]`) as a malformed body** — the API contract requires a JSON object. Empty arrays (`[]` / `{}`) remain valid.

6. **`\LogicException` replaced by `CheckVin\Api\Exception\RequestFailed`.**
   Old: curl transport errors threw `\LogicException`.
   New: they throw `CheckVin\Api\Exception\RequestFailed` (extends `\RuntimeException`, implements `CheckVin\Api\Exception\CheckVinApiException`). Update any `catch (\LogicException $e)` blocks.

7. **Most concrete classes are now `final`.**
   The following classes cannot be extended: `Config`, `Client`, `ClientResponse`, `Error`, `ApiResponse`, `ApiUriGlossary`, `AutocheckDataProvider`, `BalanceDataProvider`, `CarfaxDataProvider`, `VehicleDataProvider`.
   If you were extending any of these, compose instead.

8. **`ClientInterface::makeResponse()` has been removed.**
   Old: `ClientInterface` had both `request()` and `makeResponse()`.
   New: `ClientInterface` only has `request()`. Response mapping is handled by `ApiResponse::fromClientResponse(ClientResponse): ApiResponse`.
   Update custom `ClientInterface` implementations and any direct calls to `$client->makeResponse()`.

9. **`AutoCheckDataProviderInterface` renamed to `AutocheckDataProviderInterface`.**
   Old: `CheckVin\Api\Provider\Autocheck\AutoCheckDataProviderInterface`.
   New: `CheckVin\Api\Provider\Autocheck\AutocheckDataProviderInterface`.
   Update any type hints, `implements` clauses, and `use` statements referencing the old name.

10. **Client-side VIN validation is now enforced before any HTTP request.**
   Calling any VIN-based endpoint (`getAutoCheckForVinCode`, `checkReportExists`, `getCarfaxForVinCode`, `getInfo`) with an invalid VIN now throws `CheckVin\Api\Exception\InvalidVinCode` (extends `\InvalidArgumentException`) **before** any network call is made.
   Previously, the raw string was forwarded to the API and validation errors came back as HTTP 4xx responses.
   The underlying package exception (`LengthNotValidException`, `NotValidLetterException`, or `CheckSumNotValidException` from `checkvin/vincode`) is available via `$e->getPrevious()`.
   **Only North-American VINs (valid check digit at position 9) are accepted.**
   A new Composer dependency `checkvin/vincode ^1.0` is required.

11. **Response class hierarchy collapsed into a single `ApiResponse`.**
   Old: `Abstraction\ApiResponse` → `Abstraction\ErrorResponse` / `Abstraction\SuccessResponse` → `Error\ApplicationErrorResponse` / `Success\ApplicationSuccessResponse`, plus `ApiResponseFactory`.
   New: one `final class ApiResponse` at `CheckVin\Api\Http\Response\ApiResponse` with a named constructor `ApiResponse::fromClientResponse(ClientResponse): ApiResponse`.
   - Replace any `use CheckVin\Api\Http\Response\Abstraction\ApiResponse` with `use CheckVin\Api\Http\Response\ApiResponse`.
   - Replace `instanceof ErrorResponse` / `instanceof ApplicationErrorResponse` checks with `!$response->isSuccess()`.
   - Replace `instanceof SuccessResponse` / `instanceof ApplicationSuccessResponse` checks with `$response->isSuccess()`.
   - Replace `ApiResponseFactory::fromClientResponse(...)` with `ApiResponse::fromClientResponse(...)`.
   - `SuccessResponse::SUCCESS_CODE` (= 200) has no public replacement; remove references to it.
