<?php namespace Clumsy\CMS\Support;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Input;
use Clumsy\CMS\Support\ResourceNameResolver;
use Clumsy\Utils\Facades\HTTP;

class Bakery {

	protected $prefix;

	protected $parents;

	protected $breadcrumb = array();

	public function __construct(UrlGenerator $url, ResourceNameResolver $labeler)
	{
		$this->url = $url;

		$this->labeler = $labeler;
	}

	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

	public function getPrefix()
	{
		return $this->prefix;
	}

	public function breadcrumb($hierarchy, $action, $id = null)
	{
		$this->action = $action;

		extract($hierarchy);
		$this->parents = $parents;

		// Home
		$this->breadcrumb[trans('clumsy::breadcrumb.home')] = $this->url->to($this->prefix);

		switch ($action)
		{
			case 'create' :
				// Fall through
			case 'edit' :

				if (sizeof($parents))
				{
                    $parent_crumbs = array();

                    foreach (array_reverse($parents) as $parent)
                    {
						$parent_id = $id ? $current->parentItemId($id) : Input::get('parent');

                    	$parent_crumbs[$this->labeler->displayNamePlural($current)] = HTTP::queryStringAdd($this->url->route("{$this->prefix}.{$parent->resource_name}".'.edit', $parent_id), 'show', $current->resource_name);
                    	$parent_crumbs[trans('clumsy::titles.edit_item', array('resource' => $this->labeler->displayName($parent)))] = $this->url->route("{$this->prefix}.{$parent->resource_name}".'.edit', $parent_id);
                    	$parent_crumbs[$this->labeler->displayNamePlural($parent)] = $this->url->route("{$this->prefix}.{$parent->resource_name}".'.index');

                    	$current = $parent;
                    	$id = $parent_id;
                    }

					$this->breadcrumb = $this->breadcrumb + array_reverse($parent_crumbs);
				}
				else
				{
			    	$this->breadcrumb[$this->labeler->displayNamePlural($current)] = $this->url->route("{$this->prefix}.{$current->resource_name}.index");
				}

				$this->breadcrumb[trans("clumsy::breadcrumb.{$this->action}")] = '';
				
				break;

			case 'reorder' :
		    	$this->breadcrumb[$this->labeler->displayNamePlural($current)] = $this->url->route("{$this->prefix}.{$current->resource_name}.index");
		    	$this->breadcrumb[trans('clumsy::titles.reorder', array('resources' => $this->labeler->displayNamePlural($current)))] = '';
				break;

			case 'index-of-type' :
				// Fall through
			case 'index' :
		    	$this->breadcrumb[$this->labeler->displayNamePlural($current)] = '';
				break;

			default :
		    	$this->breadcrumb[trans("clumsy::breadcrumb.$action")] = '';
		}

        return $this->breadcrumb;
	}
}