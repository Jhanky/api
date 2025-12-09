# 🔧 Guía de Debugging - Reportes de Facturas

## 🚨 Problema Identificado

**Error**: "Factura no encontrada" cuando hay varias facturas en el sistema.

## 🔍 Diagnóstico del Problema

### Posibles Causas:

1. **Relaciones no cargadas**: Las relaciones `provider` y `costCenter` no se están cargando correctamente
2. **Datos nulos**: Algunas facturas tienen campos requeridos como `null`
3. **Filtros incorrectos**: Los filtros aplicados no coinciden con los datos existentes
4. **Problemas de memoria**: El archivo Excel es muy grande para procesar

## 🛠️ Soluciones Implementadas

### 1. **Validaciones Agregadas**

```php
// Verificar que hay facturas para procesar
if ($invoices->isEmpty()) {
    throw new \Exception('No se encontraron facturas con los filtros aplicados');
}

// Validar que la factura existe y tiene datos básicos
if (!$invoice) {
    continue;
}
```

### 2. **Manejo Seguro de Relaciones**

```php
// Manejar relaciones de forma segura
$providerName = '';
if ($invoice->provider) {
    $providerName = $invoice->provider->provider_name ?? '';
}
$sheet->setCellValue('E' . $row, $providerName);

$costCenterName = '';
if ($invoice->costCenter) {
    $costCenterName = $invoice->costCenter->cost_center_name ?? '';
}
$sheet->setCellValue('F' . $row, $costCenterName);
```

### 3. **Logging para Depuración**

```php
// Log para depuración
\Log::info('Facturas encontradas para reporte: ' . $invoices->count());
\Log::info('Filtros aplicados: ' . json_encode($request->all()));
```

### 4. **Manejo de Errores por Factura**

```php
try {
    // Procesar factura individual
} catch (\Exception $e) {
    // Log del error pero continuar con la siguiente factura
    \Log::error('Error procesando factura ID: ' . ($invoice->invoice_id ?? 'desconocido') . ' - ' . $e->getMessage());
    continue;
}
```

## 🧪 Endpoint de Prueba

### URL de Prueba
```
GET /api/invoices/test-report
```

### Parámetros de Prueba
- `status`: PENDIENTE o PAGADA
- `provider_id`: ID del proveedor
- `cost_center_id`: ID del centro de costo
- `month`: Mes (1-12)
- `year`: Año (2020-2030)

### Ejemplo de Uso
```bash
# Probar sin filtros
GET /api/invoices/test-report

# Probar con filtro de estado
GET /api/invoices/test-report?status=PENDIENTE

# Probar con filtro de proveedor
GET /api/invoices/test-report?provider_id=1

# Probar con filtro de mes y año
GET /api/invoices/test-report?month=8&year=2025
```

### Respuesta de Prueba
```json
{
    "success": true,
    "count": 5,
    "filters_applied": {
        "status": "PENDIENTE"
    },
    "data": [
        {
            "id": 1,
            "number": "FAC-001",
            "date": "2025-01-15",
            "amount": 150000.00,
            "status": "PENDIENTE",
            "provider": "SOLPHOWER S.A.S.",
            "cost_center": "Liberman",
            "due_date": "2025-02-15",
            "description": "Factura de servicios"
        }
    ]
}
```

## 🔍 Pasos para Debugging

### 1. **Verificar Datos en Base de Datos**

```sql
-- Verificar que hay facturas
SELECT COUNT(*) FROM invoices;

-- Verificar relaciones
SELECT 
    i.invoice_id,
    i.invoice_number,
    p.provider_name,
    cc.cost_center_name
FROM invoices i
LEFT JOIN providers p ON i.provider_id = p.provider_id
LEFT JOIN cost_centers cc ON i.cost_center_id = cc.cost_center_id
LIMIT 10;
```

### 2. **Probar Endpoint de Prueba**

```bash
# Probar sin filtros
curl -X GET "http://localhost:8000/api/invoices/test-report" \
  -H "Authorization: Bearer tu_token"

# Probar con filtros específicos
curl -X GET "http://localhost:8000/api/invoices/test-report?status=PENDIENTE" \
  -H "Authorization: Bearer tu_token"
```

### 3. **Revisar Logs**

```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Buscar logs específicos
grep "Facturas encontradas para reporte" storage/logs/laravel.log
grep "Error procesando factura" storage/logs/laravel.log
```

### 4. **Verificar Relaciones**

```php
// En tinker o en un controlador temporal
$invoice = Invoice::with(['provider', 'costCenter'])->first();
dd($invoice->provider, $invoice->costCenter);
```

## 🚨 Errores Comunes y Soluciones

### Error 1: "No se encontraron facturas con los filtros aplicados"

**Causa**: Los filtros aplicados no coinciden con ningún registro.

**Solución**:
1. Verificar que existen facturas en la base de datos
2. Probar sin filtros primero
3. Verificar que los IDs de proveedor y centro de costo existen

### Error 2: "Error al generar el archivo Excel"

**Causa**: Problema en el procesamiento de PhpSpreadsheet.

**Solución**:
1. Verificar que PhpSpreadsheet está instalado correctamente
2. Revisar los logs para errores específicos
3. Probar con menos facturas

### Error 3: "Error procesando factura ID: X"

**Causa**: Una factura específica tiene datos corruptos o faltantes.

**Solución**:
1. Revisar la factura específica en la base de datos
2. Verificar que las relaciones existen
3. Corregir los datos faltantes

## 📊 Monitoreo y Métricas

### Logs Importantes a Monitorear

```bash
# Contar facturas procesadas
grep "Facturas encontradas para reporte" storage/logs/laravel.log | tail -10

# Errores de procesamiento
grep "Error procesando factura" storage/logs/laravel.log | tail -10

# Errores de generación
grep "Error generando Excel" storage/logs/laravel.log | tail -10
```

### Métricas de Rendimiento

- **Tiempo de generación**: Monitorear cuánto tiempo toma generar el reporte
- **Memoria utilizada**: Verificar que no se excedan los límites de memoria
- **Facturas procesadas**: Contar cuántas facturas se procesan exitosamente

## 🔧 Configuración Recomendada

### PHP.ini
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
```

### Laravel Config
```php
// En config/app.php
'timezone' => 'America/Bogota',
'locale' => 'es',
```

## 📞 Soporte Técnico

Si el problema persiste:

1. **Recopilar información**:
   - Logs de error completos
   - Número de facturas en la base de datos
   - Filtros aplicados que causan el error

2. **Probar con datos mínimos**:
   - Una sola factura
   - Sin filtros
   - Con datos de prueba

3. **Contactar al equipo de desarrollo** con:
   - Descripción detallada del error
   - Pasos para reproducir
   - Logs relevantes

---

**📅 Última actualización**: Enero 2025  
**🔧 Versión**: 1.1  
**👨‍💻 Desarrollado por**: Equipo de Desarrollo Backend
