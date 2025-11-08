<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    public function setLocale(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:en,ha,ar,fr'
        ]);

        $user = $request->user();
        if ($user) {
            $meta = $user->meta ?? [];
            $meta['locale'] = $request->locale;
            $user->update(['meta' => $meta]);
        }

        return response()->json([
            'message' => 'Locale updated successfully',
            'locale' => $request->locale
        ]);
    }

    public function getLocale(Request $request)
    {
        $locale = 'en';
        
        if ($request->user()) {
            $locale = $request->user()->meta['locale'] ?? 'en';
        }

        return response()->json([
            'locale' => $locale
        ]);
    }
}

