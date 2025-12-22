<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Return a success response
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return an error response
     *
     * @param string $message
     * @param int $code
     * @param string|null $errorCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $code, $errorCode = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => ['code' => $errorCode]
        ], $code);
    }

    /**
     * Return a paginated response
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @param string|null $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginatedResponse($paginator, $message = null)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ]);
    }

    /**
     * Return a not found response
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFoundResponse($message = 'Recurso no encontrado')
    {
        return $this->errorResponse($message, 404, 'NOT_FOUND');
    }

    /**
     * Return a validation error response
     *
     * @param array $errors
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationErrorResponse($errors, $message = 'Error de validación')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }
}
