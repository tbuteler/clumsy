## Legacy support
For Laravel 4.1 or 4.2 support, use 0.22.* versions or below.

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