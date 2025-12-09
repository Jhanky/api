# 📚 Actualización de Documentación - Sistema de Facturas

## 🆕 Cambios Recientes Implementados

### 📅 **Fecha de Actualización**: 6 de Octubre de 2025

---

## 🔄 **Cambios Principales**

### 1. **Nueva Tabla de Métodos de Pago**

#### **Tabla `payment_methods`**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| `code` | VARCHAR(10) | Código (TCD, CP, EF) |
| `name` | VARCHAR | Nombre completo |
| `description` | TEXT | Descripción detallada |
| `is_active` | BOOLEAN | Si está activo |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Fecha de actualización |

#### **Datos de Métodos de Pago:**
| ID | Código | Nombre | Descripción |
|----|--------|--------|-------------|
| 1 | **TCD** | Transferencia desde cuenta Davivienda E4(TCD) | Transferencia bancaria desde cuenta empresarial Davivienda E4 |
| 2 | **CP** | Transferencia desde Cuenta personal(CP) | Transferencia bancaria desde cuenta personal |
| 3 | **EF** | Efectivo(EF) | Pago en efectivo |

### 2. **Actualización de Tabla `invoices`**

#### **Cambios en la Estructura:**
- ✅ **Nueva columna**: `payment_method_id` (foreign key)
- ❌ **Eliminada**: `payment_method` (enum anterior)
- ✅ **Relación**: `belongsTo(PaymentMethod::class)`

#### **Nueva Estructura de Campos:**
```php
protected $fillable = [
    'invoice_number',
    'invoice_date', 
    'due_date',
    'provider_id',
    'cost_center_id',
    'subtotal',
    'iva_amount',
    'retention',
    'total_amount',
    'status',
    'sale_type',
    'payment_method_id',  // ← NUEVO: Foreign key
    'payment_support',
    'invoice_file',
    'description'
];
```

### 3. **Nuevo Modelo PaymentMethod**

#### **Funcionalidades Implementadas:**
```php
// Relaciones
public function invoices(): HasMany

// Scopes
public function scopeActive($query)
public function scopeByCode($query, $code)

// Métodos estáticos
public static function getByCode($code)
public static function getActiveMethods()
public static function getOptions()
public static function getOptionsWithCodes()
```

#### **Ejemplo de Uso:**
```php
// Obtener método por código
$tcdMethod = PaymentMethod::getByCode('TCD');

// Crear factura con método específico
$invoice = Invoice::create([
    'payment_method_id' => $tcdMethod->id,
    // otros campos...
]);
```

### 4. **Modelo Invoice Actualizado**

#### **Nueva Relación:**
```php
public function paymentMethod()
{
    return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
}
```

#### **Métodos Actualizados:**
```php
// Verificación de métodos de pago
public function isTcdPayment()      // Verifica si es TCD
public function isCpPayment()       // Verifica si es CP
public function isEfPayment()       // Verifica si es EF
public function isTransferPayment() // Verifica si es transferencia

// Información del método
public function getPaymentMethodShort()  // Código corto (TCD, CP, EF)
public function getPaymentMethodName()   // Nombre completo
public function getPaymentMethodSummary() // Resumen completo
```

### 5. **API de Servicios Actualizada**

#### **9 Servicios Disponibles:**
| # | Método | Endpoint | Descripción |
|---|--------|----------|-------------|
| 1 | `GET` | `/api/invoices` | Listar facturas con filtros |
| 2 | `POST` | `/api/invoices` | Crear nueva factura |
| 3 | `GET` | `/api/invoices/{id}` | Mostrar factura específica |
| 4 | `PUT/PATCH` | `/api/invoices/{id}` | Actualizar factura |
| 5 | `DELETE` | `/api/invoices/{id}` | Eliminar factura |
| 6 | `PATCH` | `/api/invoices/{id}/status` | Actualizar solo estado |
| 7 | `GET` | `/api/invoices/test-report` | Probar consulta de reporte |
| 8 | `GET` | `/api/invoices/statistics` | Estadísticas de facturas |
| 9 | `GET` | `/api/invoices/export` | Exportar a Excel |

#### **Validaciones Actualizadas:**
```php
// Antes (enum)
'payment_method' => 'nullable|in:EFECTIVO,TRANSFERENCIA,CHEQUE,TARJETA,OTRO'

// Ahora (foreign key)
'payment_method_id' => 'nullable|exists:payment_methods,id'
```

### 6. **Exportación Excel Mejorada**

#### **Nueva Estructura de Columnas:**
| Columna | Campo | Descripción |
|---------|-------|-------------|
| A | Número | Número de factura |
| B | Fecha | Fecha de emisión |
| C | antes de iva(Subtotal) | Subtotal antes de impuestos |
| D | IVA | Valor del IVA (19%) |
| E | Retencion | Retención en la fuente |
| F | Valor Pagado | Total a pagar |
| G | Estado | Estado de pago |
| H | Proveedor | Nombre del proveedor |
| I | Centro de Costo | Nombre del centro de costos |
| J | Fecha Vencimiento | Fecha límite de pago |
| K | Metodo de pago | Método de pago utilizado |
| L | Descripción | Notas adicionales |
| M | Soporte de pago | **URL del archivo de soporte** |
| N | Factura | **URL del archivo de factura** |

---

## 🚀 **Beneficios de los Cambios**

### 1. **Normalización de Datos**
- ✅ **Consistencia**: Un solo lugar para métodos de pago
- ✅ **Integridad**: Foreign key constraints
- ✅ **Flexibilidad**: Fácil agregar nuevos métodos

### 2. **Mejor Rendimiento**
- ✅ **Consultas optimizadas**: JOIN en lugar de ENUM
- ✅ **Índices**: Búsquedas más rápidas
- ✅ **Relaciones**: Carga eficiente con `with()`

### 3. **Mantenibilidad**
- ✅ **Centralizado**: Cambios en un solo lugar
- ✅ **Escalable**: Fácil agregar nuevos métodos
- ✅ **Documentado**: Descripciones detalladas

---

## 📊 **Ejemplos de Uso Actualizados**

### **Crear Factura con Nuevo Sistema:**
```php
// Obtener método de pago
$tcdMethod = PaymentMethod::getByCode('TCD');

// Crear factura
$invoice = Invoice::create([
    'invoice_number' => 'FAC-001-2024',
    'invoice_date' => now(),
    'due_date' => now(),
    'subtotal' => 1000.00,
    'status' => 'PAGADA',
    'sale_type' => 'CONTADO',
    'payment_method_id' => $tcdMethod->id,  // ← NUEVO
    'provider_id' => 1,
    'cost_center_id' => 1
]);
```

### **Consultar Facturas con Relaciones:**
```php
// Cargar con relación
$invoices = Invoice::with('paymentMethod')->get();

// Filtrar por método de pago
$tcdInvoices = Invoice::whereHas('paymentMethod', function($query) {
    $query->where('code', 'TCD');
})->get();
```

### **Verificar Método de Pago:**
```php
$invoice = Invoice::with('paymentMethod')->find(1);

if ($invoice->isTcdPayment()) {
    echo "Pago por Davivienda TCD";
}

echo $invoice->getPaymentMethodName(); // Nombre completo
echo $invoice->getPaymentMethodShort(); // Código corto
```

---

## 🔧 **Migración de Datos**

### **Proceso de Migración:**
1. ✅ **Crear tabla `payment_methods`**
2. ✅ **Insertar métodos de pago**
3. ✅ **Agregar columna `payment_method_id`**
4. ✅ **Migrar datos existentes**
5. ✅ **Eliminar columna `payment_method`**
6. ✅ **Establecer foreign key**

### **Datos Preservados:**
- ✅ **Todas las facturas existentes migradas**
- ✅ **Relaciones establecidas correctamente**
- ✅ **Validaciones actualizadas**
- ✅ **Sistema funcionando**

---

## 📈 **Próximos Pasos**

### **Documentación Pendiente:**
- [ ] Actualizar documentación principal
- [ ] Crear guía de migración
- [ ] Documentar nuevos endpoints
- [ ] Ejemplos de uso actualizados

### **Mejoras Futuras:**
- [ ] Agregar más métodos de pago
- [ ] Reportes por método de pago
- [ ] Analytics avanzados
- [ ] Notificaciones automáticas

---

## 📞 **Soporte**

### **Para Desarrolladores:**
- Revisar las migraciones ejecutadas
- Verificar las relaciones en los modelos
- Probar los nuevos endpoints
- Validar la exportación Excel

### **Para Usuarios:**
- Los métodos de pago ahora son más específicos
- La exportación Excel incluye URLs de documentos
- Las consultas son más eficientes
- El sistema es más escalable

---

*Documentación de Actualización - Sistema de Facturas v2.1 - 6 de Octubre de 2025*
