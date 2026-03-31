<?php

namespace App\Http\Middleware;

use App\Support\SivsoRbac;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'is_super_admin' => SivsoRbac::userIsSuperAdmin($request->user()),
                'permissions' => $request->user()
                    ? $request->user()->getAllPermissions()->pluck('name')->values()->all()
                    : [],
            ],
        ];
    }
}
