<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ApiResponseTrait
{
    /**
     * Generate a unique request ID for tracking
     *
     * @return string
     */
    protected function generateRequestId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Get current timestamp in ISO 8601 format
     *
     * @return string
     */
    protected function getCurrentTimestamp(): string
    {
        $timezone = config('api_responses.transformation.timezone', 'UTC');
        return now()->setTimezone($timezone)->toISOString();
    }

    /**
     * Create a standardized success response
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $statusCode
     * @param array $additionalData
     * @return JsonResponse
     */
    protected function successResponse(
        $data = null,
        ?string $message = null,
        int $statusCode = 200,
        array $additionalData = []
    ): JsonResponse {
        $config = config('api_responses');
        $structure = $config['structure']['success'];

        $response = [
            'success' => $structure['success'],
            'data' => $data,
            'message' => $message ?? $config['messages']['success']['retrieved'],
        ];

        if ($config['transformation']['include_timestamp']) {
            $response['timestamp'] = $this->getCurrentTimestamp();
        }

        if ($config['transformation']['include_request_id']) {
            $response['request_id'] = $this->generateRequestId();
        }

        // Merge additional data
        $response = array_merge($response, $additionalData);

        return response()->json($response, $statusCode);
    }

    /**
     * Create a standardized error response
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @param string|null $errorDetail
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message,
        array $errors = [],
        int $statusCode = 500,
        ?string $errorDetail = null
    ): JsonResponse {
        $config = config('api_responses');
        $structure = $config['structure']['error'];

        $response = [
            'success' => $structure['success'],
            'message' => $message,
            'errors' => $errors,
        ];

        if ($config['transformation']['include_timestamp']) {
            $response['timestamp'] = $this->getCurrentTimestamp();
        }

        if ($config['transformation']['include_request_id']) {
            $response['request_id'] = $this->generateRequestId();
        }

        // Include error details only in debug mode
        if ($config['error_handling']['include_stack_trace'] && $errorDetail) {
            $response['error'] = $errorDetail;
        }

        // Log error if configured
        if ($config['error_handling']['log_errors']) {
            Log::error('API Error Response', [
                'message' => $message,
                'errors' => $errors,
                'status_code' => $statusCode,
                'request_id' => $response['request_id'] ?? null,
                'error_detail' => $errorDetail,
            ]);
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create a validation error response
     *
     * @param array $errors
     * @param string|null $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(
        array $errors,
        ?string $message = null
    ): JsonResponse {
        $config = config('api_responses');

        return $this->errorResponse(
            $message ?? $config['messages']['error']['validation'],
            $errors,
            $config['status_codes']['client_error']['unprocessable_entity']
        );
    }

    /**
     * Create a paginated response
     *
     * @param LengthAwarePaginator $paginator
     * @param string|null $message
     * @param array $additionalData
     * @return JsonResponse
     */
    protected function paginationResponse(
        LengthAwarePaginator $paginator,
        ?string $message = null,
        array $additionalData = []
    ): JsonResponse {
        $config = config('api_responses');
        $structure = $config['structure']['pagination'];

        $paginationData = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];

        $response = [
            'success' => $structure['success'],
            'data' => $paginator->items(),
            'pagination' => $paginationData,
            'message' => $message ?? $config['messages']['success']['retrieved'],
        ];

        if ($config['transformation']['include_timestamp']) {
            $response['timestamp'] = $this->getCurrentTimestamp();
        }

        if ($config['transformation']['include_request_id']) {
            $response['request_id'] = $this->generateRequestId();
        }

        // Merge additional data
        $response = array_merge($response, $additionalData);

        return response()->json($response);
    }

    /**
     * Create a not found error response
     *
     * @param string|null $resource
     * @return JsonResponse
     */
    protected function notFoundResponse(?string $resource = null): JsonResponse
    {
        $config = config('api_responses');
        $message = $resource
            ? "El recurso '{$resource}' no fue encontrado"
            : $config['messages']['error']['not_found'];

        return $this->errorResponse(
            $message,
            [],
            $config['status_codes']['client_error']['not_found']
        );
    }

    /**
     * Create an unauthorized error response
     *
     * @param string|null $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(?string $message = null): JsonResponse
    {
        $config = config('api_responses');

        return $this->errorResponse(
            $message ?? $config['messages']['error']['unauthorized'],
            [],
            $config['status_codes']['client_error']['unauthorized']
        );
    }

    /**
     * Create a forbidden error response
     *
     * @param string|null $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(?string $message = null): JsonResponse
    {
        $config = config('api_responses');

        return $this->errorResponse(
            $message ?? $config['messages']['error']['forbidden'],
            [],
            $config['status_codes']['client_error']['forbidden']
        );
    }

    /**
     * Create a created success response
     *
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    protected function createdResponse($data = null, ?string $message = null): JsonResponse
    {
        $config = config('api_responses');

        return $this->successResponse(
            $data,
            $message ?? $config['messages']['success']['created'],
            $config['status_codes']['success']['created']
        );
    }

    /**
     * Create an updated success response
     *
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    protected function updatedResponse($data = null, ?string $message = null): JsonResponse
    {
        $config = config('api_responses');

        return $this->successResponse(
            $data,
            $message ?? $config['messages']['success']['updated']
        );
    }

    /**
     * Create a deleted success response
     *
     * @param string|null $message
     * @return JsonResponse
     */
    protected function deletedResponse(?string $message = null): JsonResponse
    {
        $config = config('api_responses');

        return $this->successResponse(
            null,
            $message ?? $config['messages']['success']['deleted']
        );
    }

    /**
     * Create an accepted response (for async operations)
     *
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    protected function acceptedResponse($data = null, ?string $message = null): JsonResponse
    {
        $config = config('api_responses');

        return $this->successResponse(
            $data,
            $message ?? $config['messages']['success']['processed'],
            $config['status_codes']['success']['accepted']
        );
    }

    /**
     * Handle exceptions and return appropriate error response
     *
     * @param \Exception $exception
     * @param string|null $customMessage
     * @return JsonResponse
     */
    protected function handleException(\Exception $exception, ?string $customMessage = null): JsonResponse
    {
        $config = config('api_responses');

        // Determine status code based on exception type
        $statusCode = $this->getStatusCodeFromException($exception);

        $message = $customMessage ?? $config['messages']['error']['general'];
        $errorDetail = $config['error_handling']['include_stack_trace'] ? $exception->getMessage() : null;

        return $this->errorResponse($message, [], $statusCode, $errorDetail);
    }

    /**
     * Get appropriate HTTP status code from exception
     *
     * @param \Exception $exception
     * @return int
     */
    private function getStatusCodeFromException(\Exception $exception): int
    {
        $config = config('api_responses');

        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $config['status_codes']['client_error']['not_found'];
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $config['status_codes']['client_error']['forbidden'];
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return $config['status_codes']['client_error']['unauthorized'];
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return $config['status_codes']['client_error']['unprocessable_entity'];
        }

        return $config['status_codes']['server_error']['internal_server_error'];
    }
}
