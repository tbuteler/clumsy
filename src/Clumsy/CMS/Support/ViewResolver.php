<?php namespace Clumsy\CMS\Support;

use Illuminate\Foundation\Application;

class ViewResolver {

	protected $view;

	protected $domain;

	public function __construct(Application $app)
	{
		$this->view = $app->make('clumsy.view-resolver-factory');
	}

	protected function getDomainPrefix()
	{
		return 'admin';
	}

	public function setDomain($domain)
	{
		$this->domain = str_plural($domain);
	}

	public function getFullDomain($domain = false)
	{
		$prefix = $this->getDomainPrefix();

        $domain = $domain ? str_plural($domain) : $this->domain;

		return !$this->domain ? $prefix : "$prefix.$domain";
	}

	public function resolve($slug, $domain = false)
	{
		// 1) Local app: prefix.resources.slug
		$full_domain = $this->getFullDomain($domain);
        if ($this->view->exists("$full_domain.$slug"))
        {
            return "$full_domain.$slug";
        }

		// 2) Local app: prefix.templates.slug
        $prefix = $this->getDomainPrefix();
        if ($this->view->exists("$prefix.templates.$slug"))
        {
        	return "$prefix.templates.$slug";
        }

        $domain = $domain ? str_plural($domain) : $this->domain;

        // 3) Clumsy package: resources.slug
        if ($this->domain && $this->view->exists("clumsy::{$domain}.$slug"))
        {
            return "clumsy::{$domain}.$slug";
        }

        // 4) Clumsy package: templates.slug
        return "clumsy::templates.$slug";
	}
}