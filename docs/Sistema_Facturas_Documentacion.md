# 📄 Sistema de Facturas - Documentación Completa

## 📋 Índice
1. [Introducción](#introducción)
2. [Estructura de la Base de Datos](#estructura-de-la-base-de-datos)
3. [Tipos de Venta y Estados](#tipos-de-venta-y-estados)
4. [Modelo Invoice](#modelo-invoice)
5. [Scopes y Métodos](#scopes-y-métodos)
6. [Ejemplos de Uso](#ejemplos-de-uso)
7. [Casos de Uso Comunes](#casos-de-uso-comunes)

---

## 🎯 Introducción

El sistema de facturas ha sido diseñado para manejar de manera profesional y contablemente correcta todos los tipos de ventas y sus estados de pago. Permite distinguir claramente entre ventas de contado y a crédito, así como su estado de pago.

### ✨ Características Principales
- ✅ **Tipos de Venta**: Contado y Crédito
- ✅ **Estados de Pago**: Pendiente y Pagada
- ✅ **Cálculos Automáticos**: IVA, totales y retenciones
- ✅ **Documentos**: Soporte de pago y archivos de factura
- ✅ **Filtros Avanzados**: Scopes especializados para consultas contables

---

## 🗄️ Estructura de la Base de Datos

### Tabla: `invoices`

| Campo | Tipo | Descripción | Comentario |
|-------|------|--------------|------------|
| `invoice_id` | BIGINT | ID único de la factura | PRIMARY KEY |
| `invoice_number` | VARCHAR(255) | Número de factura | Identificador único |
| `invoice_date` | DATE | Fecha de emisión | Fecha cuando se emitió |
| `due_date` | DATE | Fecha de vencimiento | Fecha límite de pago |
| `provider_id` | BIGINT | ID del proveedor | Relación con tabla providers |
| `cost_center_id` | BIGINT | ID del centro de costos | Relación con tabla cost_centers |
| `subtotal` | DECIMAL(15,2) | Subtotal antes de impuestos | Base para cálculos |
| `iva_amount` | DECIMAL(15,2) | Valor del IVA (19%) | Calculado automáticamente |
| `retention` | DECIMAL(15,2) | Retención en la fuente | Descuentos aplicados |
| `total_amount` | DECIMAL(15,2) | Total a pagar | Subtotal + IVA - Retención |
| `status` | ENUM | Estado de pago | 'PENDIENTE' o 'PAGADA' |
| `sale_type` | ENUM | Tipo de venta | 'CONTADO' o 'CREDITO' |
| `payment_method_id` | BIGINT | ID del método de pago | Relación con tabla payment_methods |
| `payment_support` | VARCHAR(255) | Soporte de pago | Archivo PDF/imagen |
| `invoice_file` | VARCHAR(255) | Archivo de factura | Archivo PDF/imagen |
| `description` | TEXT | Descripción | Notas adicionales |
| `created_at` | TIMESTAMP | Fecha de creación | Metadato |
| `updated_at` | TIMESTAMP | Fecha de actualización | Metadato |

### Tabla: `payment_methods`

| Campo | Tipo | Descripción | Comentario |
|-------|------|--------------|------------|
| `id` | BIGINT | ID único del método | PRIMARY KEY |
| `code` | VARCHAR(10) | Código del método | TCD, CP, EF |
| `name` | VARCHAR | Nombre completo | Descripción detallada |
| `description` | TEXT | Descripción del método | Información adicional |
| `is_active` | BOOLEAN | Si está activo | Control de estado |
| `created_at` | TIMESTAMP | Fecha de creación | Metadato |
| `updated_at` | TIMESTAMP | Fecha de actualización | Metadato |

#### **Datos de Métodos de Pago:**
| ID | Código | Nombre | Descripción |
|----|--------|--------|-------------|
| 1 | **TCD** | Transferencia desde cuenta Davivienda E4(TCD) | Transferencia bancaria desde cuenta empresarial Davivienda E4 |
| 2 | **CP** | Transferencia desde Cuenta personal(CP) | Transferencia bancaria desde cuenta personal |
| 3 | **EF** | Efectivo(EF) | Pago en efectivo |

---

## 💰 Tipos de Venta y Estados

### 🏷️ Tipos de Venta (`sale_type`)

#### 1. **CONTADO**
- **Descripción**: Venta que se paga inmediatamente
- **Características**: 
  - Se paga al momento de la venta
  - No requiere seguimiento de vencimiento
  - Estado típico: `PAGADA`

#### 2. **CREDITO**
- **Descripción**: Venta que se paga posteriormente
- **Características**:
  - Se paga después de la venta
  - Requiere seguimiento de vencimiento
  - Puede estar `PENDIENTE` o `PAGADA`

### 📊 Estados de Pago (`status`)

#### 1. **PENDIENTE**
- **Descripción**: Factura pendiente de pago
- **Aplicable a**: Cualquier tipo de venta
- **Acciones**: Requiere seguimiento y cobro

#### 2. **PAGADA**
- **Descripción**: Factura ya pagada
- **Aplicable a**: Cualquier tipo de venta
- **Acciones**: Archivar, generar reportes

### 🔄 Combinaciones Posibles

| Tipo de Venta | Estado | Descripción | Uso Común |
|---------------|--------|-------------|-----------|
| `CONTADO` | `PAGADA` | Venta de contado pagada | Pago inmediato |
| `CONTADO` | `PENDIENTE` | Venta de contado pendiente | Pago diferido |
| `CREDITO` | `PENDIENTE` | Venta a crédito pendiente | Cobro posterior |
| `CREDITO` | `PAGADA` | Venta a crédito pagada | Crédito cobrado |

---

## 🏗️ Modelo Invoice

### 📦 Ubicación
```
app/Models/Invoice.php
```

### 🔗 **Nueva Relación con PaymentMethod**
```php
public function paymentMethod()
{
    return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
}
```

### 💳 **Métodos de Verificación de Pago**
```php
// Verificar tipo de método de pago
$invoice->isTcdPayment()      // Verifica si es TCD
$invoice->isCpPayment()       // Verifica si es CP
$invoice->isEfPayment()       // Verifica si es EF
$invoice->isTransferPayment() // Verifica si es transferencia

// Obtener información del método
$invoice->getPaymentMethodShort()  // Código corto (TCD, CP, EF)
$invoice->getPaymentMethodName()   // Nombre completo
$invoice->getPaymentMethodSummary() // Resumen completo
```

### 🔧 Propiedades Principales

#### Fillable Fields
```php
protected $fillable = [
    // Información básica
    'invoice_number', 'invoice_date', 'due_date',
    
    // Relaciones
    'provider_id', 'cost_center_id',
    
    // Valores contables
    'subtotal', 'iva_amount', 'retention', 'total_amount',
    
    // Estado y tipo
    'status', 'sale_type', 'payment_method',
    
    // Documentos
    'payment_support', 'invoice_file',
    
    // Metadatos
    'description'
];
```

#### Casts
```php
protected $casts = [
    'invoice_date' => 'date',
    'due_date' => 'date',
    'total_amount' => 'decimal:2',
    'subtotal' => 'decimal:2',
    'iva_amount' => 'decimal:2',
    'retention' => 'decimal:2'
];
```

---

## 🔍 Scopes y Métodos

### 📊 Scopes Básicos

#### Filtros por Estado
```php
// Facturas pendientes
Invoice::pending()->get();

// Facturas pagadas
Invoice::paid()->get();

// Facturas vencidas
Invoice::overdue()->get();

// Facturas próximas a vencer (7 días)
Invoice::dueSoon()->get();
```

#### Filtros por Tipo de Venta
```php
// Ventas de contado
Invoice::cashSales()->get();

// Ventas a crédito
Invoice::creditSales()->get();

// Créditos pendientes
Invoice::creditPending()->get();

// Créditos pagados
Invoice::creditPaid()->get();

// Contados pagados
Invoice::cashPaid()->get();
```

#### Filtros Contables
```php
// Por rango de montos
Invoice::byAmountRange(1000, 5000)->get();

// Por período contable
Invoice::byAccountingPeriod('2024-01-01', '2024-12-31')->get();

// Con retención
Invoice::withRetention()->get();

// Sin retención
Invoice::withoutRetention()->get();

// Con IVA
Invoice::withIva()->get();

// Exentas de IVA
Invoice::exemptFromIva()->get();
```

### 🧮 Métodos de Cálculo

#### Cálculos Automáticos
```php
$invoice = Invoice::find(1);

// Calcular IVA (19% del subtotal)
$iva = $invoice->calculateIvaAmount();

// Calcular total (subtotal + IVA - retención)
$total = $invoice->calculateTotalAmount();

// Resumen contable completo
$summary = $invoice->getAccountingSummary();
```

#### Verificaciones de Estado
```php
$invoice = Invoice::find(1);

// Verificar tipo de venta
$isCash = $invoice->isCashSale();        // ¿Es contado?
$isCredit = $invoice->isCreditSale();    // ¿Es crédito?

// Verificar estado de crédito
$isCreditPaid = $invoice->isCreditPaid();      // ¿Crédito pagado?
$isCreditPending = $invoice->isCreditPending(); // ¿Crédito pendiente?

// Verificar vencimiento
$isOverdue = $invoice->isOverdue();      // ¿Está vencida?
$daysOverdue = $invoice->getDaysOverdue(); // Días de vencimiento
```

#### Información Descriptiva
```php
$invoice = Invoice::find(1);

// Descripción del tipo de venta
$description = $invoice->getSaleTypeDescription();
// Ejemplo: "Venta a Crédito (Pagada)"

// Resumen completo
$summary = $invoice->getSaleTypeSummary();
// Retorna array con toda la información
```

---

## 🌐 API de Servicios

### 📊 **9 Servicios Disponibles**

| # | Método | Endpoint | Descripción |
|---|--------|----------|-------------|
| 1 | `GET` | `/api/invoices` | **Listar facturas** con filtros y paginación |
| 2 | `POST` | `/api/invoices` | **Crear nueva factura** |
| 3 | `GET` | `/api/invoices/{id}` | **Mostrar factura específica** |
| 4 | `PUT/PATCH` | `/api/invoices/{id}` | **Actualizar factura** |
| 5 | `DELETE` | `/api/invoices/{id}` | **Eliminar factura** |
| 6 | `PATCH` | `/api/invoices/{id}/status` | **Actualizar solo estado** |
| 7 | `GET` | `/api/invoices/test-report` | **Probar consulta de reporte** |
| 8 | `GET` | `/api/invoices/statistics` | **Estadísticas de facturas** |
| 9 | `GET` | `/api/invoices/export` | **Exportar a Excel** |
| 10 | `PATCH` | `/api/invoices/{id}/cost-center` | **Cambiar centro de costo** |
| 11 | `PATCH` | `/api/invoices/{id}/retention` | **Aplicar/remover retención** |
| 12 | `POST` | `/api/invoices/{id}/upload-files` | **Subir archivos a factura** |
| 13 | `DELETE` | `/api/invoices/{id}/remove-files` | **Eliminar archivos de factura** |

### 🔍 **Filtros Disponibles**
- `status` - Estado de la factura
- `provider_id` - ID del proveedor
- `cost_center_id` - ID del centro de costos
- `overdue` - Facturas vencidas
- `invoice_month` - Mes de la factura
- `invoice_year` - Año de la factura
- `search` - Búsqueda general

### 📊 **Exportación Excel Mejorada**
- ✅ **15 columnas**: Estructura contable profesional
- ✅ **Tipo de Compra**: Columna adicional para Contado/Crédito
- ✅ **URLs de documentos**: Soporte de pago y factura
- ✅ **Formato profesional**: Con estilos y bordes
- ✅ **Colores diferenciados**: Estado y tipo de compra
- ✅ **Filtros**: Mismos que listar facturas

#### **Estructura de Columnas del Excel:**
| Columna | Campo | Descripción |
|---------|-------|-------------|
| A | Número | Número de factura |
| B | Fecha | Fecha de emisión |
| C | antes de iva(Subtotal) | Subtotal antes de impuestos |
| D | IVA | Valor del IVA (19%) |
| E | Aplica Retención | Sí/No |
| F | Valor Pagado | Total de la factura |
| G | Estado | PENDIENTE/PAGADA |
| H | **Tipo de Compra** | **Contado/Crédito** |
| I | Proveedor | Nombre del proveedor |
| J | Centro de Costo | Centro de costos asignado |
| K | Fecha Vencimiento | Fecha de vencimiento |
| L | Método de pago | Método de pago utilizado |
| M | Descripción | Descripción adicional |
| N | Soporte de pago | URL del archivo de soporte |
| O | Factura | URL del archivo de factura |

#### **Colores de Diferenciación:**
- **Estado PAGADA**: Verde
- **Estado PENDIENTE**: Rojo
- **Tipo CONTADO**: Azul
- **Tipo CRÉDITO**: Naranja
- **Retención SÍ**: Rojo
- **Retención NO**: Gris

### 🔗 **Documentación de Endpoints**

#### **1. Listar Facturas**
```http
GET /api/invoices
```
**Parámetros de consulta:**
- `search` - Búsqueda general
- `status` - Estado (PENDIENTE/PAGADA)
- `provider_id` - ID del proveedor
- `cost_center_id` - ID del centro de costos
- `overdue` - Facturas vencidas (true/false)
- `invoice_month` - Mes de la factura (1-12)
- `invoice_year` - Año de la factura
- `sort_by` - Campo de ordenamiento
- `sort_order` - Dirección (asc/desc)
- `per_page` - Elementos por página
- `page` - Número de página

#### **2. Crear Factura**
```http
POST /api/invoices
Content-Type: application/json

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

#### **3. Mostrar Factura**
```http
GET /api/invoices/{id}
```

#### **4. Actualizar Factura**
```http
PUT /api/invoices/{id}
PATCH /api/invoices/{id}
Content-Type: application/json

{
    "invoice_number": "FAC-001-2024",
    "subtotal": 1200.00,
    "status": "PAGADA"
}
```

#### **5. Eliminar Factura**
```http
DELETE /api/invoices/{id}
```

#### **6. Actualizar Estado**
```http
PATCH /api/invoices/{id}/status
Content-Type: application/json

{
    "status": "PAGADA"
}
```

#### **7. Probar Consulta de Reporte**
```http
GET /api/invoices/test-report
```
**Parámetros:** Mismos que listar facturas

#### **8. Estadísticas**
```http
GET /api/invoices/statistics
```

#### **9. Exportar a Excel**
```http
GET /api/invoices/export
```
**Parámetros:** Mismos que listar facturas
**Respuesta:** Archivo Excel descargable

#### **10. Cambiar Centro de Costo**
```http
PATCH /api/invoices/{id}/cost-center
Content-Type: application/json

{
    "cost_center_id": 2
}
```

#### **11. Aplicar/Remover Retención**
```http
PATCH /api/invoices/{id}/retention
Content-Type: application/json

# Aplicar retención
{
    "has_retention": true,
    "retention_amount": 100.00
}

# Remover retención
{
    "has_retention": false
}
```

#### **12. Subir Archivos a Factura**
```http
POST /api/invoices/{id}/upload-files
Content-Type: multipart/form-data
```

**Campos del Formulario:**
- `payment_support` (opcional) - Archivo de soporte de pago (PDF, JPG, JPEG, PNG, máx 10MB)
- `invoice_file` (opcional) - Archivo de la factura (PDF, JPG, JPEG, PNG, máx 10MB)

**Ejemplo de Respuesta:**
```json
{
    "success": true,
    "message": "Archivos subidos exitosamente",
    "data": {
        "invoice": {
            "invoice_id": 1,
            "payment_support": "invoices/payment_support/abc123.pdf",
            "invoice_file": "invoices/invoice_files/def456.pdf"
        },
        "uploaded_files": {
            "payment_support": {
                "path": "invoices/payment_support/abc123.pdf",
                "url": "http://localhost/storage/invoices/payment_support/abc123.pdf",
                "size": 245760,
                "original_name": "comprobante_pago.pdf"
            }
        },
        "file_urls": {
            "payment_support_url": "http://localhost/storage/invoices/payment_support/abc123.pdf",
            "invoice_file_url": "http://localhost/storage/invoices/invoice_files/def456.pdf"
        }
    }
}
```

#### **13. Eliminar Archivos de Factura**
```http
DELETE /api/invoices/{id}/remove-files
Content-Type: application/json
```

**Cuerpo de la Solicitud:**
```json
{
    "file_type": "payment_support" // o "invoice_file" o "both"
}
```

**Ejemplo de Respuesta:**
```json
{
    "success": true,
    "message": "Archivos eliminados exitosamente",
    "data": {
        "invoice": {
            "invoice_id": 1,
            "payment_support": null,
            "invoice_file": "invoices/invoice_files/def456.pdf"
        },
        "removed_files": ["payment_support"]
    }
}
```

### 📋 **Respuestas de la API**

#### **Respuesta Exitosa:**
```json
{
    "success": true,
    "message": "Operación exitosa",
    "data": {
        // Datos de la respuesta
    }
}
```

#### **Respuesta de Error:**
```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": {
        // Detalles de errores de validación
    }
}
```

#### **Códigos de Estado HTTP:**
- `200` - Operación exitosa
- `201` - Recurso creado exitosamente
- `400` - Solicitud incorrecta
- `404` - Recurso no encontrado
- `422` - Error de validación
- `500` - Error interno del servidor

---

## 💡 Ejemplos de Uso

### 📝 Crear Nueva Factura

#### Venta de Contado
```php
// Obtener método de pago
$efMethod = PaymentMethod::getByCode('EF');

$invoice = Invoice::create([
    'invoice_number' => 'FAC-001-2024',
    'invoice_date' => now(),
    'due_date' => now(),
    'subtotal' => 1000.00,
    'retention' => 0,
    'status' => 'PAGADA',
    'sale_type' => 'CONTADO',
    'payment_method_id' => $efMethod->id, // EF - Efectivo
    'provider_id' => 1,
    'cost_center_id' => 1,
    'description' => 'Venta de contado - Pago inmediato'
]);
// El IVA se calcula automáticamente: 190.00
// El total se calcula automáticamente: 1190.00
```

#### Venta a Crédito
```php
$invoice = Invoice::create([
    'invoice_number' => 'FAC-002-2024',
    'invoice_date' => now(),
    'due_date' => now()->addDays(30),
    'subtotal' => 2000.00,
    'retention' => 0,
    'status' => 'PENDIENTE',
    'sale_type' => 'CREDITO',
    'provider_id' => 1,
    'cost_center_id' => 1,
    'description' => 'Venta a crédito - 30 días'
]);
// El IVA se calcula automáticamente: 380.00
// El total se calcula automáticamente: 2380.00
```

### 🔍 Consultas Comunes

#### Consultas con Relaciones
```php
// Cargar facturas con método de pago
$invoices = Invoice::with('paymentMethod')->get();

// Filtrar por método de pago específico
$tcdInvoices = Invoice::whereHas('paymentMethod', function($query) {
    $query->where('code', 'TCD');
})->get();

// Facturas con transferencias
$transferInvoices = Invoice::whereHas('paymentMethod', function($query) {
    $query->whereIn('code', ['TCD', 'CP']);
})->get();
```

#### Verificar Método de Pago
```php
$invoice = Invoice::with('paymentMethod')->find(1);

if ($invoice->isTcdPayment()) {
    echo "Pago por Davivienda TCD";
}

if ($invoice->isTransferPayment()) {
    echo "Pago por transferencia";
}

echo $invoice->getPaymentMethodName(); // Nombre completo
echo $invoice->getPaymentMethodShort(); // Código corto
```

#### Cambiar Centro de Costo
```php
// Cambiar centro de costo de una factura
$invoice = Invoice::find(1);
$oldCostCenter = $invoice->costCenter;

// Cambiar a un nuevo centro de costo
$newCostCenter = CostCenter::find(2);
$invoice->update(['cost_center_id' => $newCostCenter->cost_center_id]);

// Verificar el cambio
$invoice->load('costCenter');
echo "Centro anterior: {$oldCostCenter->name}";
echo "Centro nuevo: {$invoice->costCenter->name}";
```

#### Gestión de Retención Opcional
```php
// Verificar si tiene retención
$hasRetention = $invoice->hasRetentionApplied();

// Aplicar retención
$invoice->applyRetention(100.00); // Monto específico
$invoice->applyRetention(); // Usar monto existente

// Remover retención
$invoice->removeRetention();

// Obtener resumen de retención
$summary = $invoice->getRetentionSummary();
echo "Tiene retención: " . ($summary['has_retention'] ? 'Sí' : 'No');
echo "Monto: $" . number_format($summary['retention_amount'], 2);
echo "Total con retención: $" . number_format($summary['total_with_retention'], 2);
```

#### Reporte de Ventas por Tipo
```php
// Ventas de contado del mes
$cashSales = Invoice::cashSales()
    ->byInvoiceMonth(now()->month)
    ->get();

// Créditos pendientes
$creditPending = Invoice::creditPending()
    ->orderBy('due_date')
    ->get();

// Facturas vencidas
$overdue = Invoice::overdue()
    ->with(['provider', 'costCenter'])
    ->get();
```

#### Dashboard Contable
```php
// Resumen del mes
$monthlySummary = [
    'total_sales' => Invoice::byInvoiceMonth(now()->month)->sum('total_amount'),
    'cash_sales' => Invoice::cashSales()->byInvoiceMonth(now()->month)->sum('total_amount'),
    'credit_sales' => Invoice::creditSales()->byInvoiceMonth(now()->month)->sum('total_amount'),
    'pending_amount' => Invoice::pending()->sum('total_amount'),
    'overdue_count' => Invoice::overdue()->count(),
    'credit_pending' => Invoice::creditPending()->sum('total_amount')
];
```

### 🔄 Actualizar Estado de Pago

#### Marcar Crédito como Pagado
```php
$creditInvoice = Invoice::creditPending()->find(1);

if ($creditInvoice) {
    $creditInvoice->update([
        'status' => 'PAGADA',
        'payment_method' => 'TRANSFERENCIA',
        'payment_support' => 'comprobante_pago.pdf'
    ]);
    
    // Verificar el cambio
    if ($creditInvoice->isCreditPaid()) {
        echo "Crédito marcado como pagado exitosamente";
    }
}
```

---

## 🎯 Casos de Uso Comunes

### 1. **Gestión de Créditos**
```php
// Obtener todos los créditos pendientes ordenados por vencimiento
$creditPending = Invoice::creditPending()
    ->orderBy('due_date')
    ->with(['provider'])
    ->get();

// Obtener créditos próximos a vencer
$dueSoon = Invoice::creditSales()
    ->dueSoon(7)
    ->get();
```

### 2. **Reportes Financieros**
```php
// Ventas por tipo en un período
$salesByType = [
    'contado' => Invoice::cashSales()
        ->byAccountingPeriod($startDate, $endDate)
        ->sum('total_amount'),
    'credito' => Invoice::creditSales()
        ->byAccountingPeriod($startDate, $endDate)
        ->sum('total_amount')
];
```

### 3. **Control de Vencimientos**
```php
// Facturas vencidas por más de 30 días
$severelyOverdue = Invoice::overdue()
    ->where('due_date', '<', now()->subDays(30))
    ->get();
```

### 4. **Análisis de Pagos**
```php
// Métodos de pago más utilizados
$paymentMethods = Invoice::paid()
    ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
    ->groupBy('payment_method')
    ->get();
```

---

## 📚 Notas Importantes

### ⚠️ Consideraciones
- El IVA se calcula automáticamente al establecer el subtotal
- Los totales se calculan con la fórmula: `subtotal + iva_amount - retention`
- Las facturas vencidas solo incluyen las que están pendientes
- Los scopes se pueden combinar para consultas complejas

### 🔧 Mantenimiento
- Revisar periódicamente las facturas vencidas
- Actualizar estados de pago cuando se reciban pagos
- Mantener archivos de soporte organizados
- Generar reportes mensuales de ventas

### 📈 Mejores Prácticas
- Usar números de factura únicos y secuenciales
- Establecer fechas de vencimiento realistas
- Mantener documentación de soporte
- Revisar regularmente el estado de los créditos

---

## 📝 Notas de Versión

### 🆕 **Versión 2.1** (6 de Octubre de 2025)
- ✅ Nueva tabla `payment_methods` normalizada
- ✅ Métodos de pago específicos (TCD, CP, EF)
- ✅ Relaciones foreign key en facturas
- ✅ 9 servicios de API completos
- ✅ Exportación Excel mejorada con URLs
- ✅ Migración de datos preservada
- ✅ Documentación actualizada

### 🆕 **Versión 2.0**
- ✅ Tipos de venta (Contado/Crédito)
- ✅ Estados de pago mejorados
- ✅ Cálculos automáticos
- ✅ Scopes especializados
- ✅ Documentación completa

---

*Documentación del Sistema de Facturas v2.1 - Actualizada 6 de Octubre de 2025*
