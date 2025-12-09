# Endpoints del Dashboard Financiero

## Descripción General

El dashboard financiero proporciona métricas y estadísticas completas para el análisis financiero de la empresa, incluyendo resúmenes de facturas, alertas de vencimientos y análisis de proyectos activos.

## Base URL

```
/api/financial-dashboard
```

## Autenticación

Todos los endpoints requieren autenticación mediante token Bearer.

## Endpoints Disponibles

### 1. Resumen Financiero

**GET** `/api/financial-dashboard/summary`

Obtiene un resumen completo de las métricas financieras principales.

#### Ejemplo de Response:
```json
{
    "success": true,
    "data": {
        "overview": {
            "total_invoices": 6,
            "total_amount": 113050000.00,
            "total_paid": 32850000.00,
            "total_balance": 80200000.00,
            "average_invoice_value": 18841666.67,
            "payment_percentage": 29.06
        },
        "status_breakdown": {
            "pendiente": {
                "count": 5,
                "total_amount": 95200000.00,
                "paid_amount": 0.00,
                "balance": 95200000.00
            },
            "pagada": {
                "count": 1,
                "total_amount": 17850000.00,
                "paid_amount": 17850000.00,
                "balance": 0.00
            },
            "cancelada": {
                "count": 0,
                "total_amount": 0.00,
                "paid_amount": 0.00,
                "balance": 0.00
            }
        }
    }
}
```

#### Descripción de los Datos:

**Overview:**
- `total_invoices`: Total de facturas registradas
- `total_amount`: Suma total de todos los montos de facturas
- `total_paid`: Suma total de montos pagados
- `total_balance`: Suma total de saldos pendientes
- `average_invoice_value`: Promedio del valor de las facturas
- `payment_percentage`: Porcentaje de pago general

**Status Breakdown:**
- Desglose por estado con conteos y montos
- Incluye montos totales, pagados y pendientes por estado

### 2. Facturas Próximas a Vencer

**GET** `/api/financial-dashboard/upcoming-due`

Obtiene facturas próximas a vencer y vencidas con información detallada y niveles de urgencia.

#### Parámetros de Query (opcionales):
- `days` (integer): Días hacia adelante para considerar "próximas a vencer" (default: 30)

#### Ejemplo de Request:
```bash
GET /api/financial-dashboard/upcoming-due?days=15
```

#### Ejemplo de Response:
```json
{
    "success": true,
    "data": {
        "upcoming_due": {
            "count": 2,
            "total_amount": 850000.00,
            "invoices": [
                {
                    "id": 1,
                    "invoice_number": "FAC-001-2025",
                    "due_date": "07/09/2025",
                    "days_until_due": 2,
                    "total_amount": 1500000.00,
                    "balance": 0.00,
                    "status": "pendiente",
                    "supplier": "Jinko Solar Colombia",
                    "project": "Proyecto Solar Norte",
                    "responsible_user": "Jhan Martinez",
                    "urgency_level": "high"
                }
            ]
        },
        "overdue": {
            "count": 2,
            "total_amount": 1650000.00,
            "invoices": [
                {
                    "id": 4,
                    "invoice_number": "FAC-004-2025",
                    "due_date": "30/08/2025",
                    "days_overdue": 5,
                    "total_amount": 1200000.00,
                    "balance": 1200000.00,
                    "status": "pendiente",
                    "supplier": "Baterías ABC",
                    "project": "N/A",
                    "responsible_user": "Jhan Martinez",
                    "urgency_level": "critical"
                }
            ]
        },
        "summary": {
            "total_critical": 3,
            "total_upcoming_amount": 850000.00,
            "total_overdue_amount": 1650000.00,
            "days_filter": 15
        }
    }
}
```

#### Niveles de Urgencia:
- **`critical`**: Facturas vencidas
- **`high`**: Facturas que vencen en 1-7 días
- **`medium`**: Facturas que vencen en 8-15 días
- **`low`**: Facturas que vencen en más de 15 días

### 3. Gráfica de Facturas por Mes

**GET** `/api/financial-dashboard/charts/monthly-invoices`

Obtiene datos de facturas agrupadas por mes para generar gráficas del dashboard financiero.

#### Parámetros de Query (opcionales):
- `year` (integer): Año para filtrar las facturas (default: año actual)
- `month` (integer): Mes específico para filtrar (1-12, opcional)

#### Ejemplo de Request:
```bash
GET /api/financial-dashboard/charts/monthly-invoices?year=2024&month=09
```

#### Ejemplo de Response:
```json
{
  "success": true,
  "data": [
    {
      "month": "Ene",
      "year": 2024,
      "count": 5,
      "total_amount": 15000000.00
    },
    {
      "month": "Feb", 
      "year": 2024,
      "count": 3,
      "total_amount": 8500000.00
    },
    {
      "month": "Mar",
      "year": 2024,
      "count": 7,
      "total_amount": 22000000.00
    }
  ]
}
```

#### Descripción de los Datos:
- `month`: Nombre abreviado del mes en español
- `year`: Año de las facturas
- `count`: Número total de facturas en ese mes
- `total_amount`: Suma total de los montos de las facturas en ese mes

### 4. Top Proveedores

**GET** `/api/financial-dashboard/charts/top-suppliers`

Obtiene los proveedores con mayor monto total de facturas para análisis de gastos.

#### Parámetros de Query (opcionales):
- `limit` (integer): Número de proveedores a retornar (default: 5, máximo: 50)

#### Ejemplo de Request:
```bash
GET /api/financial-dashboard/charts/top-suppliers?limit=5
```

#### Ejemplo de Response:
```json
{
  "success": true,
  "data": [
    {
      "supplier_name": "Jinko Solar Colombia",
      "total_amount": 25000000.00,
      "invoice_count": 8
    },
    {
      "supplier_name": "Baterías ABC",
      "total_amount": 18000000.00,
      "invoice_count": 5
    },
    {
      "supplier_name": "Inversores XYZ",
      "total_amount": 12000000.00,
      "invoice_count": 3
    }
  ]
}
```

#### Descripción de los Datos:
- `supplier_name`: Nombre del proveedor
- `total_amount`: Suma total de todas las facturas del proveedor
- `invoice_count`: Número total de facturas del proveedor

### 5. Estados de Facturas

**GET** `/api/financial-dashboard/charts/invoice-status`

Obtiene la distribución de facturas por estado con porcentajes para gráficas de estado.

#### Ejemplo de Response:
```json
{
  "success": true,
  "data": [
    {
      "status": "pendiente",
      "count": 15,
      "percentage": 60.0
    },
    {
      "status": "pagada",
      "count": 8,
      "percentage": 32.0
    },
    {
      "status": "cancelada",
      "count": 2,
      "percentage": 8.0
    }
  ]
}
```

#### Descripción de los Datos:
- `status`: Estado de la factura (pendiente, pagada, cancelada)
- `count`: Número de facturas en ese estado
- `percentage`: Porcentaje del total de facturas que representa ese estado

### 6. Métodos de Pago

**GET** `/api/financial-dashboard/charts/payment-methods`

Obtiene la distribución de facturas por método de pago para análisis de preferencias de pago.

#### Ejemplo de Response:
```json
{
  "success": true,
  "data": [
    {
      "payment_method": "transferencia",
      "count": 12
    },
    {
      "payment_method": "efectivo",
      "count": 8
    },
    {
      "payment_method": "cheque",
      "count": 5
    }
  ]
}
```

#### Descripción de los Datos:
- `payment_method`: Método de pago utilizado (transferencia, efectivo, cheque, etc.)
- `count`: Número de facturas pagadas con ese método

### 7. Proyectos Activos con Análisis Financiero

**GET** `/api/financial-dashboard/active-projects`

Obtiene la lista de proyectos activos con el análisis financiero de facturas pagadas asociadas.

#### Ejemplo de Response:
```json
{
    "success": true,
    "data": {
        "overview": {
            "total_active_projects": 4,
            "total_paid_in_active_projects": 17850000.00
        },
        "projects": [
            {
                "project_id": 1,
                "project_name": "Sistema Solar Residencial Norte",
                "start_date": "15/08/2025",
                "estimated_end_date": "19/09/2025",
                "status_id": 1,
                "client": {
                    "id": 1,
                    "name": "Empresa Solar del Norte S.A.S.",
                    "type": "residencial"
                },
                "quotation": {
                    "id": 1,
                    "name": "Sistema Solar Residencial Norte",
                    "power_kwp": 5.0
                },
                "financial_summary": {
                    "total_paid_invoices": 1,
                    "total_paid_amount": 17850000.00
                }
            },
            {
                "project_id": 2,
                "project_name": "Sistema Solar Comercial Centro",
                "start_date": "20/08/2025",
                "estimated_end_date": "24/09/2025",
                "status_id": 1,
                "client": {
                    "id": 2,
                    "name": "Comercial Solar del Centro S.A.S.",
                    "type": "comercial"
                },
                "quotation": {
                    "id": 2,
                    "name": "Sistema Solar Comercial Centro",
                    "power_kwp": 10.0
                },
                "financial_summary": {
                    "total_paid_invoices": 0,
                    "total_paid_amount": 0.00
                }
            }
        ]
    }
}
```

#### Descripción de los Datos:

**Overview:**
- `total_active_projects`: Total de proyectos activos
- `total_paid_in_active_projects`: Suma total de facturas pagadas en proyectos activos

**Projects:**
- Lista de proyectos activos ordenados por monto pagado descendente
- Incluye información del cliente y cotización
- `financial_summary`: Resumen financiero del proyecto

## Casos de Uso

### Obtener Resumen Financiero:
```bash
GET /api/financial-dashboard/summary
```

### Obtener Facturas Próximas a Vencer:
```bash
GET /api/financial-dashboard/upcoming-due?days=15
```

### Obtener Gráfica de Facturas por Mes:
```bash
GET /api/financial-dashboard/charts/monthly-invoices?year=2024
```

### Obtener Facturas de un Mes Específico:
```bash
GET /api/financial-dashboard/charts/monthly-invoices?year=2024&month=09
```

### Obtener Top Proveedores:
```bash
GET /api/financial-dashboard/charts/top-suppliers?limit=5
```

### Obtener Estados de Facturas:
```bash
GET /api/financial-dashboard/charts/invoice-status
```

### Obtener Métodos de Pago:
```bash
GET /api/financial-dashboard/charts/payment-methods
```

### Obtener Proyectos Activos:
```bash
GET /api/financial-dashboard/active-projects
```

## Códigos de Error

### Errores Comunes:
- **400**: Parámetros de query inválidos
- **401**: Token de autenticación inválido o expirado
- **500**: Error interno del servidor

### Ejemplo de Error:
```json
{
    "success": false,
    "message": "Error getting financial summary",
    "error": "Database connection failed"
}
```

## Notas Importantes

1. **Proyectos Activos**: Se consideran activos los proyectos con `status_id = 1`
2. **Facturas Próximas a Vencer**: Solo incluye facturas con estado `pendiente`
3. **Ordenamiento**: Los proyectos se ordenan por monto pagado descendente
4. **Fechas**: Todas las fechas se devuelven en formato `d/m/Y`
5. **Montos**: Todos los montos se devuelven como números decimales
6. **Niveles de Urgencia**: Se calculan automáticamente basados en días hasta vencimiento

## Integración con Otros Módulos

### Con Facturas:
- Utiliza datos de la tabla `purchases` para cálculos financieros
- Filtra por estados y fechas de vencimiento

### Con Proyectos:
- Obtiene información de proyectos activos
- Calcula sumas de facturas pagadas por proyecto

### Con Clientes:
- Incluye información del cliente en proyectos activos
- Permite análisis por tipo de cliente

### Con Cotizaciones:
- Muestra información de la cotización asociada al proyecto
- Incluye potencia del sistema en kWp
