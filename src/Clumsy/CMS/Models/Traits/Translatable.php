<?php namespace Clumsy\CMS\Models\Traits;

use Illuminate\Support\Str;
use Clumsy\CMS\Facades\International;

trait Translatable {

	protected static $translatableMutators = array();
	public static $translatable = array();

	protected static function bootTranslatable()
	{
		foreach (static::$translatable as $translatable)
		{
			static::$mutatorCache[get_called_class()][] = $translatable;
			static::$translatableMutators['get'.studly_case($translatable).'Attribute'] = $translatable;
		}
	}

	public static function localizeColumn($column)
	{
		return $column.'_'.International::getCurrentLocale();
	}

	public static function matchSlug($column, $slug, $callback = null)
	{
		$column = self::localizeColumn($column);
		
		$items = self::all();
		return $items->filter(function($item) use($column, $slug, $callback)
			{
				return ($callback ? $callback($item) : true) && Str::slug($item->$column) === $slug;
			})
			->first();
	}

	public function translatable($column)
	{
		$column = $this->localizeColumn($column);

		return $this->$column;
	}

	public function localizeOrderBy(\Illuminate\Database\Eloquent\Builder $query, $column, $direction = 'asc')
	{
		$column = $this->localizeColumn($column);

		return $query->orderBy($column, $direction);
	}

    public function scopeOrderLocalized($query, $column, $direction = 'asc')
    {
        return $this->localizeOrderBy($query, $column, $direction);
    }

	public function scopeListsWithId($query, $column)
	{
		$column = $this->localizeColumn($column);

		return $query->lists($column, 'id');
	}

	public function hasTranslatableGetMutator($key)
	{
		return in_array($key, (array)static::$translatable);
	}

	public function hasGetMutator($key)
	{
		return $this->hasTranslatableGetMutator($key) || method_exists($this, 'get'.studly_case($key).'Attribute');
	}

	public function __call($method, $parameters)
	{
		if (array_key_exists($method, static::$translatableMutators))
		{
			return $this->translatable(static::$translatableMutators[$method]);
		}

		return parent::__call($method, $parameters);
	}
}