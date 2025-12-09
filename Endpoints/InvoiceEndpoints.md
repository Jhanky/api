# 📄 **API Endpoints - Sistema de Facturas**

## **🔗 Base URL**
```
http://localhost:8000/api
```

---

## **📋 FACTURAS**

### 📊 **Listar Facturas**
**GET** `/api/invoices`

**Descripción:** Obtiene una lista paginada de todas las facturas con filtros opcionales.

**Parámetros de consulta:**
- `page`: Número de página (por defecto: 1)
- `per_page`: Elementos por página (por defecto: 15, máximo: 100)
- `search`: Búsqueda en número de factura, descripción, proveedor o centro de costo
- `status`: Filtrar por estado (`PENDIENTE` o `PAGADA`)
- `provider_id`: Filtrar por proveedor específico
- `cost_center_id`: Filtrar por centro de costo específico
- `date_from`: Filtrar facturas desde una fecha (formato: YYYY-MM-DD)
- `date_to`: Filtrar facturas hasta una fecha (formato: YYYY-MM-DD)
- `amount_min`: Monto mínimo
- `amount_max`: Monto máximo
- `overdue`: Filtrar facturas vencidas (true/false)
- `sort_by`: Campo para ordenar (`invoice_date`, `total_amount`, `created_at`)
- `sort_order`: Orden (`asc` o `desc`)

**Ejemplo de petición:**
```
GET /api/invoices?page=1&per_page=10&status=PENDIENTE&search=paneles&sort_by=invoice_date&sort_order=desc
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Facturas obtenidas exitosamente",
  "data": {
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
        "cost_center_id": 1,
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "provider": {
          "provider_id": 1,
          "provider_name": "Energía Solar S.A.S",
          "provider_tax_id": "900123456-7"
        },
        "cost_center": {
          "cost_center_id": 1,
          "cost_center_name": "Proyectos Residenciales"
        }
      }
    ],
    "first_page_url": "http://localhost:8000/api/invoices?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/invoices?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://localhost:8000/api/invoices?page=1",
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
    "path": "http://localhost:8000/api/invoices",
    "per_page": 15,
    "prev_page_url": null,
    "to": 8,
    "total": 8
  }
}
```

---

### 🔍 **Obtener Factura Específica**
**GET** `/api/invoices/{id}`

**Descripción:** Obtiene los detalles de una factura específica.

**Parámetros de ruta:**
- `id`: ID de la factura

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Factura obtenida exitosamente",
  "data": {
    "invoice_id": 1,
    "invoice_number": "FAC-001-2024",
    "invoice_date": "2025-08-11",
    "due_date": "2025-08-26",
    "total_amount": "1500000.00",
    "description": "Compra de paneles solares para proyecto residencial",
    "status": "PAGADA",
    "provider_id": 1,
    "cost_center_id": 1,
    "created_at": "2025-09-10T13:41:10.000000Z",
    "updated_at": "2025-09-10T13:41:10.000000Z",
    "provider": {
      "provider_id": 1,
      "provider_name": "Energía Solar S.A.S",
      "provider_tax_id": "900123456-7"
    },
    "cost_center": {
      "cost_center_id": 1,
      "cost_center_name": "Proyectos Residenciales"
    }
  }
}
```

**Respuesta de error (404):**
```json
{
  "success": false,
  "message": "Factura no encontrada"
}
```

---

### ➕ **Crear Factura**
**POST** `/api/invoices`

**Descripción:** Crea una nueva factura.

**Cuerpo de la petición:**
```json
{
  "invoice_number": "FAC-009-2024",
  "invoice_date": "2025-09-10",
  "due_date": "2025-09-25",
  "total_amount": 2500000.00,
  "description": "Compra de equipos solares para proyecto comercial",
  "status": "PENDIENTE",
  "provider_id": 1,
  "cost_center_id": 1
}
```

**Validaciones:**
- `invoice_number`: Requerido, string, máximo 100 caracteres
- `invoice_date`: Requerido, fecha
- `due_date`: Opcional, fecha, debe ser mayor o igual a `invoice_date`
- `total_amount`: Requerido, numérico, mínimo 0
- `description`: Opcional, string, máximo 1000 caracteres
- `status`: Requerido, enum: `PENDIENTE` o `PAGADA`
- `provider_id`: Requerido, debe existir en la tabla `providers`
- `cost_center_id`: Requerido, debe existir en la tabla `cost_centers`

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Factura creada exitosamente",
  "data": {
    "invoice_id": 9,
    "invoice_number": "FAC-009-2024",
    "invoice_date": "2025-09-10",
    "due_date": "2025-09-25",
    "total_amount": "2500000.00",
    "description": "Compra de equipos solares para proyecto comercial",
    "status": "PENDIENTE",
    "provider_id": 1,
    "cost_center_id": 1,
    "created_at": "2025-09-10T14:00:00.000000Z",
    "updated_at": "2025-09-10T14:00:00.000000Z",
    "provider": {
      "provider_id": 1,
      "provider_name": "Energía Solar S.A.S",
      "provider_tax_id": "900123456-7"
    },
    "cost_center": {
      "cost_center_id": 1,
      "cost_center_name": "Proyectos Residenciales"
    }
  }
}
```

**Respuesta de error (422):**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "invoice_number": ["El número de factura es requerido"],
    "total_amount": ["El monto total debe ser un número válido"]
  }
}
```

---

### ✏️ **Actualizar Factura**
**PUT** `/api/invoices/{id}`

**Descripción:** Actualiza una factura existente.

**Cuerpo de la petición:**
```json
{
  "invoice_number": "FAC-001-2024",
  "invoice_date": "2025-08-11",
  "due_date": "2025-08-30",
  "total_amount": 1600000.00,
  "description": "Compra de paneles solares para proyecto residencial - Actualizado",
  "status": "PAGADA",
  "provider_id": 1,
  "cost_center_id": 1
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Factura actualizada exitosamente",
  "data": {
    "invoice_id": 1,
    "invoice_number": "FAC-001-2024",
    "invoice_date": "2025-08-11",
    "due_date": "2025-08-30",
    "total_amount": "1600000.00",
    "description": "Compra de paneles solares para proyecto residencial - Actualizado",
    "status": "PAGADA",
    "provider_id": 1,
    "cost_center_id": 1,
    "created_at": "2025-09-10T13:41:10.000000Z",
    "updated_at": "2025-09-10T14:00:00.000000Z",
    "provider": {
      "provider_id": 1,
      "provider_name": "Energía Solar S.A.S",
      "provider_tax_id": "900123456-7"
    },
    "cost_center": {
      "cost_center_id": 1,
      "cost_center_name": "Proyectos Residenciales"
    }
  }
}
```

---

### 🗑️ **Eliminar Factura**
**DELETE** `/api/invoices/{id}`

**Descripción:** Elimina una factura existente.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Factura eliminada exitosamente"
}
```

**Respuesta de error (404):**
```json
{
  "success": false,
  "message": "Factura no encontrada"
}
```

---

### 🔄 **Cambiar Estado de Factura**
**PATCH** `/api/invoices/{id}/status`

**Descripción:** Cambia el estado de una factura (PENDIENTE ↔ PAGADA).

**Cuerpo de la petición:**
```json
{
  "status": "PAGADA"
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Estado de factura actualizado exitosamente",
  "data": {
    "invoice_id": 1,
    "invoice_number": "FAC-001-2024",
    "invoice_date": "2025-08-11",
    "due_date": "2025-08-26",
    "total_amount": "1500000.00",
    "description": "Compra de paneles solares para proyecto residencial",
    "status": "PAGADA",
    "provider_id": 1,
    "cost_center_id": 1,
    "created_at": "2025-09-10T13:41:10.000000Z",
    "updated_at": "2025-09-10T14:00:00.000000Z",
    "provider": {
      "provider_id": 1,
      "provider_name": "Energía Solar S.A.S",
      "provider_tax_id": "900123456-7"
    },
    "cost_center": {
      "cost_center_id": 1,
      "cost_center_name": "Proyectos Residenciales"
    }
  }
}
```

---

### 📊 **Estadísticas de Facturas**
**GET** `/api/invoices/statistics`

**Descripción:** Obtiene estadísticas generales de las facturas.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Estadísticas obtenidas exitosamente",
  "data": {
    "total_invoices": 8,
    "pending_invoices": 6,
    "paid_invoices": 2,
    "overdue_invoices": 1,
    "total_amount": "18750000.00",
    "pending_amount": "15750000.00",
    "paid_amount": "3000000.00",
    "overdue_amount": "850000.00",
    "average_invoice_amount": "2343750.00",
    "invoices_by_status": {
      "PENDIENTE": 6,
      "PAGADA": 2
    },
    "invoices_by_provider": [
      {
        "provider_id": 1,
        "provider_name": "Energía Solar S.A.S",
        "invoice_count": 3,
        "total_amount": "7000000.00"
      }
    ],
    "invoices_by_cost_center": [
      {
        "cost_center_id": 1,
        "cost_center_name": "Proyectos Residenciales",
        "invoice_count": 4,
        "total_amount": "9000000.00"
      }
    ]
  }
}
```

---

### 🔍 **Buscar Facturas**
**GET** `/api/invoices/search`

**Descripción:** Busca facturas por número, descripción, proveedor o centro de costo.

**Parámetros de consulta:**
- `q`: Término de búsqueda (requerido)
- `page`: Número de página
- `per_page`: Elementos por página

**Ejemplo de petición:**
```
GET /api/invoices/search?q=paneles&page=1&per_page=10
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
        "invoice_id": 1,
        "invoice_number": "FAC-001-2024",
        "invoice_date": "2025-08-11",
        "due_date": "2025-08-26",
        "total_amount": "1500000.00",
        "description": "Compra de paneles solares para proyecto residencial",
        "status": "PAGADA",
        "provider_id": 1,
        "cost_center_id": 1,
        "created_at": "2025-09-10T13:41:10.000000Z",
        "updated_at": "2025-09-10T13:41:10.000000Z",
        "provider": {
          "provider_id": 1,
          "provider_name": "Energía Solar S.A.S",
          "provider_tax_id": "900123456-7"
        },
        "cost_center": {
          "cost_center_id": 1,
          "cost_center_name": "Proyectos Residenciales"
        }
      }
    ],
    "first_page_url": "http://localhost:8000/api/invoices/search?q=paneles&page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/invoices/search?q=paneles&page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://localhost:8000/api/invoices/search?q=paneles&page=1",
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
    "path": "http://localhost:8000/api/invoices/search",
    "per_page": 15,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

---

## **📝 Notas Importantes**

### **🔒 Validaciones:**
- Todos los campos requeridos deben ser proporcionados
- Las fechas deben estar en formato ISO (YYYY-MM-DD)
- Los montos deben ser números positivos
- Los IDs de proveedor y centro de costo deben existir
- El estado solo puede ser `PENDIENTE` o `PAGADA`

### **📄 Paginación:**
- Por defecto se muestran 15 elementos por página
- Máximo 100 elementos por página
- Incluye enlaces de navegación y metadatos

### **🔍 Búsqueda:**
- Busca en número de factura, descripción, nombre del proveedor y nombre del centro de costo
- No distingue entre mayúsculas y minúsculas
- Soporta búsqueda parcial

### **📊 Estadísticas:**
- Incluye conteos por estado
- Montos totales y promedios
- Distribución por proveedor y centro de costo
- Identificación de facturas vencidas

### **⚠️ Códigos de Error:**
- `400`: Solicitud incorrecta
- `404`: Recurso no encontrado
- `422`: Error de validación
- `500`: Error interno del servidor
