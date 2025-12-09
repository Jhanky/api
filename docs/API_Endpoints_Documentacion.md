# 🔗 Documentación de Endpoints - API de Facturas

## 📋 Índice
1. [Información General](#información-general)
2. [Endpoints Principales](#endpoints-principales)
3. [Endpoints Especializados](#endpoints-especializados)
4. [Respuestas de la API](#respuestas-de-la-api)
5. [Códigos de Estado](#códigos-de-estado)
6. [Ejemplos de Uso](#ejemplos-de-uso)

---

## 📊 Información General

### **Base URL**
```
/api/invoices
```

### **Autenticación**
- Requiere token de autenticación
- Headers: `Authorization: Bearer {token}`

### **Formato de Respuesta**
- **Content-Type**: `application/json`
- **Estructura**: Consistente en todos los endpoints

---

## 🎯 Endpoints Principales

### **1. Listar Facturas**
```http
GET /api/invoices
```

#### **Parámetros de Consulta:**
| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `search` | string | Búsqueda general | `?search=FAC-001` |
| `status` | enum | Estado de la factura | `?status=PENDIENTE` |
| `provider_id` | integer | ID del proveedor | `?provider_id=1` |
| `cost_center_id` | integer | ID del centro de costos | `?cost_center_id=2` |
| `overdue` | boolean | Facturas vencidas | `?overdue=true` |
| `invoice_month` | integer | Mes de la factura (1-12) | `?invoice_month=10` |
| `invoice_year` | integer | Año de la factura | `?invoice_year=2024` |
| `sort_by` | string | Campo de ordenamiento | `?sort_by=invoice_date` |
| `sort_order` | enum | Dirección (asc/desc) | `?sort_order=desc` |
| `per_page` | integer | Elementos por página | `?per_page=20` |
| `page` | integer | Número de página | `?page=2` |

#### **Ejemplo de Respuesta:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "invoice_id": 1,
                "invoice_number": "FAC-001-2024",
                "invoice_date": "2024-10-06",
                "subtotal": 1000.00,
                "iva_amount": 190.00,
                "retention": 0,
                "has_retention": false,
                "total_amount": 1190.00,
                "status": "PENDIENTE",
                "sale_type": "CREDITO",
                "provider": {
                    "provider_id": 1,
                    "name": "Proveedor ABC"
                },
                "cost_center": {
                    "cost_center_id": 1,
                    "name": "Centro de Costos A"
                },
                "payment_method": {
                    "id": 1,
                    "code": "TCD",
                    "name": "Transferencia desde cuenta Davivienda E4(TCD)"
                }
            }
        ],
        "total": 50,
        "per_page": 15
    }
}
```

### **2. Crear Factura**
```http
POST /api/invoices
Content-Type: application/json
```

#### **Cuerpo de la Solicitud:**
```json
{
    "invoice_number": "FAC-001-2024",
    "invoice_date": "2024-10-06",
    "due_date": "2024-10-13",
    "subtotal": 1000.00,
    "retention": 0,
    "has_retention": false,
    "status": "PENDIENTE",
    "sale_type": "CREDITO",
    "payment_method_id": 1,
    "provider_id": 1,
    "cost_center_id": 1,
    "description": "Descripción de la factura"
}
```

#### **Campos Requeridos:**
- `invoice_number` - Número de factura
- `invoice_date` - Fecha de emisión
- `subtotal` - Subtotal antes de impuestos
- `status` - Estado (PENDIENTE/PAGADA)
- `sale_type` - Tipo de venta (CONTADO/CREDITO)
- `provider_id` - ID del proveedor
- `cost_center_id` - ID del centro de costos

#### **Campos Opcionales:**
- `due_date` - Fecha de vencimiento
- `retention` - Monto de retención
- `has_retention` - Si aplica retención (boolean)
- `payment_method_id` - ID del método de pago
- `description` - Descripción adicional

### **3. Mostrar Factura**
```http
GET /api/invoices/{id}
```

#### **Parámetros:**
- `{id}` - ID de la factura

### **4. Actualizar Factura**
```http
PUT /api/invoices/{id}
PATCH /api/invoices/{id}
Content-Type: application/json
```

#### **Cuerpo de la Solicitud:**
```json
{
    "invoice_number": "FAC-001-2024",
    "subtotal": 1200.00,
    "status": "PAGADA",
    "has_retention": true,
    "retention": 50.00
}
```

### **5. Eliminar Factura**
```http
DELETE /api/invoices/{id}
```

---

## 🔧 Endpoints Especializados

### **6. Actualizar Estado**
```http
PATCH /api/invoices/{id}/status
Content-Type: application/json
```

#### **Cuerpo de la Solicitud:**
```json
{
    "status": "PAGADA"
}
```

### **7. Probar Consulta de Reporte**
```http
GET /api/invoices/test-report
```

#### **Parámetros:** Mismos que listar facturas
#### **Propósito:** Para desarrollo y debugging

### **8. Estadísticas**
```http
GET /api/invoices/statistics
```

#### **Ejemplo de Respuesta:**
```json
{
    "success": true,
    "data": {
        "total_invoices": 150,
        "pending_invoices": 45,
        "paid_invoices": 105,
        "total_amount": 2500000.00,
        "pending_amount": 500000.00,
        "paid_amount": 2000000.00,
        "overdue_invoices": 12,
        "overdue_amount": 150000.00
    }
}
```

### **9. Exportar a Excel**
```http
GET /api/invoices/export
```

#### **Parámetros:** Mismos que listar facturas
#### **Respuesta:** Archivo Excel descargable
#### **Headers de Respuesta:**
```
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="facturas_2024-10-06.xlsx"
```

### **10. Cambiar Centro de Costo**
```http
PATCH /api/invoices/{id}/cost-center
Content-Type: application/json
```

#### **Cuerpo de la Solicitud:**
```json
{
    "cost_center_id": 2
}
```

#### **Ejemplo de Respuesta:**
```json
{
    "success": true,
    "message": "Centro de costo actualizado exitosamente",
    "data": {
        "invoice": {
            "invoice_id": 1,
            "cost_center_id": 2
        },
        "old_cost_center": {
            "cost_center_id": 1,
            "name": "Centro Anterior"
        },
        "new_cost_center": {
            "cost_center_id": 2,
            "name": "Centro Nuevo"
        }
    }
}
```

### **11. Aplicar/Remover Retención**
```http
PATCH /api/invoices/{id}/retention
Content-Type: application/json
```

#### **Aplicar Retención:**
```json
{
    "has_retention": true,
    "retention_amount": 100.00
}
```

#### **Remover Retención:**
```json
{
    "has_retention": false
}
```

#### **Ejemplo de Respuesta:**
```json
{
    "success": true,
    "message": "Retención aplicada exitosamente",
    "data": {
        "invoice": {
            "invoice_id": 1,
            "has_retention": true,
            "retention": 100.00
        },
        "retention_summary": {
            "has_retention": true,
            "retention_amount": 100.00,
            "total_with_retention": 1090.00,
            "retention_percentage": 10.0
        }
    }
}
```

---

## 📋 Respuestas de la API

### **Estructura de Respuesta Exitosa:**
```json
{
    "success": true,
    "message": "Descripción del resultado",
    "data": {
        // Datos específicos del endpoint
    }
}
```

### **Estructura de Respuesta de Error:**
```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": {
        "field_name": ["Mensaje de error específico"]
    }
}
```

### **Ejemplo de Error de Validación:**
```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "invoice_number": ["El número de factura es requerido"],
        "subtotal": ["El subtotal debe ser mayor a 0"]
    }
}
```

---

## 🔢 Códigos de Estado HTTP

| Código | Descripción | Uso Común |
|--------|-------------|-----------|
| `200` | OK | Operación exitosa |
| `201` | Created | Recurso creado exitosamente |
| `400` | Bad Request | Solicitud incorrecta |
| `401` | Unauthorized | No autenticado |
| `403` | Forbidden | Sin permisos |
| `404` | Not Found | Recurso no encontrado |
| `422` | Unprocessable Entity | Error de validación |
| `500` | Internal Server Error | Error interno del servidor |

---

## 💡 Ejemplos de Uso

### **Ejemplo 1: Listar Facturas Pendientes**
```http
GET /api/invoices?status=PENDIENTE&sort_by=due_date&sort_order=asc
```

### **Ejemplo 2: Buscar Facturas por Proveedor**
```http
GET /api/invoices?provider_id=1&per_page=10
```

### **Ejemplo 3: Facturas Vencidas del Mes**
```http
GET /api/invoices?overdue=true&invoice_month=10&invoice_year=2024
```

### **Ejemplo 4: Crear Factura de Contado**
```json
POST /api/invoices
{
    "invoice_number": "FAC-CONTADO-001",
    "invoice_date": "2024-10-06",
    "due_date": "2024-10-06",
    "subtotal": 500.00,
    "status": "PAGADA",
    "sale_type": "CONTADO",
    "payment_method_id": 3,
    "provider_id": 1,
    "cost_center_id": 1
}
```

### **Ejemplo 5: Exportar Facturas del Mes**
```http
GET /api/invoices/export?invoice_month=10&invoice_year=2024
```

---

## 🚀 Casos de Uso Comunes

### **Dashboard de Facturas**
```http
# Obtener estadísticas generales
GET /api/invoices/statistics

# Listar facturas pendientes
GET /api/invoices?status=PENDIENTE&per_page=10

# Facturas vencidas
GET /api/invoices?overdue=true
```

### **Gestión de Pagos**
```http
# Marcar factura como pagada
PATCH /api/invoices/1/status
{
    "status": "PAGADA"
}

# Aplicar retención
PATCH /api/invoices/1/retention
{
    "has_retention": true,
    "retention_amount": 50.00
}
```

### **Reportes y Análisis**
```http
# Exportar facturas del mes
GET /api/invoices/export?invoice_month=10&invoice_year=2024

# Facturas por centro de costos
GET /api/invoices?cost_center_id=2
```

### **Gestión de Archivos**
```http
# Subir archivos a factura
POST /api/invoices/1/upload-files
Content-Type: multipart/form-data
payment_support: [archivo]
invoice_file: [archivo]

# Eliminar archivos de factura
DELETE /api/invoices/1/remove-files
{
    "file_type": "payment_support" // o "invoice_file" o "both"
}
```

---

*Documentación de Endpoints - API de Facturas v2.2 - 6 de Octubre de 2025*
