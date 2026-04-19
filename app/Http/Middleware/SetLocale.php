<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie('locale');
        $supportedLocales = ['en', 'fr'];

        if (! $locale || ! in_array($locale, $supportedLocales)) {
            $locale = $request->getPreferredLanguage($supportedLocales);
            Cookie::queue('locale', $locale, 60 * 24 * 365);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
