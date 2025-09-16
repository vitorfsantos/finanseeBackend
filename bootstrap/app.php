<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
    // Executa o comando de reset do banco diariamente à meia-noite
    $schedule->command('database:reset-daily')
      ->dailyAt('00:00')
      ->withoutOverlapping()
      ->runInBackground()
      ->appendOutputTo(storage_path('logs/database-reset.log'));
  })
  ->withMiddleware(function (Middleware $middleware): void {
    // Register custom middleware
    $middleware->alias([
      'role' => \App\Http\Middleware\CheckRole::class,
    ]);

    // Configure API middleware group
    $middleware->group('api', [
      \App\Http\Middleware\ForceJsonResponse::class,
      \Illuminate\Http\Middleware\HandleCors::class,
      \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions): void {
    // Return JSON for API routes
    $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
      if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
          'message' => 'Dados de validação inválidos.',
          'errors' => $e->errors()
        ], 422);
      }
    });

    $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
      if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
          'message' => 'Não autenticado.'
        ], 401);
      }
    });

    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
      if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
          'message' => 'Recurso não encontrado.'
        ], 404);
      }
    });
  })->create();
