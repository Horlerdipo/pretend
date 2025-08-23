# Digging Deeper

Pretend provides more advanced features to help you secure, observe, and customize impersonation in your application.  
This section explains middleware, storage customization, and the events available for listening.

---

## Prevent Impersonated Users from Accessing Routes

Pretend ships with the **`PreventImpersonatedRequests`** middleware.  
This middleware blocks impersonated users from accessing specific routes. This is useful for sensitive actions like billing, security settings, or account deletion.

Example usage:

```php
Route::middleware(['auth:sanctum', \Horlerdipo\Pretend\Http\Middleware\PreventImpersonatedUserRequests::class])
    ->post('/sensitive-action', [SensitiveActionController::class, 'store']);
```

## Capture Requests made by Impersonated Users

Pretend also provides the **`CaptureImpersonatedRequests`** middleware.
This middleware records every request made while impersonating another user and emits an event: `ImpersonatedRequestProcessedEvent`.
You can add this as a global middleware so you can get a full audit of actions performed while impersonating another user.

```php
$middleware->append([
    \Horlerdipo\Pretend\Http\Middleware\CaptureImpersonatedRequests::class
])
```

## Swapping Out Storage

Pretend stores impersonation tokens via a storage contract: **`HasImpersonationStorage`**.
By default, a DatabaseStorage implementation is provided.

You can swap this out for any custom implementation (e.g., Redis, cache, or even an external service):
```php
$this->app->singleton(\Horlerdipo\Pretend\Contracts\HasImpersonationStorage::class, MyCustomRedisImpersonationStorage::class)
```
As long as your class implements `HasImpersonationStorage`, Pretend will use it seamlessly.

## Available Events

Pretend emits several events during the impersonation lifecycle.
You can listen to them for logging, auditing, or triggering other side effects. This event emission can be disabled on the configuration file (`config/pretend.php`)

* `\Horlerdipo\Pretend\Events\ImpersonationStartedEvent`

    This is fired when an impersonation session is initiated (`start()`).

* `\Horlerdipo\Pretend\Events\ImpersonationCompletedEvent`

    This is fired when an impersonation token is successfully completed into a Sanctum access token (`complete()`).

* `\Horlerdipo\Pretend\Events\ImpersonatedRequestProcessedEvent`

    This is fired when a request made by an impersonated user is captured by the `\Horlerdipo\Pretend\Http\Middleware\CaptureImpersonatedRequests` middleware.
