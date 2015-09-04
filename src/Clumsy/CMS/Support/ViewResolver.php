<?php
namespace Clumsy\CMS\Support;

use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

class ViewResolver
{
    protected $view;

    protected $domain;

    protected $levels = array();

    protected $nested = array();

    public function __construct(Application $app)
    {
        $this->view = $app->make('clumsy.view-resolver-factory');
    }

    protected function prefix()
    {
        return 'admin';
    }

    protected function domainPath($domain = false)
    {
        $prefix = $this->prefix();

        $domain = $domain ? str_plural($domain) : $this->domain;

        return !$this->domain ? $prefix : "$prefix.$domain";
    }

    protected function prepareLevel($level)
    {
        $level = snake_case($level);

        if (sizeof($this->nested)) {
            $clone = $level;
            foreach ($this->nested as $nested) {
                $clone .= ".$nested";
                $levels[] = $clone;
            }

            $levels = array_reverse($levels);
        }

        $levels[] = $level;

        return $levels;
    }

    public function setDomain($domain)
    {
        $this->domain = str_plural($domain);

        return $this;
    }

    public function pushLevel()
    {
        foreach (func_get_args() as $level) {
            $this->levels = array_merge($this->levels, $this->prepareLevel($level));
        }

        return $this;
    }

    public function unshiftLevel()
    {
        foreach (func_get_args() as $level) {
            $this->levels = array_merge($this->prepareLevel($level), $this->levels);
        }

        return $this;
    }

    public function nestLevel()
    {
        foreach (func_get_args() as $nested) {
            $this->nested[] = $nested;

            $this->levels = array_merge(array_map(function ($level) use ($nested) {
            
                return "$level.$nested";

            }, $this->levels), $this->levels);
        }

        return $this;
    }

    public function clearLevels()
    {
        $this->levels = array();
        $this->nested = array();

        return $this;
    }

    public function resolve($slug, $domain = false)
    {
        $domain_path = $this->domainPath($domain);

        // 1) Local app, with runtime-defined levels and nested levels:
        // - prefix.resources.level.nested(.nested-n).slug
        // - prefix.resources.level-n.nested(.nested-n).slug
        foreach ($this->levels as $level) {
            if ($this->view->exists("$domain_path.$level.$slug")) {
                return "$domain_path.$level.$slug";
            }
        }

        // 2) Local app: prefix.resources.slug
        if ($this->view->exists("$domain_path.$slug")) {
            return "$domain_path.$slug";
        }

        // 3) Local app: prefix.templates.slug
        $prefix = $this->prefix();
        if ($this->view->exists("$prefix.templates.$slug")) {
            return "$prefix.templates.$slug";
        }

        $domain = $domain ? str_plural($domain) : $this->domain;

        foreach ($this->levels as $level) {
        // 4) Clumsy package with resource domain, runtime-defined actions and nested actions:
            // - resources.level.nested(.nested-n).slug
            // - resources.level-n.nested(.nested-n).slug
            if ($domain && $this->view->exists("clumsy::{$domain}.$level.$slug")) {
                return "clumsy::{$domain}.$level.$slug";
            }

            // 5) Clumsy templates with runtime-defined levels and nested levels:
            // - level.nested(.nested-n).slug
            // - level-n.nested(.nested-n).slug
            if ($this->view->exists("clumsy::templates.$level.$slug")) {
                return "clumsy::templates.$level.$slug";
            }
        }

        // 6) Clumsy package with resource domain: resources.slug
        if ($domain && $this->view->exists("clumsy::{$domain}.$slug")) {
            return "clumsy::{$domain}.$slug";
        }

        // 7) Clumsy templates: templates.slug
        return "clumsy::templates.$slug";
    }
}
