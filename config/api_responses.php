<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Response Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for standardizing API responses
    | across the application. It defines the structure, status codes, and
    | common messages used in API responses.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Response Structure
    |--------------------------------------------------------------------------
    |
    | Defines the standard structure for API responses
    |
    */

    'structure' => [
        'success' => [
            'success' => true,
            'data' => null, // The actual data
            'message' => '', // Success message
            'timestamp' => null, // ISO 8601 timestamp
            'request_id' => null, // Unique request identifier (optional)
        ],
        'error' => [
            'success' => false,
            'message' => '', // Error message
            'error' => '', // Detailed error (only in development)
            'errors' => [], // Validation errors array
            'timestamp' => null, // ISO 8601 timestamp
            'request_id' => null, // Unique request identifier (optional)
        ],
        'pagination' => [
            'success' => true,
            'data' => [], // Array of items
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'last_page' => 1,
                'from' => 0,
                'to' => 0,
                'has_more_pages' => false,
            ],
            'message' => '',
            'timestamp' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Status Codes
    |--------------------------------------------------------------------------
    |
    | Standard HTTP status codes used in responses
    |
    */

    'status_codes' => [
        'success' => [
            'ok' => 200,
            'created' => 201,
            'accepted' => 202,
            'no_content' => 204,
        ],
        'client_error' => [
            'bad_request' => 400,
            'unauthorized' => 401,
            'forbidden' => 403,
            'not_found' => 404,
            'method_not_allowed' => 405,
            'conflict' => 409,
            'unprocessable_entity' => 422,
            'too_many_requests' => 429,
        ],
        'server_error' => [
            'internal_server_error' => 500,
            'not_implemented' => 501,
            'bad_gateway' => 502,
            'service_unavailable' => 503,
            'gateway_timeout' => 504,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Messages
    |--------------------------------------------------------------------------
    |
    | Default messages for common operations
    |
    */

    'messages' => [
        'success' => [
            'retrieved' => 'Datos obtenidos exitosamente',
            'created' => 'Registro creado exitosamente',
            'updated' => 'Registro actualizado exitosamente',
            'deleted' => 'Registro eliminado exitosamente',
            'stored' => 'Datos guardados exitosamente',
            'uploaded' => 'Archivo subido exitosamente',
            'processed' => 'Operación procesada exitosamente',
        ],
        'error' => [
            'general' => 'Ha ocurrido un error inesperado',
            'validation' => 'Los datos proporcionados no son válidos',
            'not_found' => 'El recurso solicitado no fue encontrado',
            'unauthorized' => 'No tienes permisos para realizar esta acción',
            'forbidden' => 'Acceso denegado',
            'server_error' => 'Error interno del servidor',
            'bad_request' => 'La solicitud es inválida',
            'conflict' => 'Conflicto con el estado actual del recurso',
            'too_many_requests' => 'Demasiadas solicitudes. Inténtalo más tarde',
        ],
        'validation' => [
            'required' => 'Este campo es obligatorio',
            'email' => 'Debe ser una dirección de correo electrónico válida',
            'unique' => 'Este valor ya está en uso',
            'min' => 'Debe tener al menos :min caracteres',
            'max' => 'No debe exceder :max caracteres',
            'numeric' => 'Debe ser un número',
            'exists' => 'El valor seleccionado no es válido',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Default pagination configuration
    |
    */

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
        'include_stats' => true, // Include statistics in paginated responses
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | Configuration for error handling and debugging
    |
    */

    'error_handling' => [
        'include_stack_trace' => env('APP_DEBUG', false), // Include stack traces in development
        'include_request_id' => true, // Include unique request identifier
        'log_errors' => true, // Log errors to storage/logs
        'mask_sensitive_data' => true, // Mask sensitive information in errors
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Transformation
    |--------------------------------------------------------------------------
    |
    | Settings for automatic response transformation
    |
    */

    'transformation' => [
        'auto_transform' => false, // Automatically transform responses (requires middleware)
        'include_timestamp' => true, // Include timestamp in all responses
        'include_request_id' => true, // Include request ID in all responses
        'snake_case_keys' => true, // Convert camelCase to snake_case
        'timezone' => env('APP_TIMEZONE', 'UTC'), // Response timezone
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Types
    |--------------------------------------------------------------------------
    |
    | Supported response content types
    |
    */

    'content_types' => [
        'json' => 'application/json',
        'xml' => 'application/xml',
        'csv' => 'text/csv',
        'pdf' => 'application/pdf',
    ],
];
