<?php namespace Clumsy\CMS\Controllers\FrontEnd;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Clumsy\CMS\Facades\International;
use Clumsy\Utils\Facades\HTTP;

class InternationalController extends Controller {

    protected $locales;
    protected $current_locale_code;
    protected $current_locale;

    protected $use_localized_routes = false;
    protected $localized_routes_filter = null;
    protected $route_translation_prefix;

    protected $cookie_slug;
    protected $session_slug;

    public $current_route_localized = array();

    public function __construct()
    {
        if ($this->use_localized_routes && $this->localized_routes_filter)
        {
            $this->beforeFilter($this->localized_routes_filter);
        }

        $this->beforeFilter('@existingLanguageRedirect');

        $this->beforeFilter('@parseLocales');
    }

    public function parseLocales($route, $request)
    {
        $this->locales = International::getSupportedLocales();
        $this->current_locale_code = International::getCurrentLocale();
        $this->current_locale = array(
            $this->current_locale_code => array_pull($this->locales, $this->current_locale_code)
        );

        foreach ($this->locales as $locale => $locale_array)
        {
            $url = $this->translateRoute($locale, $route, $request);
            $this->current_route_localized[$locale] = $this->prepareLocalizedUrl($url);
        }

        $this->shareLocalesOnViews();
        $this->setSessionLocale();
        $this->setEnvironmentLocale();
    }

    public function setSessionLocale()
    {
        Session::put($this->session_slug, $this->current_locale_code);
    }

    public function setEnvironmentLocale()
    {
        set_locale(LC_NUMERIC, get_possible_locales($this->current_locale_code));
    }

    public function shareLocalesOnViews()
    {
        View::share(array(
            'locales'                 => $this->locales,
            'current_locale_code'     => $this->current_locale_code,
            'current_locale'          => $this->current_locale,
            'current_route_localized' => $this->current_route_localized,
        ));
    }

    public function existingLanguageRedirect($route, $request)
    {
        if (
            !Session::get('clumsy.locale-redirected')
            && !Input::exists('change_locale')
            && (Cookie::has($this->cookie_slug) || Session::has($this->session_slug))
        )
        {
            $locale = Cookie::get($this->cookie_slug, Session::get($this->session_slug));
            if ($locale !== International::getCurrentLocale())
            {
                return Redirect::to($this->translateRoute($locale, $route, $request))->with(array(
                    'clumsy.locale-redirected' => true,
                ));
            }
        }
    }

    public function hasRequiredParameters($syntax)
    {
        return (bool)preg_match('/\{[^\}\?]+\}/', $syntax);
    }

    public function routeTranslationIndex($name = null)
    {
        $index = array($this->route_translation_prefix);

        if ($name)
        {
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

        if (!$this->use_localized_routes)
        {
            return International::localizeURL($url, $locale);
        }

        $route_name = $route->getName();
        $route_syntax = Lang::has($this->routeTranslationIndex($route_name))
                        ? Lang::get($this->routeTranslationIndex($route_name))
                        : false;

        // If route name doesn't have a translation, attempt to get syntax from router
        if (!$route_syntax)
        {
            if (!Route::has($route_name))
            {
                return $this->localizedRootURL($locale);
            }

            $route_syntax = URL::route($route_name);
            if ($this->hasRequiredParameters($route_syntax))
            {
                return $this->translateRouteWithParameters($locale, $route_syntax, $route, $request);
            }

            return International::localizeURL($url, $locale);
        }

        // If syntax contains mandatory parameters, do not attempt to translate route
        if ($this->hasRequiredParameters($route_syntax))
        {
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
