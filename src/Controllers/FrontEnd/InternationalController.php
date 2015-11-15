<?php

namespace Clumsy\CMS\Controllers\FrontEnd;

use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Clumsy\CMS\Facades\International;
use Clumsy\Utils\Facades\HTTP;

class InternationalController extends Controller
{
    protected $locales;
    protected $current_locale_code;
    protected $current_locale;

    protected $use_localized_routes = false;
    protected $localized_routes_filter = null;
    protected $route_translation_prefix;

    protected $cookie_slug;
    protected $session_slug;

    protected $remember_language = true;
    protected $remember_language_on_post_requests = false;
    protected $remember_language_only_methods = [];
    protected $remember_language_except_methods = [];

    public $current_route_localized = [];

    public function __construct()
    {
        if ($this->use_localized_routes && $this->localized_routes_filter) {
            $this->beforeFilter($this->localized_routes_filter);
        }

        if ($this->remember_language) {
            $methods = [];
            if (count($this->remember_language_only_methods)) {
                $methods = ['only' => $this->remember_language_only_methods];
            } elseif (count($this->remember_language_except_methods)) {
                $methods = ['except' => $this->remember_language_except_methods];
            }

            $this->beforeFilter('@rememberLanguage', $methods);
        }

        $this->beforeFilter('@parseLocales');
    }

    public function parseLocales($route, $request)
    {
        $this->locales = International::getSupportedLocales();
        $this->current_locale_code = International::getCurrentLocale();
        $this->current_locale = [
            $this->current_locale_code => array_pull($this->locales, $this->current_locale_code)
        ];

        foreach ($this->locales as $locale => $locale_array) {
            $url = $this->translateRoute($locale, $route, $request);
            $this->current_route_localized[$locale] = $this->prepareLocalizedUrl($url);
        }

        $this->shareLocalesOnViews();
        $this->setEnvironmentLocale();
    }

    public function setEnvironmentLocale()
    {
        set_locale(LC_NUMERIC, get_possible_locales($this->current_locale_code));
    }

    public function shareLocalesOnViews()
    {
        view()->share([
            'locales'                 => $this->locales,
            'current_locale_code'     => $this->current_locale_code,
            'current_locale'          => $this->current_locale,
            'current_route_localized' => $this->current_route_localized,
        ]);
    }

    public function rememberLanguage($route, $request)
    {
        if (// Only attempt to remember language for GET/HEAD requests, unless settings explicitly set
            (in_array(Str::lower($request->method()), ['get', 'head']) || $this->remember_language_on_post_requests)
            && !Session::get('clumsy.locale-redirected')
            && !Input::exists('change_locale')
            && (Session::has($this->session_slug) || Cookie::has($this->cookie_slug))
        ) {
            $locale = Session::get($this->cookie_slug, Cookie::get($this->session_slug));
            if ($locale !== International::getCurrentLocale()) {
                return Redirect::to($this->translateRoute($locale, $route, $request))->with([
                    'clumsy.locale-redirected' => true,
                ]);
            }
        }

        $locale = International::getCurrentLocale();

        if ($this->session_slug) {
            Session::put($this->session_slug, $locale);
        }

        if ($this->cookie_slug) {
            Cookie::queue(Cookie::forever($this->cookie_slug, $locale));
        }
    }

    public function hasRequiredParameters($syntax)
    {
        return (bool)preg_match('/\{[^\}\?]+\}/', $syntax);
    }

    public function routeTranslationIndex($name = null)
    {
        $index = [$this->route_translation_prefix];

        if ($name) {
            $index[] = $name;
        }

        return implode('.', $index);
    }

    public function localizedRootURL($locale)
    {
        return International::localizeURL(url().'/', $locale);
    }

    public function translateRoute($locale, $route, $request)
    {
        // Add trailing slash, just in case
        $url = preg_match('/^(.*)\/$/', $request->url()) ? $request->url() : $request->url().'/';

        if (!$this->use_localized_routes) {
            return International::localizeURL($url, $locale);
        }

        $route_name = $route->getName();
        $route_syntax = Lang::has($this->routeTranslationIndex($route_name))
                        ? Lang::get($this->routeTranslationIndex($route_name))
                        : false;

        // If route name doesn't have a translation, attempt to get syntax from router
        if (!$route_syntax) {
            if (!Route::has($route_name)) {
                return $this->localizedRootURL($locale);
            }

            $route_syntax = route($route_name);
            if ($this->hasRequiredParameters($route_syntax)) {
                return $this->translateRouteWithParameters($locale, $route_syntax, $route, $request);
            }

            return International::localizeURL($url, $locale);
        }

        // If syntax contains mandatory parameters, do not attempt to translate route
        if ($this->hasRequiredParameters($route_syntax)) {
            return $this->translateRouteWithParameters($locale, $route_syntax, $route, $request);
        }

        return International::getURLFromRouteNameTranslated($locale, $this->routeTranslationIndex($route_name));
    }

    public function translateRouteWithParameters($locale, $syntax, $route, $request)
    {
        return $this->localizedRootURL($locale);
    }

    public function prepareLocalizedUrl($url)
    {
        return HTTP::queryStringAdd($url, 'change_locale');
    }
}
