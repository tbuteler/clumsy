<?php

namespace Clumsy\CMS\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Clumsy\CMS\Support\ResourceNameResolver;

class Bakery
{
    protected $prefix;

    protected $parents;

    protected $breadcrumb = [];

    public function __construct(
        Request $request,
        UrlGenerator $url,
        ResourceNameResolver $labeler
    )
    {
        $this->request = $request;

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

    public function breadcrumb($hierarchy, $action)
    {
        $this->action = $action;

        extract($hierarchy);

        $resourceName = $current->resourceName();

        // Home
        $this->breadcrumb[trans('clumsy::breadcrumb.home')] = $this->url->to($this->prefix);

        switch ($action) {

            case 'create':
                // Fall through
            case 'edit':

                if (count($parents)) {

                    $parentCrumbs = [];

                    foreach (array_reverse($parents) as $parent) {

                        $parentResourceName = $parent->resourceName();
                        $parentRoutePrefix = $parentResourceName;

                        $parentCrumbs[$this->labeler->displayNamePlural($current)] = app('clumsy.http')->queryStringAdd($this->url->route("{$parentRoutePrefix}.edit", $parent->id), 'show', $resourceName);
                        $parentCrumbs[trans('clumsy::titles.edit_item', ['resource' => $this->labeler->displayName($parent)])] = $this->url->route("{$parentRoutePrefix}.edit", $parent->id);
                        $parentCrumbs[$this->labeler->displayNamePlural($parent)] = $this->url->route("{$parentRoutePrefix}.index");

                        $current = $parent;
                    }

                    $this->breadcrumb = $this->breadcrumb + array_reverse($parentCrumbs);
                } else {
                    $routePrefix = $resourceName;
                    $this->breadcrumb[$this->labeler->displayNamePlural($current)] = $this->url->route("{$routePrefix}.index");
                }

                $this->breadcrumb[trans("clumsy::breadcrumb.{$this->action}")] = '';

                break;

            case 'reorder':

                $routePrefix = $resourceName;
                $this->breadcrumb[$this->labeler->displayNamePlural($current)] = $this->url->route("{$routePrefix}.index");
                $this->breadcrumb[trans('clumsy::titles.reorder', ['resources' => $this->labeler->displayNamePlural($current)])] = '';
                break;

            case 'index-of-type':
                // Fall through
            case 'index':
                $this->breadcrumb[$this->labeler->displayNamePlural($current)] = '';
                break;

            default:
                $this->breadcrumb[trans("clumsy::breadcrumb.$action")] = '';
        }

        return $this->breadcrumb;
    }

    public function render(array $breadcrumb = [])
    {
        if (empty($breadcrumb)) {
            $breadcrumb = $this->breadcrumb;
        }

        $last = key(array_slice($breadcrumb, -1, 1));
        array_pop($breadcrumb);
        $html = '<ol class="breadcrumb">';
        foreach ($breadcrumb as $crumb => $crumb_link) {
            $html .= '<li><a href="'.$crumb_link.'">'.$crumb.'</a></li>';
        }
        $html .= '<li class="active">'.$last.'</li>';
        $html .= '</ol>';

        return $html;
    }
}
