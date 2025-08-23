# Available Methods

Pretend exposes a fluent API for setting up, configuring and completing impersonations.  
Below are the available methods with descriptions and usage examples.

---

### `Pretend::from(Model $impersonator): static`
Creates a new Pretend instance from the given impersonator (the user who wants to impersonate).

```php
$impersonation = Pretend::from($admin);
```

This is the entry point for every impersonation.

### `toBe(Model $impersonated): self`
Specifies the target user to impersonate.
The model must implement ```Laravel\Sanctum\Contracts\HasApiTokens``` trait.
```php
Pretend::from($admin)->toBe($customer);
```

Throws:
```Horlerdipo\Pretend\Exceptions\ModelMissingHasTokenTrait``` if the model does not use ```Laravel\Sanctum\Contracts\HasApiTokens```.

### `for(int $time, Unit $duration = Unit::Minute): self`
Specifies how long the sanctum personal access token that will be generated should last.
By default, duration is measured in minutes.
```php
Pretend::from($admin)
    ->toBe($customer)
    ->for(2, Unit::Hour);  //expires in 2 hours
```

### `Duration Helpers`

For convenience, Pretend provides helper methods to quickly set the duration unit:
```php
Pretend::from($admin)->toBe($customer)->for(30)->minutes();
Pretend::from($admin)->toBe($customer)->for(10)->seconds();
Pretend::from($admin)->toBe($customer)->for(1)->days();
```

Available helpers:
```seconds()```
```minutes()```
```hours()```
```days()```
```months()```
```years()```

### `withAbilities(array $abilities): self`
Restricts the abilities (permissions) that the impersonated token can use.
By default, the impersonation token gets all abilities (`['*']`).

```php
Pretend::from($admin)
    ->toBe($customer)
    ->for(30)
    ->minutes()
    ->withAbilities(['orders.read', 'orders.update']);
```

### ```start(): string```
Starts the impersonation process and returns a temporary impersonation token (a random string).
This token can then be exchanged for a real Sanctum access token.
```php
$token = Pretend::from($admin)
    ->toBe($customer)
    ->for(15)
    ->minutes()
    ->withAbilities(['orders.read', 'orders.update'])
    ->start();
```
Throws:
```Horlerdipo\Pretend\Exceptions\ImpersonatedModelNotSet``` if you call ```start()``` without specifying an impersonated user.

### `Pretend::complete(string $token): NewAccessToken`
Consumes an impersonation token and issues a new Sanctum access token for the impersonated user.
```php
$newAccessToken = Pretend::complete($token);
```

This method handles validation:
* Ensures the token exists and has not been used. 
* Checks if the token has expired. 
* Finds the impersonated user and issues a new access token. 
* Marks the impersonation token as used.

Throws:

```Horlerdipo\Pretend\Exceptions\UnknownImpersonationToken``` if the token does not exist.

```Horlerdipo\Pretend\Exceptions\ImpersonationTokenUsed``` if the token has already been consumed.

```Horlerdipo\Pretend\Exceptions\ImpersonationTokenExpired``` if the token has expired.

```Horlerdipo\Pretend\Exceptions\ImpersonatedModelNotFound``` if the impersonated user no longer exists.

```Horlerdipo\Pretend\Exceptions\ModelMissingHasTokenTrait``` if the impersonated model does not support Sanctum tokens.
