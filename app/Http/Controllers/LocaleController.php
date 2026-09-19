<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\AppLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(AppLocale::isSupported($locale), 404);

        $request->session()->put('locale', $locale);

        return redirect()->back(fallback: route('landing'));
    }
}
