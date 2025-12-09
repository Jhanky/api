# 🏢 **API Endpoints - Sistema de Centros de Costo**

## **🔗 Base URL**
```
http://localhost:8000/api
```

---

## **🏢 CENTROS DE COSTO**

### 📊 **Listar Centros de Costo**
**GET** `/api/cost-centers`

**Descripción:** Obtiene una lista paginada de todos los centros de costo con filtros opcionales.

**Parámetros de consulta:**
- `page`: Número de página (por defecto: 1)
- `per_page`: Elementos por página (por defecto: 15, máximo: 100)
- `search`: Búsqueda en nombre del centro de costo
- `sort_by`: Campo para ordenar (`cost_center_name`, `created_at`)
- `sort_order`: Orden (`asc` o `desc`)

**Ejemplo de petición:**
```
GET /api/cost-centers?page=1&per_page=10&search=residencial&sort_by=cost_center_name&sort_order=asc
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Centros de costo obtenidos exitosamente",
  "data": {
    "current_page": 1,
    "data": [
      {
        "cost_center_id": 1,
        "cost_center_name": "Proyectos Residenciales",
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "invoices_count": 4,
        "total_invoiced": "9000000.00"
      },
      {
        "cost_center_id": 2,
        "cost_center_name": "Proyectos Comerciales",
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "invoices_count": 2,
        "total_invoiced": "5000000.00"
      }
    ],
    "first_page_url": "http://localhost:8000/api/cost-centers?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/cost-centers?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://localhost:8000/api/cost-centers?page=1",
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
    "path": "http://localhost:8000/api/cost-centers",
    "per_page": 15,
    "prev_page_url": null,
    "to": 3,
    "total": 3
  }
}
```

---

### 🔍 **Obtener Centro de Costo Específico**
**GET** `/api/cost-centers/{id}`

**Descripción:** Obtiene los detalles de un centro de costo específico.

**Parámetros de ruta:**
- `id`: ID del centro de costo

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Centro de costo obtenido exitosamente",
  "data": {
    "cost_center_id": 1,
    "cost_center_name": "Proyectos Residenciales",
    "created_at": "2025-09-10T13:41:10.000000Z",
    "updated_at": "2025-09-10T13:41:10.000000Z",
    "invoices_count": 4,
    "total_invoiced": "9000000.00",
    "invoices": [
      {
        "invoice_id": 1,
        "invoice_number": "FAC-001-2024",
        "invoice_date": "2025-08-11",
        "due_date": "2025-08-26",
        "total_amount": "1500000.00",
        "description": "Compra de paneles solares para proyecto residencial",
        "status": "PAGADA",
        "provider_id": 1,
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "provider": {
          "provider_id": 1,
          "provider_name": "Energía Solar S.A.S",
          "NIT": "900123456-7"
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
  "message": "Centro de costo no encontrado"
}
```

---

### ➕ **Crear Centro de Costo**
**POST** `/api/cost-centers`

**Descripción:** Crea un nuevo centro de costo.

**Cuerpo de la petición:**
```json
{
  "cost_center_name": "Proyectos Industriales"
}
```

**Validaciones:**
- `cost_center_name`: Requerido, string, máximo 255 caracteres, único

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Centro de costo creado exitosamente",
  "data": {
    "cost_center_id": 4,
    "cost_center_name": "Proyectos Industriales",
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
    "cost_center_name": ["El nombre del centro de costo es requerido"]
  }
}
```

---

### ✏️ **Actualizar Centro de Costo**
**PUT** `/api/cost-centers/{id}`

**Descripción:** Actualiza un centro de costo existente.

**Cuerpo de la petición:**
```json
{
  "cost_center_name": "Proyectos Residenciales - Actualizado"
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Centro de costo actualizado exitosamente",
  "data": {
    "cost_center_id": 1,
    "cost_center_name": "Proyectos Residenciales - Actualizado",
    "created_at": "2025-09-10T13:41:10.000000Z",
    "updated_at": "2025-09-10T14:00:00.000000Z",
    "invoices_count": 4,
    "total_invoiced": "9000000.00"
  }
}
```

---

### 🗑️ **Eliminar Centro de Costo**
**DELETE** `/api/cost-centers/{id}`

**Descripción:** Elimina un centro de costo existente.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Centro de costo eliminado exitosamente"
}
```

**Respuesta de error (404):**
```json
{
  "success": false,
  "message": "Centro de costo no encontrado"
}
```

**Respuesta de error (409):**
```json
{
  "success": false,
  "message": "No se puede eliminar el centro de costo porque tiene facturas asociadas"
}
```

---

### 📊 **Estadísticas de Centros de Costo**
**GET** `/api/cost-centers/statistics`

**Descripción:** Obtiene estadísticas generales de los centros de costo.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Estadísticas obtenidas exitosamente",
  "data": {
    "total_cost_centers": 3,
    "cost_centers_with_invoices": 3,
    "cost_centers_without_invoices": 0,
    "total_invoiced": "18750000.00",
    "average_invoiced_per_cost_center": "6250000.00",
    "top_cost_centers": [
      {
        "cost_center_id": 1,
        "cost_center_name": "Proyectos Residenciales",
        "invoices_count": 4,
        "total_invoiced": "9000000.00",
        "percentage": "48.00"
      },
      {
        "cost_center_id": 2,
        "cost_center_name": "Proyectos Comerciales",
        "invoices_count": 2,
        "total_invoiced": "5000000.00",
        "percentage": "26.67"
      }
    ],
    "cost_centers_by_invoice_count": {
      "0": 0,
      "1": 0,
      "2": 1,
      "3": 1,
      "4": 1
    }
  }
}
```

---

### 🔍 **Buscar Centros de Costo**
**GET** `/api/cost-centers/search`

**Descripción:** Busca centros de costo por nombre.

**Parámetros de consulta:**
- `q`: Término de búsqueda (requerido)
- `page`: Número de página
- `per_page`: Elementos por página

**Ejemplo de petición:**
```
GET /api/cost-centers/search?q=residencial&page=1&per_page=10
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
        "cost_center_id": 1,
        "cost_center_name": "Proyectos Residenciales",
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "invoices_count": 4,
        "total_invoiced": "9000000.00"
      }
    ],
    "first_page_url": "http://localhost:8000/api/cost-centers/search?q=residencial&page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/cost-centers/search?q=residencial&page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://localhost:8000/api/cost-centers/search?q=residencial&page=1",
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
    "path": "http://localhost:8000/api/cost-centers/search",
    "per_page": 15,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

---

### 📋 **Facturas por Centro de Costo**
**GET** `/api/cost-centers/{id}/invoices`

**Descripción:** Obtiene todas las facturas de un centro de costo específico.

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
  "message": "Facturas del centro de costo obtenidas exitosamente",
  "data": {
    "cost_center": {
      "cost_center_id": 1,
      "cost_center_name": "Proyectos Residenciales"
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
          "provider_id": 1,
          "created_at": "2025-09-10T13:41:10.000000Z",
          "updated_at": "2025-09-10T13:41:10.000000Z",
          "provider": {
            "provider_id": 1,
            "provider_name": "Energía Solar S.A.S",
            "NIT": "900123456-7"
          }
        }
      ],
      "total": 4,
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
- El nombre del centro de costo es requerido y único
- No se puede eliminar un centro de costo que tenga facturas asociadas

### **📄 Paginación:**
- Por defecto se muestran 15 elementos por página
- Máximo 100 elementos por página
- Incluye enlaces de navegación y metadatos

### **🔍 Búsqueda:**
- Busca en nombre del centro de costo
- No distingue entre mayúsculas y minúsculas
- Soporta búsqueda parcial

### **📊 Estadísticas:**
- Incluye conteos de centros de costo con y sin facturas
- Montos totales facturados por centro de costo
- Ranking de centros de costo por monto facturado
- Distribución por cantidad de facturas

### **✨ Mejoras Implementadas:**
- **Campos calculados**: Todos los endpoints ahora incluyen `invoices_count` y `total_invoiced`
- **Relaciones optimizadas**: Se cargan las facturas y proveedores cuando es necesario
- **Límite de paginación**: Máximo 100 elementos por página para evitar sobrecarga
- **Estadísticas mejoradas**: Incluye porcentajes y distribuciones más detalladas
- **Búsqueda dedicada**: Endpoint específico para búsquedas con validación
- **Facturas por centro de costo**: Endpoint para obtener facturas de un centro de costo específico

### **⚠️ Códigos de Error:**
- `400`: Solicitud incorrecta
- `404`: Recurso no encontrado
- `409`: Conflicto (centro de costo con facturas asociadas)
- `422`: Error de validación
- `500`: Error interno del servidor
