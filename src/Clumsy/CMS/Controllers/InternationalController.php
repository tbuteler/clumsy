<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Clumsy\CMS\Facades\International;

class InternationalController extends Controller {

    protected $locales;
    protected $current_locale_code;
    protected $current_locale;

    public function __construct()
    {
        $this->beforeFilter('LaravelLocalizationRoutes');
        $this->beforeFilter('@existingLanguageRedirect');

        $this->beforeFilter('@parseLocales');
    }

    public function parseLocales()
    {
        $this->locales = International::getSupportedLocales();
        $this->current_locale_code = International::getCurrentLocale();
        $this->current_locale = array(
            $this->current_locale_code => array_pull($this->locales, $this->current_locale_code)
        );

        $this->shareLocalesOnViews();
        $this->setEnvironmentLocale();
    }

    public function setEnvironmentLocale()
    {
        set_locale(LC_NUMERIC, get_possible_locales($this->current_locale_code));
    }

    public function shareLocalesOnViews()
    {
        View::share(array(
            'locales'             => $this->locales,
            'current_locale_code' => $this->current_locale_code,
            'current_locale'      => $this->current_locale,
        ));
    }

    public function existingLanguageRedirect($route, $request)
    {
        if (!str_contains(URL::previous(), url()) && (Cookie::has('language') || Session::has('language')))
        {
            $locale = Cookie::get('language', Session::get('language'));
            return Redirect::to($this->translateRoute($locale, $route, $request));
        }
    }

    public function hasRequiredParameters($syntax)
    {
        return (bool)preg_match('/\{[^\}\?]+\}/', $syntax);
    }

    public function localizedRootURL()
    {
        return International::getURLFromRouteNameTranslated($locale, null);
    }

    public function translateRoute($locale, $route, $request)
    {
        $route_name = $route->getName();
        $route_syntax = Lang::has("routes.{$route_name}") ? Lang::get("routes.{$route_name}") : false;

        // If route name doesn't have a translation, attempt to get syntax from router
        if (!$route_syntax)
        {
            if (!Route::has($route_name))
            {
                return $this->localizedRootURL();
            }

            $route_syntax = URL::route($route_name);
            if ($this->hasRequiredParameters($route_syntax))
            {
                return $this->translateRouteWithParameters($locale, $route_syntax, $route, $request);
            }
        
            return International::localizeURL($request->url(), $locale);
        }

        // If syntax contains mandatory parameters, do not attempt to translate route
        if ($this->hasRequiredParameters($route_syntax))
        {
            return $this->translateRouteWithParameters($locale, $route_syntax, $route, $request);
        }

        return International::getURLFromRouteNameTranslated($locale, "routes.{$route_name}");
    }

    public function translateRouteWithParameters($locale, $syntax, $route, $request)
    {
        return $this->localizedRootURL();
    }
}
