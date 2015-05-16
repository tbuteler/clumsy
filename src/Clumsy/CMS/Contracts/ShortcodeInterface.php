<?php namespace Clumsy\CMS\Contracts;

interface ShortcodeInterface {

    public function wrap($string);

	public function add($key, $description);

	public function remove($key);

	public function setCodes(Array $shortcodes);

	public function availableCodes();

	public function parse($content);
}