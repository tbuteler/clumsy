<?php namespace Clumsy\CMS\Models\Traits;

use Illuminate\Support\Str;
use Clumsy\CMS\Facades\International;

trait Translatable {

	public static function matchSlug($base_column, $slug)
	{
		$base_column .= '_'.International::getCurrentLocale();
		
		$items = self::all();
		return $items->filter(function($item) use($base_column, $slug)
			{
				return Str::slug($item->$base_column) === $slug;
			})
			->first();
	}

	public function translatable($column)
	{
		$column .= '_'.International::getCurrentLocale();

		return $this->$column;
	}

	public function localizeOrderBy(\Illuminate\Database\Eloquent\Builder $query, $column, $direction = 'asc')
	{
		$column .= '_'.International::getCurrentLocale();

		return $query->orderBy($column, $direction);
	}

    public function scopeOrderLocalized($query, $column, $direction = 'asc')
    {
        return $this->localizeOrderBy($query, $column, $direction);
    }

	public function scopeListsWithId($query, $column)
	{
		$column .= '_'.International::getCurrentLocale();

		return $query->lists($column, 'id');
	}
}