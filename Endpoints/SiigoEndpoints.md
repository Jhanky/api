# Endpoints de Integración con Siigo

## 📋 Descripción General

La integración con Siigo permite consultar productos, facturas y clientes directamente desde la API de Siigo. El sistema maneja automáticamente la autenticación, renovación de tokens y todas las operaciones de forma transparente.

### 🎯 Características Principales
- **Autenticación Automática**: Manejo automático de tokens de acceso
- **Renovación Automática**: Tokens se renuevan cada 24 horas automáticamente
- **Cache Inteligente**: Los tokens se almacenan en caché con expiración precisa
- **Middleware de Verificación**: Verificación automática antes de cada petición
- **Tareas Programadas**: Renovación diaria a las 2:00 AM y verificación cada 6 horas
- **Manejo de Errores**: Gestión robusta de errores comunes de la API
- **Validación de Datos**: Validación completa de parámetros de entrada
- **Logging Detallado**: Registro completo de todas las operaciones

### 🔄 Flujo de Autenticación Automatizado
1. **Paso 1**: Obtener token de acceso usando credenciales (automático)
2. **Paso 2**: Almacenar token en caché con expiración de 24 horas
3. **Paso 3**: Verificar token antes de cada petición
4. **Paso 4**: Renovar automáticamente cuando está próximo a expirar
5. **Paso 5**: Renovación programada diaria a las 2:00 AM

## 🌐 Configuración de API

### Base URL
```
/api/siigo
```

### Autenticación
**🔐 Requiere autenticación** - Todos los endpoints requieren token Bearer válido.

### Variables de Entorno Requeridas
```env
SIIGO_BASE_URL=https://api.siigo.com
SIIGO_USERNAME=tu_usuario_api@correo.com
SIIGO_ACCESS_KEY=tu_access_key
SIIGO_PARTNER_ID=enterprise
```

## 🚀 Endpoints Disponibles

### 1. 🔍 Probar Conexión

**GET** `/api/siigo/test-connection`

Verifica la conectividad con la API de Siigo y valida las credenciales. Este endpoint no requiere middleware de verificación de token.

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Conexión exitosa con Siigo",
    "data": {
        "success": true,
        "message": "Conexión exitosa con Siigo",
        "has_token": true,
        "timestamp": "2025-01-09T10:30:00.000000Z"
    }
}
```

### 2. ℹ️ Información de la API

**GET** `/api/siigo/info`

Obtiene información general sobre la configuración de la API de Siigo.

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Información de la API de Siigo",
    "data": {
        "base_url": "https://api.siigo.com",
        "partner_id": "enterprise",
        "endpoints": {
            "products": "/api/siigo/products",
            "invoices": "/api/siigo/invoices",
            "customers": "/api/siigo/customers",
            "test_connection": "/api/siigo/test-connection",
            "token_info": "/api/siigo/token-info",
            "refresh_token": "/api/siigo/refresh-token"
        },
        "authentication": {
            "type": "Bearer Token",
            "auto_refresh": true,
            "cache_duration": "24 horas (con 1 hora de margen)",
            "scheduled_refresh": "Cada 24 horas a las 2:00 AM"
        },
        "rate_limits": {
            "note": "Sujeto a los límites de la API de Siigo"
        }
    }
}
```

### 3. 🔑 Gestión de Tokens

#### 3.1. Información del Token

**GET** `/api/siigo/token-info`

Obtiene información detallada del token actual, incluyendo estado de expiración y tiempo restante.

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Información del token obtenida exitosamente",
    "data": {
        "has_token": true,
        "token_length": 64,
        "expires_at": "2025-01-10T02:00:00.000000Z",
        "expires_in_minutes": 1430,
        "is_expired": false,
        "needs_refresh": false
    }
}
```

#### 3.2. Forzar Renovación del Token

**POST** `/api/siigo/refresh-token`

Fuerza la renovación del token de Siigo, incluso si el actual es válido.

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Token renovado exitosamente",
    "data": {
        "success": true,
        "message": "Token renovado exitosamente",
        "has_token": true,
        "timestamp": "2025-01-09T10:30:00.000000Z"
    }
}
```

#### 3.3. Limpiar Token

**DELETE** `/api/siigo/clear-token`

Elimina el token actual del caché, forzando la obtención de uno nuevo en la siguiente petición.

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Token eliminado exitosamente",
    "data": {
        "cleared": true,
        "timestamp": "2025-01-09T10:30:00.000000Z"
    }
}
```

### 4. 📦 Productos

**⚠️ Nota**: Los endpoints de productos requieren middleware de verificación de token automático.

#### 4.1. Obtener Lista de Productos

**GET** `/api/siigo/products`

Obtiene una lista paginada de productos de Siigo.

#### Parámetros de Query (opcionales):
- `page` (integer): Número de página (default: 1)
- `page_size` (integer): Tamaño de página (default: 50, máximo: 100)
- `name` (string): Filtrar por nombre del producto
- `code` (string): Filtrar por código del producto

#### Ejemplo de Request:
```bash
GET /api/siigo/products?page=1&page_size=20&name=panel
```

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Productos obtenidos exitosamente",
    "data": {
        "results": [
            {
                "id": "12345678-1234-1234-1234-123456789012",
                "code": "PANEL-001",
                "name": "Panel Solar 400W",
                "description": "Panel solar monocristalino de 400W",
                "price": 850000.00,
                "cost": 650000.00,
                "active": true,
                "created": "2024-01-15T10:30:00Z"
            }
        ],
        "pagination": {
            "page": 1,
            "page_size": 20,
            "total_results": 150,
            "total_pages": 8
        }
    }
}
```

#### 4.2. Obtener Producto Específico

**GET** `/api/siigo/products/{productId}`

Obtiene un producto específico por su ID.

#### Ejemplo de Request:
```bash
GET /api/siigo/products/12345678-1234-1234-1234-123456789012
```

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Producto obtenido exitosamente",
    "data": {
        "id": "12345678-1234-1234-1234-123456789012",
        "code": "PANEL-001",
        "name": "Panel Solar 400W",
        "description": "Panel solar monocristalino de 400W",
        "price": 850000.00,
        "cost": 650000.00,
        "active": true,
        "created": "2024-01-15T10:30:00Z",
        "updated": "2024-01-20T14:45:00Z"
    }
}
```

### 5. 🧾 Facturas

**⚠️ Nota**: Los endpoints de facturas requieren middleware de verificación de token automático.

#### 5.1. Obtener Lista de Facturas

**GET** `/api/siigo/invoices`

Obtiene una lista paginada de facturas de Siigo.

#### Parámetros de Query (opcionales):
- `page` (integer): Número de página (default: 1)
- `page_size` (integer): Tamaño de página (default: 50, máximo: 100)
- `created_start` (date): Fecha de inicio para filtrar (formato: YYYY-MM-DD)
- `created_end` (date): Fecha de fin para filtrar (formato: YYYY-MM-DD)
- `document_id` (string): Filtrar por número de documento

#### Ejemplo de Request:
```bash
GET /api/siigo/invoices?created_start=2024-01-01&created_end=2024-01-31&page=1
```

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Facturas obtenidas exitosamente",
    "data": {
        "results": [
            {
                "id": "87654321-4321-4321-4321-210987654321",
                "document_id": "FAC-001-2024",
                "number": "FAC-001-2024",
                "date": "2024-01-15",
                "due_date": "2024-02-15",
                "customer": {
                    "id": "11111111-1111-1111-1111-111111111111",
                    "name": "Cliente Ejemplo S.A.S.",
                    "document": "900123456-1"
                },
                "total": 1500000.00,
                "balance": 0.00,
                "status": "paid",
                "created": "2024-01-15T10:30:00Z"
            }
        ],
        "pagination": {
            "page": 1,
            "page_size": 50,
            "total_results": 25,
            "total_pages": 1
        }
    }
}
```

#### 5.2. Obtener Factura Específica

**GET** `/api/siigo/invoices/{invoiceId}`

Obtiene una factura específica por su ID.

#### Ejemplo de Request:
```bash
GET /api/siigo/invoices/87654321-4321-4321-4321-210987654321
```

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Factura obtenida exitosamente",
    "data": {
        "id": "87654321-4321-4321-4321-210987654321",
        "document_id": "FAC-001-2024",
        "number": "FAC-001-2024",
        "date": "2024-01-15",
        "due_date": "2024-02-15",
        "customer": {
            "id": "11111111-1111-1111-1111-111111111111",
            "name": "Cliente Ejemplo S.A.S.",
            "document": "900123456-1",
            "email": "cliente@ejemplo.com",
            "phone": "3001234567"
        },
        "items": [
            {
                "product": {
                    "id": "12345678-1234-1234-1234-123456789012",
                    "code": "PANEL-001",
                    "name": "Panel Solar 400W"
                },
                "quantity": 10,
                "unit_price": 850000.00,
                "total": 8500000.00
            }
        ],
        "subtotal": 8500000.00,
        "tax": 1615000.00,
        "total": 1500000.00,
        "balance": 0.00,
        "status": "paid",
        "payment_method": "transfer",
        "created": "2024-01-15T10:30:00Z",
        "updated": "2024-01-20T14:45:00Z"
    }
}
```

### 6. 👥 Clientes

**⚠️ Nota**: Los endpoints de clientes requieren middleware de verificación de token automático.

#### 6.1. Obtener Lista de Clientes

**GET** `/api/siigo/customers`

Obtiene una lista paginada de clientes de Siigo.

#### Parámetros de Query (opcionales):
- `page` (integer): Número de página (default: 1)
- `page_size` (integer): Tamaño de página (default: 50, máximo: 100)
- `name` (string): Filtrar por nombre del cliente
- `document` (string): Filtrar por número de documento

#### Ejemplo de Request:
```bash
GET /api/siigo/customers?name=Cliente&page=1&page_size=10
```

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Clientes obtenidos exitosamente",
    "data": {
        "results": [
            {
                "id": "11111111-1111-1111-1111-111111111111",
                "name": "Cliente Ejemplo S.A.S.",
                "document": "900123456-1",
                "email": "cliente@ejemplo.com",
                "phone": "3001234567",
                "address": "Calle 123 #45-67, Bogotá",
                "active": true,
                "created": "2024-01-10T08:00:00Z"
            }
        ],
        "pagination": {
            "page": 1,
            "page_size": 10,
            "total_results": 5,
            "total_pages": 1
        }
    }
}
```

#### 6.2. Obtener Cliente Específico

**GET** `/api/siigo/customers/{customerId}`

Obtiene un cliente específico por su ID.

#### Ejemplo de Request:
```bash
GET /api/siigo/customers/11111111-1111-1111-1111-111111111111
```

#### Ejemplo de Response:
```json
{
    "success": true,
    "message": "Cliente obtenido exitosamente",
    "data": {
        "id": "11111111-1111-1111-1111-111111111111",
        "name": "Cliente Ejemplo S.A.S.",
        "document": "900123456-1",
        "document_type": "NIT",
        "email": "cliente@ejemplo.com",
        "phone": "3001234567",
        "address": {
            "street": "Calle 123 #45-67",
            "city": "Bogotá",
            "department": "Cundinamarca",
            "country": "Colombia",
            "postal_code": "110111"
        },
        "active": true,
        "created": "2024-01-10T08:00:00Z",
        "updated": "2024-01-15T12:30:00Z"
    }
}
```

## 🛠️ Comandos de Terminal

### Comandos Artisan Disponibles

#### 1. Probar Conexión
```bash
php artisan siigo:refresh-token --test
```
**Descripción**: Prueba la conexión con Siigo sin renovar el token.

#### 2. Renovar Token Manualmente
```bash
php artisan siigo:refresh-token
```
**Descripción**: Renueva el token si es necesario o si está próximo a expirar.

#### 3. Forzar Renovación
```bash
php artisan siigo:refresh-token --force
```
**Descripción**: Fuerza la renovación del token incluso si el actual es válido.

### Tareas Programadas

El sistema incluye tareas programadas automáticas:

#### Renovación Diaria
- **Horario**: Cada día a las 2:00 AM
- **Comando**: `php artisan siigo:refresh-token`
- **Log**: `storage/logs/siigo-token-refresh.log`

#### Verificación Preventiva
- **Horario**: Cada 6 horas
- **Comando**: `php artisan siigo:refresh-token --test`
- **Propósito**: Detectar problemas antes de que afecten las peticiones

### Configuración del Cron Job

Para que las tareas programadas funcionen, configura el cron job en tu servidor:

```bash
# Agregar esta línea al crontab
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## ⚠️ Códigos de Error

### Errores Comunes:

- **400**: Parámetros de query inválidos o ID requerido faltante
- **401**: Token de autenticación inválido o expirado
- **422**: Datos de validación incorrectos
- **500**: Error interno del servidor o error de conexión con Siigo
- **503**: Servicio no disponible (problemas de conexión con Siigo)

### Ejemplos de Errores:

#### Error de Validación:
```json
{
    "success": false,
    "message": "Parámetros de validación incorrectos",
    "errors": {
        "page": ["El campo page debe ser un número entero."],
        "page_size": ["El campo page_size no puede ser mayor que 100."]
    }
}
```

#### Error de Conexión con Siigo:
```json
{
    "success": false,
    "message": "Error al obtener productos de Siigo",
    "error": "Error al autenticar con Siigo: Invalid credentials"
}
```

#### Error de Token Expirado:
```json
{
    "success": false,
    "message": "Error al obtener productos de Siigo",
    "error": "Token de Siigo expirado. Intenta nuevamente. (Status: 401)"
}
```

## 📝 Notas Importantes

1. **Autenticación Automática**: El sistema maneja automáticamente la obtención y renovación de tokens
2. **Cache de Tokens**: Los tokens se almacenan en caché por 24 horas (con 1 hora de margen)
3. **Middleware Automático**: Verificación y renovación automática antes de cada petición
4. **Tareas Programadas**: Renovación diaria a las 2:00 AM y verificación cada 6 horas
5. **Rate Limiting**: Sujeto a los límites de la API de Siigo
6. **Validación Robusta**: Todos los parámetros se validan antes de enviar a Siigo
7. **Logging Completo**: Todas las operaciones se registran en los logs de Laravel
8. **Manejo de Errores**: Gestión inteligente de errores comunes (401, 400, 503, etc.)
9. **Paginación**: Todos los endpoints de lista soportan paginación
10. **Filtros**: Múltiples opciones de filtrado disponibles
11. **Renovación Transparente**: Los usuarios no notan las renovaciones de tokens
12. **Alta Disponibilidad**: El sistema funciona sin interrupciones

## 📊 Monitoreo y Logs

### Logs del Sistema

#### Logs Generales
- **Archivo**: `storage/logs/laravel.log`
- **Contenido**: Todas las operaciones de Siigo, errores y renovaciones
- **Rotación**: Automática por Laravel

#### Logs de Renovación de Tokens
- **Archivo**: `storage/logs/siigo-token-refresh.log`
- **Contenido**: Solo operaciones de renovación programada
- **Formato**: Timestamp + resultado de la operación

### Comandos de Monitoreo

#### Ver logs en tiempo real:
```bash
# Logs generales
tail -f storage/logs/laravel.log

# Logs específicos de tokens
tail -f storage/logs/siigo-token-refresh.log

# Filtrar solo logs de Siigo
tail -f storage/logs/laravel.log | grep -i siigo
```

#### Verificar estado del sistema:
```bash
# Información del token
php artisan tinker
```
```php
// En tinker
$siigoService = new App\Services\SiigoService();
$tokenInfo = $siigoService->getTokenInfo();
print_r($tokenInfo);
```

### Métricas de Rendimiento

#### Información del Token:
```bash
GET /api/siigo/token-info
```
**Métricas incluidas**:
- Estado del token (válido/expirado)
- Tiempo restante hasta expiración
- Longitud del token
- Necesidad de renovación

#### Logs de Operaciones:
- Tiempo de respuesta de Siigo
- Frecuencia de renovaciones
- Errores de conexión
- Uso de caché

## 🔧 Configuración Requerida

### Variables de Entorno (.env):
```env
# Configuración de Siigo
SIIGO_BASE_URL=https://api.siigo.com
SIIGO_USERNAME=tu_usuario_api@correo.com
SIIGO_ACCESS_KEY=tu_access_key
SIIGO_PARTNER_ID=enterprise

# Configuración de Cache (opcional)
CACHE_DRIVER=file
# CACHE_DRIVER=redis
# CACHE_DRIVER=database
```

### Credenciales de Siigo:
1. Obtener usuario y access_key desde el portal de Siigo
2. Configurar las variables de entorno
3. Probar la conexión con `/api/siigo/test-connection`

### Configuración del Cron Job:
```bash
# Agregar al crontab del servidor
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## 🚀 Casos de Uso

### Gestión de Tokens:
```bash
# Verificar estado del token
GET /api/siigo/token-info

# Forzar renovación manual
POST /api/siigo/refresh-token

# Limpiar token (forzar nueva autenticación)
DELETE /api/siigo/clear-token

# Probar conexión
GET /api/siigo/test-connection
```

### Sincronización de Productos:
```bash
# Obtener todos los productos de Siigo
GET /api/siigo/products?page=1&page_size=100

# Buscar productos específicos
GET /api/siigo/products?name=panel&code=PANEL

# Obtener producto específico
GET /api/siigo/products/12345678-1234-1234-1234-123456789012
```

### Consulta de Facturas:
```bash
# Obtener facturas del mes actual
GET /api/siigo/invoices?created_start=2024-01-01&created_end=2024-01-31

# Buscar factura específica
GET /api/siigo/invoices/87654321-4321-4321-4321-210987654321

# Filtrar por número de documento
GET /api/siigo/invoices?document_id=FAC-001-2024
```

### Gestión de Clientes:
```bash
# Obtener lista de clientes
GET /api/siigo/customers?page=1&page_size=50

# Buscar cliente por documento
GET /api/siigo/customers?document=900123456-1

# Buscar cliente por nombre
GET /api/siigo/customers?name=Cliente

# Obtener cliente específico
GET /api/siigo/customers/11111111-1111-1111-1111-111111111111
```

### Comandos de Terminal:
```bash
# Probar conexión
php artisan siigo:refresh-token --test

# Renovar token manualmente
php artisan siigo:refresh-token

# Forzar renovación
php artisan siigo:refresh-token --force
```

## 🔄 Integración con Otros Módulos

### Con Productos Locales:
- Sincronizar catálogo de productos
- Actualizar precios automáticamente
- Validar disponibilidad

### Con Facturas Locales:
- Comparar facturas entre sistemas
- Sincronizar estados de pago
- Validar totales y montos

### Con Clientes:
- Sincronizar base de datos de clientes
- Validar información de contacto
- Actualizar datos automáticamente
