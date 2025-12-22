<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Solo para peticiones API
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions with standardized JSON responses.
     */
    protected function handleApiException($request, Throwable $exception)
    {
        // Modelo no encontrado
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'model' => class_basename($exception->getModel())
                ]
            ], 404);
        }

        // Ruta no encontrada
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint no encontrado',
                'error' => ['code' => 'ROUTE_NOT_FOUND']
            ], 404);
        }

        // Error de validación
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Los datos proporcionados no son válidos',
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'fields' => $exception->errors()
                ]
            ], 422);
        }

        // No autenticado
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
                'error' => ['code' => 'UNAUTHENTICATED']
            ], 401);
        }

        // Error interno (solo en producción ocultar detalles)
        $message = config('app.debug')
            ? $exception->getMessage()
            : 'Error interno del servidor';

        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'trace' => config('app.debug') ? $exception->getTrace() : null
            ]
        ], 500);
    }
}
