<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->hasSession() 
            ? $request->session()->get('locale', config('app.locale', 'lv'))
            : Session::get('locale', config('app.locale', 'lv'));

        if (!in_array($locale, ['lv', 'en'], true)) {
            $locale = 'lv';
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
