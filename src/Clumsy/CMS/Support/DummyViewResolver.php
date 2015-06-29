<?php namespace Clumsy\CMS\Support;

class DummyViewResolver {

	public function resolve($slug)
	{
        return \Clumsy\CMS\Facades\ViewResolver::resolve($slug);
    }
}