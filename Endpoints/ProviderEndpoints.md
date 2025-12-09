# 🏢 **API Endpoints - Sistema de Proveedores**

## **🔗 Base URL**
```
http://localhost:8000/api
```

---

## **🏢 PROVEEDORES**

### 📊 **Listar Proveedores**
**GET** `/api/providers`

**Descripción:** Obtiene una lista paginada de todos los proveedores con filtros opcionales.

**Parámetros de consulta:**
- `page`: Número de página (por defecto: 1)
- `per_page`: Elementos por página (por defecto: 15, máximo: 100)
- `search`: Búsqueda en nombre o NIT del proveedor
- `sort_by`: Campo para ordenar (`provider_name`, `NIT`, `created_at`)
- `sort_order`: Orden (`asc` o `desc`)

**Ejemplo de petición:**
```
GET /api/providers?page=1&per_page=10&search=energía&sort_by=provider_name&sort_order=asc
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Proveedores obtenidos exitosamente",
  "data": {
    "current_page": 1,
    "data": [
      {
        "provider_id": 1,
        "provider_name": "Energía Solar S.A.S",
        "NIT": "900123456-7",
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "invoices_count": 3,
        "total_invoiced": "7000000.00"
      },
      {
        "provider_id": 2,
        "provider_name": "Tecnología Verde Ltda",
        "NIT": "900987654-3",
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "invoices_count": 2,
        "total_invoiced": "4500000.00"
      }
    ],
    "first_page_url": "http://localhost:8000/api/providers?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/providers?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://localhost:8000/api/providers?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "next_page_url": null,
    "path": "http://localhost:8000/api/providers",
    "per_page": 15,
    "prev_page_url": null,
    "to": 5,
    "total": 5
  }
}
```

---

### 🔍 **Obtener Proveedor Específico**
**GET** `/api/providers/{id}`

**Descripción:** Obtiene los detalles de un proveedor específico.

**Parámetros de ruta:**
- `id`: ID del proveedor

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Proveedor obtenido exitosamente",
  "data": {
    "provider_id": 1,
    "provider_name": "Energía Solar S.A.S",
    "NIT": "900123456-7",
    "created_at": "2025-09-10T13:41:10.000000Z",
    "updated_at": "2025-09-10T13:41:10.000000Z",
    "invoices_count": 3,
    "total_invoiced": "7000000.00",
    "invoices": [
      {
        "invoice_id": 1,
        "invoice_number": "FAC-001-2024",
        "invoice_date": "2025-08-11",
        "due_date": "2025-08-26",
        "total_amount": "1500000.00",
        "description": "Compra de paneles solares para proyecto residencial",
        "status": "PAGADA",
        "cost_center_id": 1,
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "cost_center": {
          "cost_center_id": 1,
          "cost_center_name": "Proyectos Residenciales"
        }
      }
    ]
  }
}
```

**Respuesta de error (404):**
```json
{
  "success": false,
  "message": "Proveedor no encontrado"
}
```

---

### ➕ **Crear Proveedor**
**POST** `/api/providers`

**Descripción:** Crea un nuevo proveedor.

**Cuerpo de la petición:**
```json
{
  "provider_name": "Nuevo Proveedor S.A.S",
  "NIT": "900555666-7"
}
```

**Validaciones:**
- `provider_name`: Requerido, string, máximo 255 caracteres
- `NIT`: Requerido, string, máximo 50 caracteres, único

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Proveedor creado exitosamente",
  "data": {
    "provider_id": 6,
    "provider_name": "Nuevo Proveedor S.A.S",
    "NIT": "900555666-7",
    "created_at": "2025-09-10T14:00:00.000000Z",
    "updated_at": "2025-09-10T14:00:00.000000Z",
    "invoices_count": 0,
    "total_invoiced": "0.00"
  }
}
```

**Respuesta de error (422):**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "provider_name": ["El nombre del proveedor es requerido"],
    "NIT": ["El NIT ya está registrado"]
  }
}
```

---

### ✏️ **Actualizar Proveedor**
**PUT** `/api/providers/{id}`

**Descripción:** Actualiza un proveedor existente.

**Cuerpo de la petición:**
```json
{
  "provider_name": "Energía Solar S.A.S - Actualizado",
  "NIT": "900123456-7"
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Proveedor actualizado exitosamente",
  "data": {
    "provider_id": 1,
    "provider_name": "Energía Solar S.A.S - Actualizado",
    "NIT": "900123456-7",
    "created_at": "2025-09-10T13:41:10.000000Z",
    "updated_at": "2025-09-10T14:00:00.000000Z",
    "invoices_count": 3,
    "total_invoiced": "7000000.00"
  }
}
```

---

### 🗑️ **Eliminar Proveedor**
**DELETE** `/api/providers/{id}`

**Descripción:** Elimina un proveedor existente.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Proveedor eliminado exitosamente"
}
```

**Respuesta de error (404):**
```json
{
  "success": false,
  "message": "Proveedor no encontrado"
}
```

**Respuesta de error (409):**
```json
{
  "success": false,
  "message": "No se puede eliminar el proveedor porque tiene facturas asociadas"
}
```

---

### 📊 **Estadísticas de Proveedores**
**GET** `/api/providers/statistics`

**Descripción:** Obtiene estadísticas generales de los proveedores.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Estadísticas obtenidas exitosamente",
  "data": {
    "total_providers": 5,
    "providers_with_invoices": 4,
    "providers_without_invoices": 1,
    "total_invoiced": "18750000.00",
    "average_invoiced_per_provider": "3750000.00",
    "top_providers": [
      {
        "provider_id": 1,
        "provider_name": "Energía Solar S.A.S",
        "NIT": "900123456-7",
        "invoices_count": 3,
        "total_invoiced": "7000000.00",
        "percentage": "37.33"
      },
      {
        "provider_id": 2,
        "provider_name": "Tecnología Verde Ltda",
        "NIT": "900987654-3",
        "invoices_count": 2,
        "total_invoiced": "4500000.00",
        "percentage": "24.00"
      }
    ],
    "providers_by_invoice_count": {
      "0": 1,
      "1": 1,
      "2": 2,
      "3": 1
    }
  }
}
```

---

### 🔍 **Buscar Proveedores**
**GET** `/api/providers/search`

**Descripción:** Busca proveedores por nombre o NIT.

**Parámetros de consulta:**
- `q`: Término de búsqueda (requerido)
- `page`: Número de página
- `per_page`: Elementos por página

**Ejemplo de petición:**
```
GET /api/providers/search?q=energía&page=1&per_page=10
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Búsqueda realizada exitosamente",
  "data": {
    "current_page": 1,
    "data": [
      {
        "provider_id": 1,
        "provider_name": "Energía Solar S.A.S",
        "NIT": "900123456-7",
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "invoices_count": 3,
        "total_invoiced": "7000000.00"
      }
    ],
    "first_page_url": "http://localhost:8000/api/providers/search?q=energía&page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/providers/search?q=energía&page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://localhost:8000/api/providers/search?q=energía&page=1",
        "label": "1",
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "next_page_url": null,
    "path": "http://localhost:8000/api/providers/search",
    "per_page": 15,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

---

### 📋 **Facturas por Proveedor**
**GET** `/api/providers/{id}/invoices`

**Descripción:** Obtiene todas las facturas de un proveedor específico.

**Parámetros de consulta:**
- `page`: Número de página
- `per_page`: Elementos por página
- `status`: Filtrar por estado (`PENDIENTE` o `PAGADA`)
- `sort_by`: Campo para ordenar
- `sort_order`: Orden (`asc` o `desc`)

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Facturas del proveedor obtenidas exitosamente",
  "data": {
    "provider": {
      "provider_id": 1,
      "provider_name": "Energía Solar S.A.S",
      "NIT": "900123456-7"
    },
    "invoices": {
      "current_page": 1,
      "data": [
        {
          "invoice_id": 1,
          "invoice_number": "FAC-001-2024",
          "invoice_date": "2025-08-11",
          "due_date": "2025-08-26",
          "total_amount": "1500000.00",
          "description": "Compra de paneles solares para proyecto residencial",
          "status": "PAGADA",
          "cost_center_id": 1,
          "created_at": "2025-09-10T13:41:10.000000Z",
          "updated_at": "2025-09-10T13:41:10.000000Z",
          "cost_center": {
            "cost_center_id": 1,
            "cost_center_name": "Proyectos Residenciales"
          }
        }
      ],
      "total": 3,
      "per_page": 15,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

---

## **📝 Notas Importantes**

### **🔒 Validaciones:**
- El nombre del proveedor es requerido y único
- El NIT es requerido y único
- No se puede eliminar un proveedor que tenga facturas asociadas

### **📄 Paginación:**
- Por defecto se muestran 15 elementos por página
- Máximo 100 elementos por página
- Incluye enlaces de navegación y metadatos

### **🔍 Búsqueda:**
- Busca en nombre y NIT del proveedor
- No distingue entre mayúsculas y minúsculas
- Soporta búsqueda parcial

### **📊 Estadísticas:**
- Incluye conteos de proveedores con y sin facturas
- Montos totales facturados por proveedor
- Ranking de proveedores por monto facturado
- Distribución por cantidad de facturas

### **✨ Mejoras Implementadas:**
- **Campos calculados**: Todos los endpoints ahora incluyen `invoices_count` y `total_invoiced`
- **Relaciones optimizadas**: Se cargan las facturas y centros de costo cuando es necesario
- **Límite de paginación**: Máximo 100 elementos por página para evitar sobrecarga
- **Estadísticas mejoradas**: Incluye porcentajes y distribuciones más detalladas
- **Búsqueda dedicada**: Endpoint específico para búsquedas con validación
- **Facturas por proveedor**: Endpoint para obtener facturas de un proveedor específico

### **⚠️ Códigos de Error:**
- `400`: Solicitud incorrecta
- `404`: Recurso no encontrado
- `409`: Conflicto (proveedor con facturas asociadas)
- `422`: Error de validación
- `500`: Error interno del servidor
