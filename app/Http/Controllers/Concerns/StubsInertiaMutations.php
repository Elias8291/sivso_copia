<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait StubsInertiaMutations
{
    protected function emptyPage(int $perPage = 15): LengthAwarePaginator
    {
        $page = max(1, (int) request()->input('page', 1));

        return new LengthAwarePaginator([], 0, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    protected function ejercicio(Request $request): int
    {
        $def = (int) config('sivso.ejercicio_actual', 2026);

        return (int) $request->input('anio', $def);
    }

    public function noop(): RedirectResponse
    {
        return back();
    }
}
