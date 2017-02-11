<?php

namespace Clumsy\CMS\Support;

use Illuminate\View\Factory;

class ViewResolver
{
    protected $view;

    protected $domain;

    protected $levels = [];

    protected $nested = [];

    protected $resolved = [];

    public function __construct(Factory $view)
    {
        $this->view = $view;
    }

    protected function prefix()
    {
        return 'admin';
    }

    protected function domainPath()
    {
        $prefix = $this->prefix();

        return !$this->domain ? $prefix : "$prefix.{$this->domain}";
    }

    protected function prepareLevel($level)
    {
        $level = snake_case($level);

        if (count($this->nested)) {
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

    protected function remember($key, $resolved)
    {
        $this->resolved[$key] = $resolved;

        return $resolved;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;

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
        $this->levels = [];
        $this->nested = [];

        return $this;
    }

    public function resolve($slug, $domain = false)
    {
        $domainPath = $this->domainPath($domain);
        $cacheKey = "{$slug}.{$domainPath}";

        // Check if we've already resolved this in this request
        if ($resolved = array_get($this->resolved, $cacheKey)) {
            return $resolved;
        }

        // 1) Local app, with runtime-defined levels and nested levels:
        // - prefix.resources.level.nested(.nested-n).slug
        // - prefix.resources.level-n.nested(.nested-n).slug
        foreach ($this->levels as $level) {
            if ($this->view->exists("$domainPath.$level.$slug")) {
                return $this->remember($cacheKey, "{$domainPath}.{$level}.{$slug}");
            }
        }

        // 2) Local app: prefix.resources.slug
        if ($this->view->exists("$domainPath.$slug")) {
            return $this->remember($cacheKey, "{$domainPath}.{$slug}");
        }

        // 3) Local app: prefix.templates.slug
        $prefix = $this->prefix();
        if ($this->view->exists("{$prefix}.templates.{$slug}")) {
            return $this->remember($cacheKey, "{$prefix}.templates.{$slug}");
        }

        $domain = $domain ?: $this->domain;

        foreach ($this->levels as $level) {
            // 4) Clumsy package with resource domain, runtime-defined actions and nested actions:
            // - resources.level.nested(.nested-n).slug
            // - resources.level-n.nested(.nested-n).slug
            if ($domain && $this->view->exists("clumsy::{$domain}.{$level}.{$slug}")) {
                return $this->remember($cacheKey, "clumsy::{$domain}.{$level}.{$slug}");
            }

            // 5) Clumsy templates with runtime-defined levels and nested levels:
            // - level.nested(.nested-n).slug
            // - level-n.nested(.nested-n).slug
            if ($this->view->exists("clumsy::templates.{$level}.{$slug}")) {
                return $this->remember($cacheKey, "clumsy::templates.{$level}.{$slug}");
            }
        }

        // 6) Clumsy package with resource domain: resources.slug
        if ($domain && $this->view->exists("clumsy::{$domain}.{$slug}")) {
            return $this->remember($cacheKey, "clumsy::{$domain}.{$slug}");
        }

        // 7) Clumsy templates: templates.slug
        return $this->remember($cacheKey, "clumsy::templates.{$slug}");
    }
}
