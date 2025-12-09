# Estructura de Middleware API y APK

## Descripción General

El sistema de middleware ha sido reorganizado y optimizado para separar claramente las funcionalidades del frontend web (React) y la aplicación móvil (React Native).

## Estructura de Carpetas

```
app/Http/Middleware/
├── Api/                    # Middleware específicos para frontend web
│   ├── CheckPermission.php
│   ├── CheckRole.php
│   └── WebOptimization.php
├── Apk/                    # Middleware específicos para aplicación móvil
│   ├── MobileAuth.php
│   ├── MobileOptimization.php
│   └── MobileRateLimit.php
├── Shared/                 # Middleware compartidos
│   ├── LoggingMiddleware.php
│   └── ResponseOptimization.php
└── [middleware existentes] # Middleware base de Laravel
```

## Middleware por Plataforma

### API (Frontend Web - React)

#### 1. **CheckPermission** (`api.permission`)
- **Propósito**: Verificación de permisos específicos
- **Uso**: `Route::middleware('api.permission:admin')`
- **Características**:
  - Verificación de autenticación
  - Validación de permisos específicos
  - Respuestas detalladas de error
  - Información de permisos en la request

#### 2. **CheckRole** (`api.role`)
- **Propósito**: Verificación de roles específicos
- **Uso**: `Route::middleware('api.role:admin')`
- **Características**:
  - Verificación de autenticación
  - Validación de roles específicos
  - Información de roles y permisos en la request

#### 3. **WebOptimization** (`api.web.optimization`)
- **Propósito**: Optimizaciones específicas para web
- **Características**:
  - Cache para datos estáticos (5 minutos)
  - Headers de seguridad
  - Compresión de respuestas
  - Headers informativos

### APK (Aplicación Móvil - React Native)

#### 1. **MobileAuth** (`apk.mobile.auth`)
- **Propósito**: Autenticación optimizada para móvil
- **Características**:
  - Verificación de sesión móvil
  - Validación de tokens con scope 'mobile'
  - Mensajes de error específicos para móvil
  - Información de usuario optimizada

#### 2. **MobileOptimization** (`apk.mobile.optimization`)
- **Propósito**: Optimizaciones específicas para móvil
- **Características**:
  - Cache optimizado para móvil (1 minuto)
  - Headers de compresión
  - Detección de PWA
  - Headers específicos de móvil

#### 3. **MobileRateLimit** (`apk.mobile.rate.limit`)
- **Propósito**: Rate limiting específico para móvil
- **Características**:
  - Límites más permisivos (100 requests/minuto)
  - Rate limiting por usuario y IP
  - Headers informativos de rate limit
  - Respuestas optimizadas para móvil

### Middleware Compartidos

#### 1. **LoggingMiddleware** (`shared.logging`)
- **Propósito**: Logging unificado para ambas plataformas
- **Características**:
  - Log de peticiones y respuestas
  - Request ID único
  - Métricas de rendimiento
  - Logs separados por canal

#### 2. **ResponseOptimization** (`shared.response.optimization`)
- **Propósito**: Optimizaciones comunes de respuesta
- **Características**:
  - Compresión de JSON
  - Headers de seguridad comunes
  - Headers informativos
  - Optimización de tamaño

## Grupos de Middleware

### `api-web`
```php
[
    PlatformDetection::class,
    LoggingMiddleware::class,
    WebOptimization::class,
    ResponseOptimization::class,
    ThrottleRequests::class,
    SubstituteBindings::class,
]
```

### `api-mobile`
```php
[
    PlatformDetection::class,
    LoggingMiddleware::class,
    MobileOptimization::class,
    MobileRateLimit::class,
    ResponseOptimization::class,
    SubstituteBindings::class,
]
```

## Uso en Rutas

### Rutas Web (API)
```php
// Middleware completo para web
Route::middleware(['auth:sanctum', 'api-web'])->group(function () {
    // Rutas web
});

// Middleware específicos
Route::middleware(['auth:sanctum', 'api.permission:admin'])->group(function () {
    // Rutas que requieren permiso admin
});
```

### Rutas Móvil (APK)
```php
// Middleware completo para móvil
Route::middleware(['auth:sanctum', 'api-mobile'])->group(function () {
    // Rutas móvil
});

// Middleware específicos
Route::middleware(['apk.mobile.auth', 'apk.mobile.rate.limit:50,1'])->group(function () {
    // Rutas con rate limiting personalizado
});
```

## Configuración de Logging

### Canales de Log
- **`api`**: Logs generales de API (30 días)
- **`mobile`**: Logs específicos de móvil (30 días)
- **`auth`**: Logs de autenticación (90 días)
- **`integrations`**: Logs de integraciones (60 días)

### Ejemplo de Uso
```php
// En controladores
Log::channel('api')->info('API Request', $data);
Log::channel('mobile')->warning('Mobile Error', $error);
Log::channel('auth')->error('Auth Failed', $attempt);
```

## Optimizaciones Implementadas

### 1. **Cache Inteligente**
- **Web**: 5 minutos para datos estáticos
- **Móvil**: 1 minuto para datos dinámicos
- **ETags**: Para validación de cache

### 2. **Compresión**
- **JSON**: Eliminación de espacios innecesarios
- **Headers**: Compresión automática
- **Tamaño**: Optimización para respuestas > 1KB

### 3. **Rate Limiting**
- **Web**: 60 requests/minuto (estándar)
- **Móvil**: 100 requests/minuto (optimizado)
- **Personalizable**: Por ruta específica

### 4. **Headers de Seguridad**
- **X-Content-Type-Options**: nosniff
- **X-Frame-Options**: DENY
- **X-XSS-Protection**: 1; mode=block
- **Referrer-Policy**: strict-origin-when-cross-origin

### 5. **Headers Informativos**
- **X-API-Version**: 1.0.0
- **X-Platform**: web|mobile
- **X-Response-Time**: Tiempo de respuesta
- **X-Request-ID**: ID único de petición

## Monitoreo y Métricas

### Logs Automáticos
- **Request ID**: Identificador único por petición
- **Response Time**: Tiempo de respuesta en milisegundos
- **Memory Usage**: Uso de memoria
- **Platform**: Plataforma detectada
- **User ID**: ID del usuario autenticado

### Métricas de Rendimiento
- **Tiempo de respuesta**: Automático en headers
- **Uso de memoria**: Registrado en logs
- **Rate limiting**: Headers informativos
- **Cache hits**: Headers de cache

## Beneficios de la Reorganización

### 1. **Separación Clara**
- Middleware específicos por plataforma
- Optimizaciones diferenciadas
- Mantenimiento más fácil

### 2. **Rendimiento Optimizado**
- Cache inteligente por plataforma
- Compresión automática
- Rate limiting personalizado

### 3. **Seguridad Mejorada**
- Headers de seguridad específicos
- Validaciones diferenciadas
- Logging detallado

### 4. **Monitoreo Avanzado**
- Logs separados por canal
- Métricas de rendimiento
- Request tracking

### 5. **Escalabilidad**
- Middleware modulares
- Configuración flexible
- Fácil extensión

## Próximos Pasos

1. **Implementar métricas** de rendimiento en tiempo real
2. **Crear dashboard** de monitoreo
3. **Optimizar cache** basado en uso
4. **Implementar alertas** automáticas
5. **Crear tests** para middleware
