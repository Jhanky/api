# Estándar de Respuestas API - VatioCore

Este documento define las convenciones y estructura estándar para todas las respuestas de la API REST del proyecto VatioCore.

---

## Tabla de Contenidos

1. [Estructura General](#estructura-general)
2. [Tipos de Respuesta](#tipos-de-respuesta)
3. [Códigos de Estado HTTP](#códigos-de-estado-http)
4. [Métodos del ApiResponseTrait](#métodos-del-apiresponsetrait)
5. [Ejemplos de Implementación](#ejemplos-de-implementación)
6. [Mensajes Estándar](#mensajes-estándar)
7. [Paginación](#paginación)
8. [Manejo de Errores](#manejo-de-errores)

---

## Estructura General

Todas las respuestas de la API siguen un formato JSON consistente. La estructura base incluye:

```json
{
  "success": true|false,
  "data": { ... } | null,
  "message": "Mensaje descriptivo",
  "timestamp": "2025-12-17T21:42:03.000000Z",
  "request_id": "uuid-único"
}
```

### Campos Base

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `success` | `boolean` | Indica si la operación fue exitosa |
| `data` | `object\|array\|null` | Datos de respuesta (puede ser null en errores o eliminaciones) |
| `message` | `string` | Mensaje descriptivo de la operación |
| `timestamp` | `string` | Fecha y hora en formato ISO 8601 |
| `request_id` | `string` | UUID único para rastreo de la solicitud |

---

## Tipos de Respuesta

### 1. Respuesta Exitosa Simple

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Ejemplo"
  },
  "message": "Datos obtenidos exitosamente",
  "timestamp": "2025-12-17T21:42:03.000000Z",
  "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
}
```

### 2. Respuesta de Error

```json
{
  "success": false,
  "message": "El recurso solicitado no fue encontrado",
  "errors": [],
  "timestamp": "2025-12-17T21:42:03.000000Z",
  "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
}
```

### 3. Respuesta de Validación

```json
{
  "success": false,
  "message": "Los datos proporcionados no son válidos",
  "errors": {
    "email": ["El campo email es obligatorio"],
    "password": ["La contraseña debe tener al menos 8 caracteres"]
  },
  "timestamp": "2025-12-17T21:42:03.000000Z",
  "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
}
```

### 4. Respuesta Paginada

```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Item 1" },
    { "id": 2, "name": "Item 2" }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4,
    "from": 1,
    "to": 15,
    "has_more_pages": true
  },
  "message": "Datos obtenidos exitosamente",
  "timestamp": "2025-12-17T21:42:03.000000Z",
  "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
}
```

---

## Códigos de Estado HTTP

### Éxito (2xx)

| Código | Constante | Uso |
|--------|-----------|-----|
| `200` | `ok` | Solicitud exitosa (GET, PUT) |
| `201` | `created` | Recurso creado exitosamente (POST) |
| `202` | `accepted` | Solicitud aceptada para procesamiento asíncrono |
| `204` | `no_content` | Sin contenido (DELETE exitoso) |

### Error del Cliente (4xx)

| Código | Constante | Uso |
|--------|-----------|-----|
| `400` | `bad_request` | Solicitud malformada |
| `401` | `unauthorized` | No autenticado |
| `403` | `forbidden` | No autorizado |
| `404` | `not_found` | Recurso no encontrado |
| `405` | `method_not_allowed` | Método HTTP no permitido |
| `409` | `conflict` | Conflicto con estado actual |
| `422` | `unprocessable_entity` | Error de validación |
| `429` | `too_many_requests` | Límite de tasa excedido |

### Error del Servidor (5xx)

| Código | Constante | Uso |
|--------|-----------|-----|
| `500` | `internal_server_error` | Error interno del servidor |
| `501` | `not_implemented` | Funcionalidad no implementada |
| `502` | `bad_gateway` | Gateway inválido |
| `503` | `service_unavailable` | Servicio no disponible |
| `504` | `gateway_timeout` | Tiempo de espera del gateway |

---

## Métodos del ApiResponseTrait

Todos los controladores extienden el `Controller` base que incluye el trait `ApiResponseTrait`. Los siguientes métodos están disponibles:

### Respuestas Exitosas

```php
// Respuesta exitosa genérica (HTTP 200)
$this->successResponse($data, $message, $statusCode, $additionalData);

// Recurso creado (HTTP 201)
$this->createdResponse($data, $message);

// Recurso actualizado (HTTP 200)
$this->updatedResponse($data, $message);

// Recurso eliminado (HTTP 200)
$this->deletedResponse($message);

// Operación aceptada para procesamiento (HTTP 202)
$this->acceptedResponse($data, $message);

// Respuesta paginada
$this->paginationResponse($paginator, $message, $additionalData);
```

### Respuestas de Error

```php
// Error genérico
$this->errorResponse($message, $errors, $statusCode, $errorDetail);

// Error de validación (HTTP 422)
$this->validationErrorResponse($errors, $message);

// Recurso no encontrado (HTTP 404)
$this->notFoundResponse($resourceName);

// No autenticado (HTTP 401)
$this->unauthorizedResponse($message);

// No autorizado (HTTP 403)
$this->forbiddenResponse($message);

// Manejo de excepciones
$this->handleException($exception, $customMessage);
```

---

## Ejemplos de Implementación

### Controlador CRUD Típico

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Example;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExampleController extends Controller
{
    /**
     * Listar todos los registros (con paginación)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Example::query();
            
            // Aplicar filtros
            if ($request->has('search')) {
                $query->search($request->search);
            }
            
            $examples = $query->paginate($request->get('per_page', 15));
            
            return $this->paginationResponse($examples);
            
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los registros');
        }
    }

    /**
     * Crear un nuevo registro
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $example = Example::create($request->all());

            return $this->createdResponse($example, 'Registro creado exitosamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al crear el registro');
        }
    }

    /**
     * Mostrar un registro específico
     */
    public function show(string $id): JsonResponse
    {
        try {
            $example = Example::findOrFail($id);
            
            return $this->successResponse($example);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Registro');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener el registro');
        }
    }

    /**
     * Actualizar un registro
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $example = Example::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $example->update($request->all());

            return $this->updatedResponse($example, 'Registro actualizado exitosamente');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Registro');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar el registro');
        }
    }

    /**
     * Eliminar un registro
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $example = Example::findOrFail($id);
            $example->delete();

            return $this->deletedResponse('Registro eliminado exitosamente');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Registro');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al eliminar el registro');
        }
    }
}
```

---

## Mensajes Estándar

### Mensajes de Éxito

| Clave | Mensaje |
|-------|---------|
| `retrieved` | Datos obtenidos exitosamente |
| `created` | Registro creado exitosamente |
| `updated` | Registro actualizado exitosamente |
| `deleted` | Registro eliminado exitosamente |
| `stored` | Datos guardados exitosamente |
| `uploaded` | Archivo subido exitosamente |
| `processed` | Operación procesada exitosamente |

### Mensajes de Error

| Clave | Mensaje |
|-------|---------|
| `general` | Ha ocurrido un error inesperado |
| `validation` | Los datos proporcionados no son válidos |
| `not_found` | El recurso solicitado no fue encontrado |
| `unauthorized` | No tienes permisos para realizar esta acción |
| `forbidden` | Acceso denegado |
| `server_error` | Error interno del servidor |
| `bad_request` | La solicitud es inválida |
| `conflict` | Conflicto con el estado actual del recurso |
| `too_many_requests` | Demasiadas solicitudes. Inténtalo más tarde |

---

## Paginación

### Configuración por Defecto

```php
'pagination' => [
    'default_per_page' => 15,
    'max_per_page' => 100,
    'include_stats' => true,
]
```

### Parámetros de Query

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `page` | `int` | `1` | Número de página |
| `per_page` | `int` | `15` | Registros por página (máximo 100) |

### Estructura de Paginación

```json
{
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10,
    "from": 1,
    "to": 15,
    "has_more_pages": true
  }
}
```

---

## Manejo de Errores

### Configuración de Errores

```php
'error_handling' => [
    'include_stack_trace' => env('APP_DEBUG', false),
    'include_request_id' => true,
    'log_errors' => true,
    'mask_sensitive_data' => true,
]
```

### Detalle de Error (Solo en Desarrollo)

Cuando `APP_DEBUG=true`, las respuestas de error incluyen el campo `error` con detalles adicionales:

```json
{
  "success": false,
  "message": "Error al procesar la solicitud",
  "error": "SQLSTATE[42S22]: Column not found...",
  "errors": [],
  "timestamp": "...",
  "request_id": "..."
}
```

### Mapeo Automático de Excepciones

El trait mapea automáticamente las siguientes excepciones a códigos HTTP:

| Excepción | Código HTTP |
|-----------|-------------|
| `ModelNotFoundException` | 404 |
| `AuthorizationException` | 403 |
| `AuthenticationException` | 401 |
| `ValidationException` | 422 |
| Cualquier otra `Exception` | 500 |

---

## Archivos Relacionados

- **Trait**: `app/Traits/ApiResponseTrait.php`
- **Configuración**: `config/api_responses.php`
- **Controlador Base**: `app/Http/Controllers/Controller.php`

---

## Buenas Prácticas

1. **Siempre usar el trait**: No retornar respuestas JSON directamente; usar los métodos del `ApiResponseTrait`.

2. **Validación temprana**: Validar los datos de entrada al inicio del método.

3. **Try-catch apropiado**: Envolver la lógica en bloques try-catch para manejar excepciones específicas.

4. **Mensajes descriptivos**: Usar mensajes claros y en español para el usuario final.

5. **Códigos HTTP correctos**: Usar el código HTTP apropiado según el resultado de la operación.

6. **Logging**: Los errores se registran automáticamente cuando `log_errors` está habilitado.

---

*Última actualización: Diciembre 2025*
