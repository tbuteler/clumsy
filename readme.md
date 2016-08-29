# Clumsy
A CMS for Laravel

[![Latest Stable Version](https://poser.pugx.org/clumsy/cms/version)](https://packagist.org/packages/clumsy/cms) [![Latest Unstable Version](https://poser.pugx.org/clumsy/cms/v/unstable)](//packagist.org/packages/clumsy/cms) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/49d89e49d2884fa7ba3199c978ea2b65)](https://www.codacy.com/app/tbuteler/clumsy?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=tbuteler/clumsy&amp;utm_campaign=Badge_Grade) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/2a3c7b2f-5980-4ecc-b65b-b57cc747c7ba/mini.png)](https://insight.sensiolabs.com/projects/2a3c7b2f-5980-4ecc-b65b-b57cc747c7ba)

## Installing

Use Composer to install:
```
composer require clumsy/cms
```

In the `config/app.php` file, add this to the `providers` key:
```php
Clumsy\CMS\CMSServiceProvider::class,
```

Optionally, you can set the following aliases:
```php
'Clumsy' => Clumsy\CMS\Facades\Clumsy::class,
'Overseer' => Clumsy\CMS\Facades\Overseer::class,
'Shortcode' => Clumsy\CMS\Facades\Shortcode::class,
```

Publish Clumsy configuration:
```
php artisan vendor:publish --provider="Clumsy\CMS\CMSServiceProvider" --tag=config
```

Run migrations to get the admin area's authentication tables and media management tables:
```
php artisan migrate
```

Finally, publish all Clumsy public assets:
```
php artisan clumsy:publish
```

## Usage

Clumsy will create an admin area where its users can manage resources. Before you start adding resources and routes, it could be a good idea to make sure you have access to it. Ideally, if you have many users, you would create a seeder in which you would register them all at once. If you just want to get up and running for development, you can user the following:

```
php artisan clumsy:user
```

You will be prompted for email, name, password and optionally a user level.

With a user of your own you can proceed to the admin area. By default this will be http://example.com/admin (you can configure this by editing the `authentication-prefix` in the config). After successful authentication, if you haven't yet defined a route which resolves the http://example.com/admin URL, you will see a simple message that reads: `[Admin home] Clumsy is correctly set up.`

Adding routes to that authenticated admin area is easy: whatever route that uses the `clumsy` middleware will automatically be managed by Clumsy.

### Creating new resources

A resource that will be managed inside the admin area is basically an *Eloquent* model with some standard methods and properties. Most of the funcionalities which make up Clumsy are achieved by those model methods plus a standardized controller and a type of class which we call *Panels*. Because the Clumsy admin area can be customized entirely, all those classes inhabit your local app's folders and not the package's. Luckily there's an easy way to just create all those objects at once:

```
php artisan clumsy:resource post
```

The resource name (*post* in this example) defines what the classes will be called. The above command will create the following:

- `App\Post.php` model
- `App\Http\Controllers\PostsController.php` controller
- `Panels\Post\Table.php` panel
- `database\migrations\(...)_create_posts_table.php` migration
- `database\seeds\PostsSeeder.php` seeder
- some boilerplate code inside the `database\factories\ModelFactory.php` class
- `resources\views\admin\post` folder

(The above paths are based on default configuration. All object namespaces can be overridden so that the resource generator puts them in the correct place inside your local application.)

The command output will also give you the resourceful route. By now your `routes\web.php` might look like this:

```php
<?php

Route::group(['prefix' => 'admin', 'middleware' => ['web', 'clumsy']], function () {
    Route::resource('post', 'PostsController');
});
```

Which means that, after running the newly created migration, you can go to http://example.com/admin/post and see the index for the *post* resource and from there create, update and delete entries.

### Customizing views

Clumsy will prioritize your local application's views whenever they're available and fallback to its default templates. Clumsy's templates are small bits of code in *Blade* syntax, intentionally small so you can override them in a very granular manner.

Say you want to override the editing form of your *post* resource. Instead of overriding the entire page, you can just create a `resources\views\admin\post\fields.blade.php` and place your HTML inputs inside it. Clumsy will use them for the create and edit forms. If you want your create forms to be different from your edit forms, you can create a `resources\views\admin\post\create\fields.blade.php` which will be used **only** during creation.

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
