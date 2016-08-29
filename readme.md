# Clumsy
A CMS for Laravel

[![Latest Stable Version](https://poser.pugx.org/clumsy/cms/version)](https://packagist.org/packages/clumsy/cms) [![Latest Unstable Version](https://poser.pugx.org/clumsy/cms/v/unstable)](//packagist.org/packages/clumsy/cms) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/49d89e49d2884fa7ba3199c978ea2b65)](https://www.codacy.com/app/tbuteler/clumsy?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=tbuteler/clumsy&amp;utm_campaign=Badge_Grade) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/2a3c7b2f-5980-4ecc-b65b-b57cc747c7ba/mini.png)](https://insight.sensiolabs.com/projects/2a3c7b2f-5980-4ecc-b65b-b57cc747c7ba)

## Installing

- Use Composer to install:
```
composer require clumsy/cms
```

- In the `config/app.php` file, add this to the `providers` key:
```php
Clumsy\CMS\CMSServiceProvider::class,
```

- Optionally, you can set the following aliases:
```php
'Clumsy' => Clumsy\CMS\Facades\Clumsy::class,
'Overseer' => Clumsy\CMS\Facades\Overseer::class,
'Shortcode' => Clumsy\CMS\Facades\Shortcode::class,
```

- Publish Clumsy configuration:
```
php artisan vendor:publish --provider="Clumsy\CMS\CMSServiceProvider" --tag=config
```

- Run migrations to get the admin area's authentication tables and media management tables:
```
php artisan migrate
```

- Finally, publish all Clumsy public assets:
```
php artisan clumsy:publish
```

## Usage

Clumsy will create an admin area where its users can manage resources. Before you start adding resources and routes, it could be a good idea to make sure you have access to it. Ideally, if you have many users, you would create a seeder in which you would register them all at once. If you just want to get up and running for development, you can user the following:

```
php artisan clumsy:user
```

You will be prompted for email, name, password and optionally a user level.

## Legacy

- For Laravel 5.2 support, use version 0.27.*
- For Laravel 5.1 support, use version 0.24.*
- For Laravel 4.1 or 4.2 support, use 0.22.*

### Upgrading from 0.22

- Users, groups and password reset database structure now follows Laravel's default
- Form macros have been removed with the exception of `Form::location` and `Form::delete`
- Parent / Child relations now need to be explicitly declared as methods and return Laravel `Relation` objects in order to work
- Resource view folders are now singular
- External resources:
    - `Route::externalResource` no longer exists; instead, an `external` option should be passed when registering an ordinary resource route, e.g. `Route::resource('resource', 'Controller', ['external' => true]);`
    - `ExternalResource` is no longer a controller to be extended, but a `Trait`, and must be used to achieve the same effect
    - `Importable` is now a model `Trait` and must be used in order to have importing
- Resource names are now supposed to be slug-like and valid URL components -- no more underscores
- `AdminController` property `resource_plural` has been removed
- `AdminControllers` no longer receive arbitrary data in their methods
- Switched most `BaseModel` properties to `Panels`
- All properties are now camel-cased
- Config setting `default-columns` has been removed
- The alert system has changed; it is now expected to call `withAlert` method on the redirect response and pass an associative array where the key is the alert status and the value is the alert message
- There are no more active booleans, but rather `editableInline` inputs (to be defined on index-type `Panels`); also, the `booleans` method in the models is now deprecated in favour of Laravel's attribute casting
- Removed `has_slug` property in the `BaseModel` in favour of a `Trait` which acts as if the property has been set
- "Inner view" terminology was changed to "Panel Type" throughout;
