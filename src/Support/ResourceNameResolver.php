<?php

namespace Clumsy\CMS\Support;

class ResourceNameResolver
{
    public static function display($label)
    {
        return title_case(str_replace(['_', '-'], ' ', snake_case(str_replace(' ', '_', $label))));
    }

    public static function singular($model)
    {
        if (is_object($model)) {
            $model = $model->displayName() ? $model->displayName() : class_basename($model);
        }

        return $model;
    }

    public static function plural($model)
    {
        if (is_object($model)) {
            $model = $model->displayNamePlural() ? $model->displayNamePlural() : str_plural(static::singular($model));
        } else {
            $model = str_plural($model);
        }

        return $model;
    }

    public static function displayName($model)
    {
        if (is_object($model) && method_exists($model, 'displayNamesCallback')) {
            return $model->displayNamesCallback(static::singular($model));
        }

        return static::display(static::singular($model));
    }

    public static function displayNamePlural($model = false)
    {
        if (is_object($model) && method_exists($model, 'displayNamesCallback')) {
            return $model->displayNamesCallback(static::plural($model));
        }

        return static::display(static::plural($model));
    }
}
