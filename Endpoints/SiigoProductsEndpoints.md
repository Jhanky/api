# Endpoint de Productos de Siigo

## 📋 Descripción General

Este endpoint permite obtener una lista paginada de productos desde la API de Siigo con múltiples opciones de filtrado y búsqueda. El sistema maneja automáticamente la autenticación y renovación de tokens.

## 🌐 Configuración

### Base URL
```
/api/siigo/products
```

### Autenticación
**🔐 Requiere autenticación** - Token Bearer válido de Laravel

### Headers Requeridos
```
Authorization: Bearer tu_token_de_autenticacion_laravel
Content-Type: application/json
```

## 🚀 Endpoint Principal

### **GET** `/api/siigo/products`

Obtiene una lista paginada de productos de Siigo con opciones de filtrado y búsqueda.

## 📝 Parámetros de Query

### **Parámetros de Paginación**

| Parámetro | Tipo | Requerido | Default | Descripción |
|-----------|------|-----------|---------|-------------|
| `page` | integer | No | 1 | Número de página a obtener |
| `page_size` | integer | No | 50 | Cantidad de productos por página (máximo: 100) |

### **Parámetros de Filtrado**

| Parámetro | Tipo | Requerido | Default | Descripción |
|-----------|------|-----------|---------|-------------|
| `name` | string | No | - | Filtrar por nombre del producto (búsqueda parcial) |
| `code` | string | No | - | Filtrar por código del producto (búsqueda exacta) |

## 🔍 Ejemplos de Uso

### **1. Obtener Primera Página (Básico)**
```bash
GET /api/siigo/products
```

**Response:**
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
            },
            {
                "id": "87654321-4321-4321-4321-210987654321",
                "code": "INV-001",
                "name": "Inversor 5kW",
                "description": "Inversor string de 5kW para sistemas On-grid",
                "price": 2500000.00,
                "cost": 2000000.00,
                "active": true,
                "created": "2024-01-16T14:20:00Z"
            }
        ],
        "pagination": {
            "page": 1,
            "page_size": 50,
            "total_results": 150,
            "total_pages": 3
        }
    }
}
```

### **2. Paginación Personalizada**
```bash
GET /api/siigo/products?page=2&page_size=10
```

**Parámetros:**
- `page=2`: Segunda página
- `page_size=10`: 10 productos por página

**Response:**
```json
{
    "success": true,
    "message": "Productos obtenidos exitosamente",
    "data": {
        "results": [
            // 10 productos de la página 2
        ],
        "pagination": {
            "page": 2,
            "page_size": 10,
            "total_results": 150,
            "total_pages": 15
        }
    }
}
```

### **3. Búsqueda por Nombre**
```bash
GET /api/siigo/products?name=panel
```

**Parámetros:**
- `name=panel`: Busca productos que contengan "panel" en el nombre

**Response:**
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
            },
            {
                "id": "11111111-1111-1111-1111-111111111111",
                "code": "PANEL-002",
                "name": "Panel Solar 500W",
                "description": "Panel solar policristalino de 500W",
                "price": 950000.00,
                "cost": 750000.00,
                "active": true,
                "created": "2024-01-17T09:15:00Z"
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

### **4. Búsqueda por Código**
```bash
GET /api/siigo/products?code=PANEL-001
```

**Parámetros:**
- `code=PANEL-001`: Busca productos con código exacto "PANEL-001"

**Response:**
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
            "page_size": 50,
            "total_results": 1,
            "total_pages": 1
        }
    }
}
```

### **5. Combinación de Filtros**
```bash
GET /api/siigo/products?name=solar&page=1&page_size=5
```

**Parámetros:**
- `name=solar`: Busca productos que contengan "solar"
- `page=1`: Primera página
- `page_size=5`: 5 productos por página

### **6. Búsqueda Avanzada**
```bash
GET /api/siigo/products?name=inversor&code=INV&page=1&page_size=20
```

**Parámetros:**
- `name=inversor`: Busca productos que contengan "inversor"
- `code=INV`: Busca productos que contengan "INV" en el código
- `page=1`: Primera página
- `page_size=20`: 20 productos por página

## 📊 Estructura de Respuesta

### **Estructura General**
```json
{
    "success": boolean,
    "message": string,
    "data": {
        "results": array,
        "pagination": object
    }
}
```

### **Estructura de Producto**
```json
{
    "id": "string (UUID)",
    "code": "string",
    "name": "string",
    "description": "string",
    "price": "number (decimal)",
    "cost": "number (decimal)",
    "active": "boolean",
    "created": "string (ISO 8601)",
    "updated": "string (ISO 8601)" // Opcional
}
```

### **Estructura de Paginación**
```json
{
    "page": "integer",
    "page_size": "integer",
    "total_results": "integer",
    "total_pages": "integer"
}
```

## ⚠️ Códigos de Error

### **400 - Bad Request**
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

### **401 - Unauthorized**
```json
{
    "success": false,
    "message": "Token de autenticación inválido o expirado"
}
```

### **500 - Internal Server Error**
```json
{
    "success": false,
    "message": "Error al obtener productos de Siigo",
    "error": "Error de conexión con Siigo"
}
```

### **503 - Service Unavailable**
```json
{
    "success": false,
    "message": "No se puede conectar con Siigo. Verifica las credenciales.",
    "error": "Siigo connection failed"
}
```

## 🧪 Ejemplos de Pruebas

### **Con cURL**
```bash
# Básico
curl -X GET "http://localhost:8000/api/siigo/products" \
  -H "Authorization: Bearer tu_token_laravel" \
  -H "Content-Type: application/json"

# Con paginación
curl -X GET "http://localhost:8000/api/siigo/products?page=1&page_size=10" \
  -H "Authorization: Bearer tu_token_laravel" \
  -H "Content-Type: application/json"

# Con filtro por nombre
curl -X GET "http://localhost:8000/api/siigo/products?name=panel" \
  -H "Authorization: Bearer tu_token_laravel" \
  -H "Content-Type: application/json"

# Con filtro por código
curl -X GET "http://localhost:8000/api/siigo/products?code=PANEL-001" \
  -H "Authorization: Bearer tu_token_laravel" \
  -H "Content-Type: application/json"
```

### **Con Postman**
```
Método: GET
URL: http://localhost:8000/api/siigo/products
Headers:
  Authorization: Bearer tu_token_laravel
  Content-Type: application/json

Query Parameters (opcionales):
  page: 1
  page_size: 10
  name: panel
  code: PANEL-001
```

## 📝 Casos de Uso Comunes

### **1. Sincronización de Catálogo**
```bash
# Obtener todos los productos (paginado)
GET /api/siigo/products?page=1&page_size=100
```

### **2. Búsqueda de Productos Específicos**
```bash
# Buscar paneles solares
GET /api/siigo/products?name=panel

# Buscar inversores
GET /api/siigo/products?name=inversor

# Buscar baterías
GET /api/siigo/products?name=bateria
```

### **3. Validación de Códigos**
```bash
# Verificar si existe un producto específico
GET /api/siigo/products?code=PANEL-001
```

### **4. Navegación por Páginas**
```bash
# Primera página
GET /api/siigo/products?page=1&page_size=20

# Segunda página
GET /api/siigo/products?page=2&page_size=20

# Tercera página
GET /api/siigo/products?page=3&page_size=20
```

## 🔧 Configuración Avanzada

### **Límites de Paginación**
- **Mínimo page**: 1
- **Máximo page_size**: 100
- **Default page_size**: 50

### **Filtros de Búsqueda**
- **name**: Búsqueda parcial (case-insensitive)
- **code**: Búsqueda exacta (case-sensitive)

### **Ordenamiento**
- Los productos se ordenan por fecha de creación (más recientes primero)
- El ordenamiento es manejado por la API de Siigo

## 📊 Monitoreo y Logs

### **Logs de Operaciones**
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log | grep -i siigo

# Ver logs específicos de productos
tail -f storage/logs/laravel.log | grep -i "productos"
```

### **Métricas de Rendimiento**
- Tiempo de respuesta de Siigo
- Número de productos obtenidos
- Frecuencia de búsquedas
- Errores de conexión

## ⚡ Optimizaciones

### **Cache de Tokens**
- Los tokens se almacenan en caché por 24 horas
- Renovación automática antes de expirar
- Verificación automática antes de cada petición

### **Paginación Eficiente**
- Usar `page_size` apropiado (recomendado: 20-50)
- Evitar páginas muy grandes para mejor rendimiento
- Implementar cache local si es necesario

### **Filtros Optimizados**
- Usar filtros específicos para reducir resultados
- Combinar filtros para búsquedas más precisas
- Evitar búsquedas muy amplias sin filtros

## 🚀 Próximos Pasos

1. **Probar el endpoint** con diferentes parámetros
2. **Implementar cache local** si es necesario
3. **Configurar monitoreo** de rendimiento
4. **Integrar con sistema de productos** local
5. **Implementar sincronización** automática
