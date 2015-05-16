<?php namespace Clumsy\CMS\Library;

use Clumsy\CMS\Contracts\ShortcodeInterface;

class Shortcode implements ShortcodeInterface {

	protected $start_limiter = '[';
	protected $end_limiter = ']';

	protected $shortcodes = array();

	protected function decode($shortcode)
	{
	    return camel_case($shortcode);
	}

	protected function encode($method)
	{
	    return str_replace('_', ' ', snake_case($method));
	}

	public function wrap($string)
	{
		return "{$this->start_limiter}$string{$this->end_limiter}";
	}

	public function add($key, $description)
	{
		$this->shortcodes[$key] = $description;
	}

	public function remove($key)
	{
		$this->shortcodes[$key] = $description;
	}

	public function setCodes(Array $shortcodes)
	{
		$this->shortcodes = $shortcodes;
	}

	public function availableCodes()
	{
	    return $this->shortcodes;
	}

	public function parse($content)
	{
	    foreach (array_keys($this->availableCodes()) as $shortcode)
	    {
	        if (str_contains($content, $this->wrap($shortcode)))
	        {
	            $method = $this->decode($shortcode);
	            $content = str_replace($this->wrap($shortcode), call_user_func_array(array($this, $method), func_get_args()), $content);
	        }
	    }

	    return $content;
	}

	public function ignore($content)
	{
	    foreach (array_keys($this->availableCodes()) as $shortcode)
	    {
	        if (str_contains($content, $this->wrap($shortcode)))
	        {
	            $method = $this->decode($shortcode);
	            $content = str_replace($this->wrap($shortcode), '', $content);
	        }
	    }

	    return $content;
	}

	public function __call($name, $arguments)
	{
	    return $this->wrap($this->encode($name));
	}
}