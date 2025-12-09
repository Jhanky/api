# 💰 Guía de Tipos de Venta y Estados - Sistema de Facturas

## 📋 Índice
1. [Conceptos Básicos](#conceptos-básicos)
2. [Tipos de Venta](#tipos-de-venta)
3. [Estados de Pago](#estados-de-pago)
4. [Combinaciones y Casos de Uso](#combinaciones-y-casos-de-uso)
5. [Ejemplos Prácticos](#ejemplos-prácticos)
6. [Preguntas Frecuentes](#preguntas-frecuentes)

---

## 🎯 Conceptos Básicos

### ¿Qué es un Tipo de Venta?
El **tipo de venta** determina **cómo se maneja el pago** de la factura:
- **CONTADO**: Se paga inmediatamente
- **CREDITO**: Se paga posteriormente

### ¿Qué es un Estado de Pago?
El **estado de pago** indica **si la factura ya fue pagada**:
- **PENDIENTE**: Aún no se ha pagado
- **PAGADA**: Ya se pagó

### 🔄 ¿Por qué Necesitamos Ambos?
Porque una factura a crédito puede estar:
- **Pendiente**: Se vendió a crédito pero aún no se ha pagado
- **Pagada**: Se vendió a crédito y ya se pagó posteriormente

---

## 🏷️ Tipos de Venta

### 1. **CONTADO** 💵
**Definición**: Venta que se paga inmediatamente al momento de la transacción.

#### Características:
- ✅ Pago inmediato
- ✅ No requiere seguimiento de vencimiento
- ✅ Flujo de caja inmediato
- ✅ Menor riesgo de incobrabilidad

#### Estados Típicos:
- **PAGADA**: Normal (se paga al momento)
- **PENDIENTE**: Excepcional (pago diferido de contado)

#### Ejemplos:
- Venta de productos con pago en efectivo
- Venta con transferencia inmediata
- Venta con tarjeta de débito

### 2. **CREDITO** 💳
**Definición**: Venta que se paga posteriormente, según términos acordados.

#### Características:
- ⏰ Pago diferido
- 📅 Requiere seguimiento de vencimiento
- 🔄 Flujo de caja futuro
- ⚠️ Mayor riesgo de incobrabilidad

#### Estados Posibles:
- **PENDIENTE**: Normal (aún no se ha pagado)
- **PAGADA**: Objetivo (se pagó según términos)

#### Ejemplos:
- Venta con 30 días de plazo
- Venta con cuotas
- Venta a empresas con crédito aprobado

---

## 📊 Estados de Pago

### 1. **PENDIENTE** ⏳
**Definición**: La factura aún no ha sido pagada.

#### Características:
- 🔍 Requiere seguimiento
- 📞 Puede necesitar gestión de cobro
- ⚠️ Riesgo de vencimiento
- 📈 Impacta el flujo de caja

#### Acciones Recomendadas:
- Seguimiento regular
- Recordatorios de pago
- Gestión de cobro si es necesario
- Monitoreo de vencimientos

### 2. **PAGADA** ✅
**Definición**: La factura ya ha sido pagada.

#### Características:
- ✅ Confirmación de pago
- 📁 Archivo de soporte
- 💰 Impacto positivo en flujo de caja
- 📊 Incluida en reportes de ingresos

#### Acciones Recomendadas:
- Archivar documentación
- Generar reportes
- Actualizar estados contables
- Confirmar recepción de fondos

---

## 🔄 Combinaciones y Casos de Uso

### Tabla de Combinaciones

| Tipo de Venta | Estado | Descripción | Uso Común | Ejemplo |
|---------------|--------|-------------|-----------|---------|
| `CONTADO` | `PAGADA` | **Venta de contado pagada** | Pago inmediato | Tienda física, efectivo |
| `CONTADO` | `PENDIENTE` | **Venta de contado pendiente** | Pago diferido | Transferencia pendiente |
| `CREDITO` | `PENDIENTE` | **Venta a crédito pendiente** | Cobro posterior | 30 días de plazo |
| `CREDITO` | `PAGADA` | **Venta a crédito pagada** | Crédito cobrado | Pago recibido |

### 🎯 Casos de Uso Detallados

#### 1. **CONTADO + PAGADA** 💵✅
**Escenario**: Venta con pago inmediato
- **Cuándo usar**: Pago en efectivo, transferencia inmediata
- **Flujo**: Venta → Pago inmediato → Estado PAGADA
- **Beneficios**: Flujo de caja inmediato, sin riesgo

```php
// Ejemplo de creación
$invoice = Invoice::create([
    'sale_type' => 'CONTADO',
    'status' => 'PAGADA',
    'payment_method' => 'EFECTIVO',
    'subtotal' => 1000,
    // IVA y total se calculan automáticamente
]);
```

#### 2. **CONTADO + PENDIENTE** 💵⏳
**Escenario**: Venta de contado con pago diferido
- **Cuándo usar**: Transferencia pendiente, cheque en tránsito
- **Flujo**: Venta → Pago pendiente → Seguimiento → Estado PAGADA
- **Consideraciones**: Seguimiento hasta confirmación

```php
// Ejemplo de creación
$invoice = Invoice::create([
    'sale_type' => 'CONTADO',
    'status' => 'PENDIENTE',
    'payment_method' => 'TRANSFERENCIA',
    'subtotal' => 1000,
]);
```

#### 3. **CREDITO + PENDIENTE** 💳⏳
**Escenario**: Venta a crédito pendiente de pago
- **Cuándo usar**: Plazos de pago, ventas a empresas
- **Flujo**: Venta → Términos de crédito → Seguimiento → Cobro
- **Gestión**: Seguimiento de vencimientos, gestión de cobro

```php
// Ejemplo de creación
$invoice = Invoice::create([
    'sale_type' => 'CREDITO',
    'status' => 'PENDIENTE',
    'due_date' => now()->addDays(30),
    'subtotal' => 2000,
]);
```

#### 4. **CREDITO + PAGADA** 💳✅
**Escenario**: Venta a crédito ya pagada
- **Cuándo usar**: Crédito cobrado según términos
- **Flujo**: Venta → Crédito → Pago recibido → Estado PAGADA
- **Resultado**: Objetivo cumplido, flujo de caja positivo

```php
// Ejemplo de actualización
$creditInvoice = Invoice::find(1);
$creditInvoice->update([
    'status' => 'PAGADA',
    'payment_method' => 'TRANSFERENCIA',
    'payment_support' => 'comprobante.pdf'
]);
```

---

## 💡 Ejemplos Prácticos

### 🏪 Escenario 1: Tienda de Retail

#### Venta de Contado (Efectivo)
```php
// Cliente paga en efectivo
$invoice = Invoice::create([
    'invoice_number' => 'FAC-001-2024',
    'sale_type' => 'CONTADO',
    'status' => 'PAGADA',
    'payment_method' => 'EFECTIVO',
    'subtotal' => 500.00,
    'description' => 'Venta en tienda - Pago efectivo'
]);
// Resultado: Flujo de caja inmediato
```

#### Venta a Crédito (30 días)
```php
// Cliente compra a crédito
$invoice = Invoice::create([
    'invoice_number' => 'FAC-002-2024',
    'sale_type' => 'CREDITO',
    'status' => 'PENDIENTE',
    'due_date' => now()->addDays(30),
    'subtotal' => 1500.00,
    'description' => 'Venta a crédito - 30 días'
]);
// Resultado: Seguimiento de cobro requerido
```

### 🏢 Escenario 2: Empresa B2B

#### Venta a Crédito Empresarial
```php
// Venta a empresa con crédito aprobado
$invoice = Invoice::create([
    'invoice_number' => 'FAC-003-2024',
    'sale_type' => 'CREDITO',
    'status' => 'PENDIENTE',
    'due_date' => now()->addDays(45),
    'subtotal' => 5000.00,
    'description' => 'Venta empresarial - 45 días'
]);
// Resultado: Gestión de cobro empresarial
```

#### Pago Recibido
```php
// Empresa paga la factura
$invoice->update([
    'status' => 'PAGADA',
    'payment_method' => 'TRANSFERENCIA',
    'payment_support' => 'comprobante_empresa.pdf'
]);
// Resultado: Crédito cobrado exitosamente
```

### 🛒 Escenario 3: E-commerce

#### Venta Online (Tarjeta)
```php
// Venta con tarjeta de crédito
$invoice = Invoice::create([
    'invoice_number' => 'FAC-004-2024',
    'sale_type' => 'CONTADO',
    'status' => 'PAGADA',
    'payment_method' => 'TARJETA',
    'subtotal' => 300.00,
    'description' => 'Venta online - Tarjeta de crédito'
]);
// Resultado: Pago procesado inmediatamente
```

---

## ❓ Preguntas Frecuentes

### 🤔 **¿Cuál es la diferencia entre CONTADO y CREDITO?**

**CONTADO**: Se paga inmediatamente al momento de la venta
- Ejemplo: Pago en efectivo, transferencia inmediata
- Flujo: Venta → Pago inmediato

**CREDITO**: Se paga posteriormente según términos acordados
- Ejemplo: 30 días de plazo, cuotas mensuales
- Flujo: Venta → Términos → Pago posterior

### 🤔 **¿Por qué una venta de CONTADO puede estar PENDIENTE?**

Porque el pago puede estar en proceso:
- Transferencia bancaria pendiente de confirmación
- Cheque en tránsito
- Pago con tarjeta pendiente de procesamiento

### 🤔 **¿Cómo sé si un CREDITO ya fue pagado?**

Verificando el estado:
```php
$invoice = Invoice::find(1);

if ($invoice->isCreditPaid()) {
    echo "Este crédito ya fue pagado";
} else {
    echo "Este crédito está pendiente";
}
```

### 🤔 **¿Qué hacer con facturas vencidas?**

```php
// Obtener facturas vencidas
$overdue = Invoice::overdue()->get();

foreach ($overdue as $invoice) {
    // Enviar recordatorio
    // Llamar al cliente
    // Gestión de cobro
}
```

### 🤔 **¿Cómo generar reportes por tipo de venta?**

```php
// Reporte mensual
$monthlyReport = [
    'contado' => Invoice::cashSales()
        ->byInvoiceMonth(now()->month)
        ->sum('total_amount'),
    'credito' => Invoice::creditSales()
        ->byInvoiceMonth(now()->month)
        ->sum('total_amount')
];
```

---

## 📈 Mejores Prácticas

### ✅ Recomendaciones

1. **Usar CONTADO para**:
   - Ventas con pago inmediato
   - Clientes nuevos sin crédito
   - Productos de bajo valor

2. **Usar CREDITO para**:
   - Clientes con crédito aprobado
   - Ventas de alto valor
   - Relaciones comerciales establecidas

3. **Seguimiento de PENDIENTES**:
   - Revisar diariamente
   - Enviar recordatorios
   - Gestionar vencimientos

4. **Archivo de PAGADAS**:
   - Mantener soportes
   - Generar reportes
   - Confirmar recepción

### ⚠️ Consideraciones

- **Riesgo**: Los créditos tienen mayor riesgo de incobrabilidad
- **Flujo de caja**: Los contados mejoran el flujo inmediato
- **Seguimiento**: Los créditos requieren gestión activa
- **Documentación**: Mantener soportes de todos los pagos

---

*Guía de Tipos de Venta y Estados - Sistema de Facturas v2.0*
