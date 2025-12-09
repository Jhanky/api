# Endpoints de Estados de Cotizaciones

## Descripción General
API para gestionar los estados de las cotizaciones en el sistema de energía solar. Los estados definen el flujo de trabajo de las cotizaciones desde su creación hasta su finalización.

## Estados Disponibles
- **Pendiente**: Cotización en estado inicial
- **Diseñada**: Cotización con diseño técnico completo
- **Enviada**: Cotización enviada al cliente
- **Negociaciones**: En proceso de negociación
- **Contratada**: Cotización aceptada y convertida en contrato
- **Descartada**: Cotización rechazada o descartada

---

## 1. Obtener Todos los Estados

### GET `/api/quotation-statuses`

Obtiene la lista completa de estados de cotizaciones disponibles.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": [
        {
            "status_id": 1,
            "name": "Pendiente",
            "description": "Cotización en estado inicial, pendiente de diseño y desarrollo",
            "color": "#F59E0B",
            "is_active": true,
            "created_at": "2025-09-01T14:30:18.000000Z",
            "updated_at": "2025-09-01T14:30:18.000000Z"
        },
        {
            "status_id": 2,
            "name": "Diseñada",
            "description": "Cotización con diseño técnico completo y especificaciones definidas",
            "color": "#3B82F6",
            "is_active": true,
            "created_at": "2025-09-01T14:30:18.000000Z",
            "updated_at": "2025-09-01T14:30:18.000000Z"
        }
    ],
    "message": "Estados de cotizaciones obtenidos exitosamente"
}
```

---

## 2. Obtener Estado por ID

### GET `/api/quotation-statuses/{id}`

Obtiene un estado específico de cotización por su ID.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Parámetros de URL:**
- `id` (integer, requerido): ID del estado de cotización

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "status_id": 1,
        "name": "Pendiente",
        "description": "Cotización en estado inicial, pendiente de diseño y desarrollo",
        "color": "#F59E0B",
        "is_active": true,
        "created_at": "2025-09-01T14:30:18.000000Z",
        "updated_at": "2025-09-01T14:30:18.000000Z"
    },
    "message": "Estado de cotización obtenido exitosamente"
}
```

**Respuesta de Error (404):**
```json
{
    "success": false,
    "message": "Estado de cotización no encontrado"
}
```

---

## 3. Obtener Estados Activos

### GET `/api/quotation-statuses/active`

Obtiene solo los estados de cotizaciones que están activos.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": [
        {
            "status_id": 1,
            "name": "Pendiente",
            "description": "Cotización en estado inicial, pendiente de diseño y desarrollo",
            "color": "#F59E0B",
            "is_active": true
        },
        {
            "status_id": 2,
            "name": "Diseñada",
            "description": "Cotización con diseño técnico completo y especificaciones definidas",
            "color": "#3B82F6",
            "is_active": true
        }
    ],
    "message": "Estados activos obtenidos exitosamente"
}
```

---

## 4. Crear Nuevo Estado

### POST `/api/quotation-statuses`

Crea un nuevo estado de cotización.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
    "name": "Nuevo Estado",
    "description": "Descripción del nuevo estado",
    "color": "#FF6B6B",
    "is_active": true
}
```

**Validaciones:**
- `name` (string, requerido, máximo 50 caracteres, único)
- `description` (string, opcional)
- `color` (string, opcional, máximo 20 caracteres)
- `is_active` (boolean, opcional)

**Respuesta Exitosa (201):**
```json
{
    "success": true,
    "data": {
        "status_id": 7,
        "name": "Nuevo Estado",
        "description": "Descripción del nuevo estado",
        "color": "#FF6B6B",
        "is_active": true,
        "created_at": "2025-09-01T15:00:00.000000Z",
        "updated_at": "2025-09-01T15:00:00.000000Z"
    },
    "message": "Estado de cotización creado exitosamente"
}
```

**Respuesta de Error (422):**
```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "name": ["El nombre ya ha sido utilizado."]
    }
}
```

---

## 5. Actualizar Estado

### PUT `/api/quotation-statuses/{id}`

Actualiza un estado de cotización existente.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Parámetros de URL:**
- `id` (integer, requerido): ID del estado de cotización

**Body:**
```json
{
    "name": "Estado Actualizado",
    "description": "Descripción actualizada",
    "color": "#4ECDC4",
    "is_active": false
}
```

**Validaciones:**
- `name` (string, opcional, máximo 50 caracteres, único)
- `description` (string, opcional)
- `color` (string, opcional, máximo 20 caracteres)
- `is_active` (boolean, opcional)

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "status_id": 1,
        "name": "Estado Actualizado",
        "description": "Descripción actualizada",
        "color": "#4ECDC4",
        "is_active": false,
        "created_at": "2025-09-01T14:30:18.000000Z",
        "updated_at": "2025-09-01T15:30:00.000000Z"
    },
    "message": "Estado de cotización actualizado exitosamente"
}
```

**Respuesta de Error (404):**
```json
{
    "success": false,
    "message": "Estado de cotización no encontrado"
}
```

---

## 6. Eliminar Estado

### DELETE `/api/quotation-statuses/{id}`

Elimina un estado de cotización (solo si no está siendo utilizado).

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Parámetros de URL:**
- `id` (integer, requerido): ID del estado de cotización

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "message": "Estado de cotización eliminado exitosamente"
}
```

**Respuesta de Error (400):**
```json
{
    "success": false,
    "message": "No se puede eliminar el estado porque está siendo utilizado por cotizaciones existentes"
}
```

**Respuesta de Error (404):**
```json
{
    "success": false,
    "message": "Estado de cotización no encontrado"
}
```

---

## 7. Obtener Estadísticas de Estados

### GET `/api/quotation-statuses/statistics`

Obtiene estadísticas de uso de los estados de cotizaciones.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "total_statuses": 6,
        "active_statuses": 6,
        "usage_count": {
            "Pendiente": 15,
            "Diseñada": 8,
            "Enviada": 12,
            "Negociaciones": 5,
            "Contratada": 3,
            "Descartada": 2
        }
    },
    "message": "Estadísticas obtenidas exitosamente"
}
```

---

## 8. Buscar Estados

### GET `/api/quotation-statuses/search`

Busca estados de cotizaciones por nombre o descripción.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Parámetros de Query:**
- `q` (string, opcional): Término de búsqueda
- `is_active` (boolean, opcional): Filtrar por estado activo/inactivo

**Ejemplo de URL:**
```
GET /api/quotation-statuses/search?q=pendiente&is_active=true
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": [
        {
            "status_id": 1,
            "name": "Pendiente",
            "description": "Cotización en estado inicial, pendiente de diseño y desarrollo",
            "color": "#F59E0B",
            "is_active": true
        }
    ],
    "message": "Búsqueda completada exitosamente"
}
```

---

## Códigos de Error Comunes

### 401 Unauthorized
```json
{
    "success": false,
    "message": "No autorizado"
}
```

### 403 Forbidden
```json
{
    "success": false,
    "message": "Acceso denegado"
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Error interno del servidor"
}
```

---

## Notas Importantes

1. **Autenticación**: Todos los endpoints requieren autenticación mediante token Bearer.
2. **Permisos**: Algunos endpoints pueden requerir permisos específicos según el rol del usuario.
3. **Validación**: Los nombres de estados deben ser únicos en el sistema.
4. **Integridad**: No se pueden eliminar estados que estén siendo utilizados por cotizaciones existentes.
5. **Colores**: Los colores se almacenan en formato hexadecimal (#RRGGBB).
6. **Estados por Defecto**: Los estados básicos (Pendiente, Diseñada, Enviada, etc.) están predefinidos y no se pueden eliminar.
