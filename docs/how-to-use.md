# How to Use

Imagine you have three separate applications:

A user-facing frontend (e.g. https://frontend.example.com)

A mobile app used by your end users

An admin backend (e.g. https://admin.example.com) where admins manage the system

As an admin, you may want to impersonate a user and see exactly what they see on the frontend or mobile app, without needing their credentials. This is where Pretend comes in.

Pretend revolves around two main steps:

1. **Generating an impersonation token** with `Pretend::start()` (usually from the admin backend).

2. **Consuming that token** and turning it into a real Sanctum access token with `Pretend::complete()` (usually from the frontend or mobile app).

Once this flow is complete, the admin is fully authenticated as the impersonated user, experiencing the application exactly as the normal user would.

---

## 1. Starting an Impersonation

The impersonation flow starts on the **backend**.  
You create a new `Pretend` instance from the **impersonator** (e.g. an admin user) and specify the **impersonated user**.

```php
$token = Pretend::from($adminUser)   // impersonator (e.g. admin)
    ->toBe($normalUser)             // impersonated user
    ->for(30)                       // valid for 30 minutes
    ->withAbilities(['orders.read', 'orders.update'])
    ->start();
```

```$adminUser ``` is the currently logged in admin (this can be any Model in the database)

```$normalUser```  is the target user you intend to impersonate ( this can also be any Model as long as it implements the ```HasApiTokens``` interface)

```for(30)``` is the sanctum authentication token lifetime (this method can also take a ```Unit``` enum as it's second parameter, while the default is ```Unit::Minute```)

```withAbilities()``` optional list of abilities the impersonated user can perform.

```start()``` returns the impersonation token string 

## 2. Sending the token to the frontend

The impersonation token is not yet an access token.
It must be sent to the frontend or wherever you intend to complete the impersonation.

For example, you might return it in an API response:
```php
return response()->json([
    'impersonation_token' => $token,
]);
```
On the frontend, you can store this token temporarily (e.g. in memory or local storage).

## 3. Completing the Impersonation

Once the frontend has the token, it needs to be exchanged for a real Sanctum access token.
This is done by calling `Pretend::complete($token)` on the backend.

Example endpoint:
```php
Route::post('/impersonation/complete', function (Request $request) {
    $newAccessToken = Pretend::complete($request->input('token'));

    return response()->json([
        'token' => $newAccessToken->plainTextToken,
    ]);
});
```

Here’s what happens inside `Pretend::complete()`:

1. The token is looked up in the configured storage.

2. Validations are performed:

   * Does the token exist?

   * Has it expired?

   * Has it already been used?

   * Does the impersonated user still exist?

3. If valid, a new Sanctum access token is generated for the impersonated user with the configured abilities and expiry.

4. The impersonation token is marked as used to prevent reuse.

The endpoint then returns a valid Sanctum access token, which the frontend can use in subsequent authenticated requests acting exactly as the impersonated user.

