<?php

namespace Clumsy\CMS\Library;

use Clumsy\CMS\Contracts\ShortcodeInterface;

class Shortcode implements ShortcodeInterface
{
    protected $start_delimiter = '[';
    protected $end_delimiter = ']';

    protected $shortcodes = [];

    protected function decode($shortcode)
    {
        return camel_case($shortcode);
    }

    protected function encode($method)
    {
        return str_replace('_', ' ', snake_case($method));
    }

    public function regex($string)
    {
        return "/\\{$this->start_delimiter}$string(.*)\\{$this->end_delimiter}/i";
    }

    public function add($key, $description)
    {
        $this->shortcodes[$key] = $description;
    }

    public function addMany($codes)
    {
        $this->shortcodes = array_merge($this->shortcodes, $codes);
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
        foreach (array_keys($this->availableCodes()) as $shortcode) {
            $regex = $this->regex($shortcode);
            if (preg_match_all($regex, $content, $matches)) {
                $params = last($matches);
                $matches = head($matches);
                foreach ($matches as $i => $match) {
                    parse_str(trim($params[$i]), $shortcode_params);
                    $method = $this->decode($shortcode);
                    if (method_exists($this, $method)) {
                        $args = func_get_args();
                        array_shift($args);
                        array_unshift($args, $shortcode_params);
                        $content = preg_replace($regex, call_user_func_array(array($this, $method), $args), $content, 1);
                    }
                }
            }
        }

        return $content;
    }

    public function ignore($content)
    {
        foreach (array_keys($this->availableCodes()) as $shortcode) {
            if (preg_match($this->regex($shortcode), $content)) {
                $content = preg_replace($this->regex($shortcode), '', $content);
            }
        }

        return $content;
    }
}
