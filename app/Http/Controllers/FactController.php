<?php

namespace App\Http\Controllers;

use App\Http\Resources\FactResource;
use App\Models\Fact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FactController extends Controller
{
    /**
     * Show the "fact of the day" web page.
     */
    public function show(): View
    {
        return view('fact', [
            'fact' => Fact::ofTheDay(),
        ]);
    }

    /**
     * Return the "fact of the day" as JSON for API consumers.
     */
    public function json(Request $request): JsonResponse
    {
        $fact = Fact::ofTheDay();

        if ($fact === null) {
            return response()->json([
                'message' => 'No fact has been generated yet.',
            ], 404);
        }

        return FactResource::make($fact)->response($request);
    }
}
