<?php namespace Clumsy\CMS\Support;

use Illuminate\Foundation\Application;

class ViewResolver {

	protected $view;

	protected $domain;

	public function __construct(Application $app)
	{
		$this->view = $app->make('clumsy.view-resolver');
	}

	protected function getDomainPrefix()
	{
		return 'admin';
	}

	public function setDomain($domain)
	{
		$this->domain = $domain;
	}

	public function getDomain()
	{
		return $this->domain;
	}

	public function getFullDomain()
	{
		$prefix = $this->getDomainPrefix();
		
		return !$this->domain ? $prefix : "$prefix.{$this->domain}";
	}

	public function resolve($slug)
	{
		// 1) Local app: prefix.resources.slug
		$full_domain = $this->getFullDomain();
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
        
        // 3) Clumsy package: resources.slug
        if ($this->domain && $this->view->exists("clumsy::{$this->domain}.$slug"))
        {
        	return "clumsy::{$this->domain}.$slug";
        }

        // 4) Clumsy package: templates.slug
        return "clumsy::templates.$slug";
	}
}