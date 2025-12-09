# 🔍 Guía de Scopes y Métodos - Sistema de Facturas

## 📋 Índice
1. [Introducción a Scopes](#introducción-a-scopes)
2. [Scopes Básicos](#scopes-básicos)
3. [Scopes por Tipo de Venta](#scopes-por-tipo-de-venta)
4. [Scopes Contables](#scopes-contables)
5. [Métodos de Verificación](#métodos-de-verificación)
6. [Métodos de Cálculo](#métodos-de-cálculo)
7. [Ejemplos Avanzados](#ejemplos-avanzados)
8. [Combinación de Scopes](#combinación-de-scopes)

---

## 🎯 Introducción a Scopes

### ¿Qué son los Scopes?
Los **scopes** son métodos que permiten filtrar y consultar facturas de manera específica. Son como "filtros predefinidos" que puedes usar para obtener exactamente las facturas que necesitas.

### ¿Por qué usar Scopes?
- ✅ **Código más limpio**: Evita consultas SQL complejas
- ✅ **Reutilización**: Los mismos filtros en diferentes partes
- ✅ **Mantenibilidad**: Fácil de modificar y actualizar
- ✅ **Legibilidad**: Código más fácil de entender

### 🔧 Sintaxis Básica
```php
// Usar un scope
$facturas = Invoice::scopeName()->get();

// Combinar scopes
$facturas = Invoice::scope1()->scope2()->get();

// Con parámetros
$facturas = Invoice::scopeWithParam($valor)->get();
```

---

## 📊 Scopes Básicos

### 🕐 Scopes por Estado de Pago

#### `pending()`
**Descripción**: Facturas pendientes de pago
```php
$pendientes = Invoice::pending()->get();
// Retorna: Facturas con status = 'PENDIENTE'
```

#### `paid()`
**Descripción**: Facturas ya pagadas
```php
$pagadas = Invoice::paid()->get();
// Retorna: Facturas con status = 'PAGADA'
```

#### `overdue()`
**Descripción**: Facturas vencidas (pendientes y fecha vencida)
```php
$vencidas = Invoice::overdue()->get();
// Retorna: Facturas pendientes con due_date < hoy
```

#### `dueSoon($days = 7)`
**Descripción**: Facturas próximas a vencer
```php
$proximas = Invoice::dueSoon()->get();        // Próximos 7 días
$proximas = Invoice::dueSoon(15)->get();      // Próximos 15 días
```

### 🏢 Scopes por Relaciones

#### `byProvider($providerId)`
**Descripción**: Facturas de un proveedor específico
```php
$facturas = Invoice::byProvider(1)->get();
// Retorna: Facturas del proveedor con ID 1
```

#### `byCostCenter($costCenterId)`
**Descripción**: Facturas de un centro de costos específico
```php
$facturas = Invoice::byCostCenter(2)->get();
// Retorna: Facturas del centro de costos con ID 2
```

#### `byStatus($status)`
**Descripción**: Facturas con un estado específico
```php
$pendientes = Invoice::byStatus('PENDIENTE')->get();
$pagadas = Invoice::byStatus('PAGADA')->get();
```

---

## 💰 Scopes por Tipo de Venta

### 💵 Scopes de Contado

#### `cashSales()`
**Descripción**: Todas las ventas de contado
```php
$contado = Invoice::cashSales()->get();
// Retorna: Facturas con sale_type = 'CONTADO'
```

#### `cashPaid()`
**Descripción**: Ventas de contado pagadas
```php
$contadoPagado = Invoice::cashPaid()->get();
// Retorna: sale_type = 'CONTADO' AND status = 'PAGADA'
```

### 💳 Scopes de Crédito

#### `creditSales()`
**Descripción**: Todas las ventas a crédito
```php
$credito = Invoice::creditSales()->get();
// Retorna: Facturas con sale_type = 'CREDITO'
```

#### `creditPending()`
**Descripción**: Créditos pendientes de pago
```php
$creditoPendiente = Invoice::creditPending()->get();
// Retorna: sale_type = 'CREDITO' AND status = 'PENDIENTE'
```

#### `creditPaid()`
**Descripción**: Créditos ya pagados
```php
$creditoPagado = Invoice::creditPaid()->get();
// Retorna: sale_type = 'CREDITO' AND status = 'PAGADA'
```

---

## 🧮 Scopes Contables

### 💰 Scopes por Montos

#### `byAmountRange($min, $max)`
**Descripción**: Facturas en un rango de montos
```php
$pequenas = Invoice::byAmountRange(0, 1000)->get();
$medianas = Invoice::byAmountRange(1000, 5000)->get();
$grandes = Invoice::byAmountRange(5000, 999999)->get();
```

### 📅 Scopes por Período

#### `byAccountingPeriod($startDate, $endDate)`
**Descripción**: Facturas en un período contable
```php
$enero = Invoice::byAccountingPeriod('2024-01-01', '2024-01-31')->get();
$trimestre = Invoice::byAccountingPeriod('2024-01-01', '2024-03-31')->get();
```

#### `byInvoiceMonth($month)`
**Descripción**: Facturas de un mes específico
```php
$enero = Invoice::byInvoiceMonth(1)->get();
$diciembre = Invoice::byInvoiceMonth(12)->get();
```

#### `byInvoiceYear($year)`
**Descripción**: Facturas de un año específico
```php
$2024 = Invoice::byInvoiceYear(2024)->get();
$2023 = Invoice::byInvoiceYear(2023)->get();
```

### 🧾 Scopes por Impuestos

#### `withRetention()`
**Descripción**: Facturas con retención
```php
$conRetencion = Invoice::withRetention()->get();
// Retorna: Facturas con retention > 0
```

#### `withoutRetention()`
**Descripción**: Facturas sin retención
```php
$sinRetencion = Invoice::withoutRetention()->get();
// Retorna: Facturas con retention = 0 o NULL
```

#### `withIva()`
**Descripción**: Facturas con IVA
```php
$conIva = Invoice::withIva()->get();
// Retorna: Facturas con iva_amount > 0
```

#### `exemptFromIva()`
**Descripción**: Facturas exentas de IVA
```php
$exentas = Invoice::exemptFromIva()->get();
// Retorna: Facturas con iva_amount = 0 o NULL
```

### 📄 Scopes por Documentos

#### `withPaymentSupport()`
**Descripción**: Facturas con soporte de pago
```php
$conSoporte = Invoice::withPaymentSupport()->get();
// Retorna: Facturas con payment_support NOT NULL
```

#### `withInvoiceFile()`
**Descripción**: Facturas con archivo de factura
```php
$conArchivo = Invoice::withInvoiceFile()->get();
// Retorna: Facturas con invoice_file NOT NULL
```

---

## 🔍 Métodos de Verificación

### 🏷️ Verificación de Tipo de Venta

#### `isCashSale()`
**Descripción**: Verifica si es venta de contado
```php
$invoice = Invoice::find(1);

if ($invoice->isCashSale()) {
    echo "Es una venta de contado";
}
```

#### `isCreditSale()`
**Descripción**: Verifica si es venta a crédito
```php
$invoice = Invoice::find(1);

if ($invoice->isCreditSale()) {
    echo "Es una venta a crédito";
}
```

### 💳 Verificación de Estado de Crédito

#### `isCreditPaid()`
**Descripción**: Verifica si un crédito ya fue pagado
```php
$invoice = Invoice::find(1);

if ($invoice->isCreditPaid()) {
    echo "Este crédito ya fue pagado";
}
```

#### `isCreditPending()`
**Descripción**: Verifica si un crédito está pendiente
```php
$invoice = Invoice::find(1);

if ($invoice->isCreditPending()) {
    echo "Este crédito está pendiente";
}
```

### ⏰ Verificación de Vencimiento

#### `isOverdue()`
**Descripción**: Verifica si la factura está vencida
```php
$invoice = Invoice::find(1);

if ($invoice->isOverdue()) {
    echo "Esta factura está vencida";
}
```

#### `getDaysOverdue()`
**Descripción**: Obtiene los días de vencimiento
```php
$invoice = Invoice::find(1);
$dias = $invoice->getDaysOverdue();

if ($dias > 0) {
    echo "Vencida hace {$dias} días";
} else {
    echo "No está vencida";
}
```

---

## 🧮 Métodos de Cálculo

### 💰 Cálculos Automáticos

#### `calculateIvaAmount()`
**Descripción**: Calcula el IVA (19% del subtotal)
```php
$invoice = Invoice::find(1);
$iva = $invoice->calculateIvaAmount();
echo "IVA: $" . number_format($iva, 2);
```

#### `calculateTotalAmount()`
**Descripción**: Calcula el total (subtotal + IVA - retención)
```php
$invoice = Invoice::find(1);
$total = $invoice->calculateTotalAmount();
echo "Total: $" . number_format($total, 2);
```

#### `getAccountingSummary()`
**Descripción**: Obtiene resumen contable completo
```php
$invoice = Invoice::find(1);
$resumen = $invoice->getAccountingSummary();

echo "Subtotal: $" . $resumen['subtotal'];
echo "IVA: $" . $resumen['iva_amount'];
echo "Retención: $" . $resumen['retention'];
echo "Total: $" . $resumen['total_amount'];
echo "Neto: $" . $resumen['net_amount'];
```

### 📊 Información Descriptiva

#### `getSaleTypeDescription()`
**Descripción**: Obtiene descripción legible del tipo de venta
```php
$invoice = Invoice::find(1);
$descripcion = $invoice->getSaleTypeDescription();
echo $descripcion; // "Venta a Crédito (Pagada)"
```

#### `getSaleTypeSummary()`
**Descripción**: Obtiene resumen completo del tipo de venta
```php
$invoice = Invoice::find(1);
$resumen = $invoice->getSaleTypeSummary();

echo "Tipo: " . $resumen['sale_type'];
echo "Estado: " . $resumen['status'];
echo "Es contado: " . ($resumen['is_cash'] ? 'Sí' : 'No');
echo "Es crédito: " . ($resumen['is_credit'] ? 'Sí' : 'No');
echo "Está pagada: " . ($resumen['is_paid'] ? 'Sí' : 'No');
echo "Está pendiente: " . ($resumen['is_pending'] ? 'Sí' : 'No');
echo "Descripción: " . $resumen['description'];
```

---

## 🚀 Ejemplos Avanzados

### 📊 Dashboard de Ventas

```php
// Resumen mensual completo
$dashboard = [
    // Totales por tipo
    'total_contado' => Invoice::cashSales()
        ->byInvoiceMonth(now()->month)
        ->sum('total_amount'),
    
    'total_credito' => Invoice::creditSales()
        ->byInvoiceMonth(now()->month)
        ->sum('total_amount'),
    
    // Estados
    'pendientes' => Invoice::pending()
        ->byInvoiceMonth(now()->month)
        ->count(),
    
    'pagadas' => Invoice::paid()
        ->byInvoiceMonth(now()->month)
        ->count(),
    
    // Vencimientos
    'vencidas' => Invoice::overdue()->count(),
    'proximas_vencer' => Invoice::dueSoon()->count(),
    
    // Créditos
    'creditos_pendientes' => Invoice::creditPending()->count(),
    'creditos_pagados' => Invoice::creditPaid()->count(),
];
```

### 📈 Reporte de Análisis

```php
// Análisis de cartera de créditos
$analisis = [
    'creditos_por_vencer' => Invoice::creditPending()
        ->where('due_date', '>', now())
        ->orderBy('due_date')
        ->get(),
    
    'creditos_vencidos' => Invoice::creditPending()
        ->overdue()
        ->orderBy('due_date')
        ->get(),
    
    'creditos_cobrados' => Invoice::creditPaid()
        ->byInvoiceMonth(now()->month)
        ->get(),
    
    'monto_pendiente' => Invoice::creditPending()
        ->sum('total_amount'),
    
    'monto_cobrado' => Invoice::creditPaid()
        ->byInvoiceMonth(now()->month)
        ->sum('total_amount'),
];
```

### 🔍 Búsqueda Avanzada

```php
// Facturas de un proveedor específico que están vencidas
$facturasVencidas = Invoice::byProvider(1)
    ->overdue()
    ->with(['provider', 'costCenter'])
    ->get();

// Créditos próximos a vencer de un centro de costos
$creditosProximos = Invoice::byCostCenter(2)
    ->creditSales()
    ->dueSoon(15)
    ->orderBy('due_date')
    ->get();

// Facturas con retención en un período específico
$conRetencion = Invoice::withRetention()
    ->byAccountingPeriod('2024-01-01', '2024-03-31')
    ->orderBy('total_amount', 'desc')
    ->get();
```

---

## 🔗 Combinación de Scopes

### 📋 Reglas de Combinación

#### ✅ Combinaciones Válidas
```php
// Múltiples filtros
$resultado = Invoice::cashSales()
    ->paid()
    ->byInvoiceMonth(1)
    ->get();

// Filtros con parámetros
$resultado = Invoice::creditSales()
    ->byAmountRange(1000, 5000)
    ->dueSoon(10)
    ->get();
```

#### ⚠️ Consideraciones
- Los scopes se ejecutan en secuencia
- Cada scope filtra el resultado del anterior
- Usar `with()` para cargar relaciones
- Usar `orderBy()` para ordenar resultados

### 🎯 Ejemplos de Combinaciones

#### Dashboard Ejecutivo
```php
$dashboard = [
    'ventas_contado_mes' => Invoice::cashSales()
        ->byInvoiceMonth(now()->month)
        ->sum('total_amount'),
    
    'creditos_pendientes' => Invoice::creditPending()
        ->sum('total_amount'),
    
    'facturas_vencidas' => Invoice::overdue()
        ->with(['provider'])
        ->get(),
    
    'proximas_vencer' => Invoice::dueSoon(7)
        ->orderBy('due_date')
        ->get(),
];
```

#### Reporte de Gestión
```php
$reporte = [
    'por_proveedor' => Invoice::byProvider(1)
        ->byInvoiceYear(2024)
        ->get(),
    
    'por_centro_costo' => Invoice::byCostCenter(2)
        ->creditSales()
        ->get(),
    
    'con_retencion' => Invoice::withRetention()
        ->byAccountingPeriod('2024-01-01', '2024-12-31')
        ->get(),
];
```

---

## 📚 Mejores Prácticas

### ✅ Recomendaciones

1. **Usar scopes específicos** en lugar de consultas SQL complejas
2. **Combinar scopes** para filtros múltiples
3. **Cargar relaciones** con `with()` cuando sea necesario
4. **Ordenar resultados** con `orderBy()` para mejor presentación
5. **Limitar resultados** con `limit()` o `paginate()` para grandes volúmenes

### ⚠️ Consideraciones

- **Performance**: Los scopes complejos pueden ser lentos
- **Memoria**: Cargar muchas relaciones consume memoria
- **Índices**: Asegurar índices en campos filtrados frecuentemente
- **Caché**: Considerar caché para consultas repetitivas

### 🔧 Optimización

```php
// ✅ Bueno: Usar scopes específicos
$creditos = Invoice::creditPending()->get();

// ❌ Malo: Consulta SQL compleja
$creditos = Invoice::where('sale_type', 'CREDITO')
    ->where('status', 'PENDIENTE')
    ->get();

// ✅ Bueno: Cargar relaciones necesarias
$facturas = Invoice::overdue()
    ->with(['provider', 'costCenter'])
    ->get();

// ❌ Malo: Cargar todas las relaciones
$facturas = Invoice::overdue()
    ->with(['provider', 'costCenter', 'user', 'items'])
    ->get();
```

---

*Guía de Scopes y Métodos - Sistema de Facturas v2.0*
