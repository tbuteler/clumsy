<?php namespace Clumsy\CMS\Models\Traits;

use Illuminate\Support\Str;
use Clumsy\CMS\Facades\International;

trait Translatable {

	public static function localizeColumn($column)
	{
		return $column.'_'.International::getCurrentLocale();
	}

	public static function matchSlug($column, $slug, $callback = null)
	{
		$column = $this->localizeColumn($column);
		
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
}