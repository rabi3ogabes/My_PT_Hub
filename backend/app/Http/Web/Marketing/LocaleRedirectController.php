<?php

namespace App\Http\Web\Marketing;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** "/" → "/{locale}" chosen from Accept-Language (x-default is English). */
class LocaleRedirectController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $locale = $request->getPreferredLanguage(SetLocale::SUPPORTED) ?? 'en';

        return redirect()->route('marketing.home', ['locale' => $locale], 302)
            ->header('Vary', 'Accept-Language')
            ->header('Cache-Control', 'public, max-age=300');
    }
}
