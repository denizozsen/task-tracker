<?php

namespace App\Http\Middleware;

use App\Service\LoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class EnsureAuthenticated
{
    private $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function handle(Request $request, \Closure $next, $guard = null)
    {
        $userId             = $request->route('userId');
        $authorizationValue = $request->header('Authorization');

        try {
            $this->loginService->ensureSessionAuthenticated($authorizationValue, $userId);
        } catch(\Throwable $e) {
            return Response::json([
                'success' => false,
                'error'   => [
                    'type'    => 'not_authenticated',
                    'message' => 'Not authenticated. Please log in first.'
                ]
            ], 401);
        }

        return $next($request);
    }
}
