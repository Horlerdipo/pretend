# Getting Started

## Installation
Install Pretend via Composer:

```bash
composer require horlerdipo/pretend
```

Publish the configuration file:
```bash
php artisan vendor:publish --tag=pretend-config
```

Publish the migration(s) and run them:

```bash
php artisan vendor:publish --tag=pretend-migrations
php artisan migrate
```

If your app can’t find the tag, publish by provider instead (replace the class with your package’s provider):

```bash
php artisan vendor:publish --provider="Horlerdipo\Pretend\PretendServiceProvider" --tag=pretend-migrations
```

## Configuration

Pretend comes with a configuration file (config/pretend.php) that lets you adjust and modify how impersonation works.

Available options:

* ```impersonation_token_length```
    
Defines the length of the generated impersonation token.

* ```impersonation_token_ttl```
   
Default lifetime (in minutes) before a generated impersonation token expires.

* ```allow_events_dispatching```

Whether Pretend should dispatch lifecycle events. See the list of available events [here](/digging-deeper?id=available-events).

* ```auth_token_prefix```

Prefix for impersonation access tokens (this is used to identify normal user tokens from impersonated users token, so if you change this, please ensure you destroy any currently valid impersonated tokens).

* ```unauthorized_action_message```
    This is the message returned if an impersonated user is attempting to access a restricted route. See [`PreventImpersonatedRequests`](/digging-deeper?id=prevent-impersonated-users-from-accessing-routes) for more details on this.

* ```impersonation_storage```
    This is the class in charge of storage, retrievals, and updates on Pretend, see the [`Custom Storage`](/digging-deeper?id=swapping-out-storage) section for more details on this

You can customize these values to match your application’s needs.
