<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin'                     => \App\Http\Middleware\AdminMiddleware::class,
            'superadmin'                => \App\Http\Middleware\SuperAdminMiddleware::class,
            'prevent_user_id_injection' => \App\Http\Middleware\PreventUserIdInjection::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\PreventUserIdInjection::class,
            \App\Http\Middleware\ApiEncryptionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render JSON for API endpoints
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->wantsJson(),
        );

        // Custom Throttle / Rate Limit Renderer dengan countdown waktu yang jelas
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $headers = $e->getHeaders();
                $retryAfter = isset($headers['Retry-After']) ? (int) $headers['Retry-After'] : 60;

                if ($retryAfter >= 60) {
                    $minutes = ceil($retryAfter / 60);
                    $timeText = "{$minutes} menit";
                } else {
                    $timeText = "{$retryAfter} detik";
                }

                return response()->json([
                    'message'     => "Terlalu banyak percobaan. Silakan tunggu {$timeText} sebelum mencoba kembali.",
                    'retry_after' => $retryAfter,
                ], 429, $headers);
            }

            return response()->view('errors.429', [], 429);
        });

        // Custom Exception Renderer for Web / Admin routes
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return null; // Handled as JSON automatically
            }

            if ($e instanceof MethodNotAllowedHttpException) {
                return response()->view('errors.405', [], 405);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->view('errors.404', [], 404);
            }

            if ($e instanceof AuthenticationException) {
                return redirect()->route('admin.login')->with('error', 'Silakan login terlebih dahulu.');
            }

            if ($e instanceof AuthorizationException) {
                return response()->view('errors.403', ['exception' => $e], 403);
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                if (view()->exists("errors.{$status}")) {
                    return response()->view("errors.{$status}", ['exception' => $e], $status);
                }
            }
        });
    })->create();
