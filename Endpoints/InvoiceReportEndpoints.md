# Endpoints de Reportes de Facturas

## Generar Reporte de Facturas

### Endpoint
```
GET /api/invoices/report
```

### Descripción
Genera un reporte de facturas en formato Excel (.xlsx) con filtros opcionales. El archivo se descarga automáticamente con formato profesional y estilos aplicados.

### Parámetros de Filtro (Query Parameters)

| Parámetro | Tipo | Requerido | Descripción | Valores Válidos |
|-----------|------|-----------|-------------|-----------------|
| `status` | string | No | Filtrar por estado de la factura | `PENDIENTE`, `PAGADA` |
| `provider_id` | integer | No | Filtrar por proveedor específico | ID del proveedor |
| `cost_center_id` | integer | No | Filtrar por centro de costo específico | ID del centro de costo |
| `month` | integer | No | Filtrar por mes de la factura | 1-12 |
| `year` | integer | No | Filtrar por año de la factura | 2020-2030 |

### Ejemplos de Uso

#### 1. Reporte de todas las facturas
```
GET /api/invoices/report
```

#### 2. Reporte de facturas pendientes
```
GET /api/invoices/report?status=PENDIENTE
```

#### 3. Reporte de facturas pagadas
```
GET /api/invoices/report?status=PAGADA
```

#### 4. Reporte por proveedor específico
```
GET /api/invoices/report?provider_id=1
```

#### 5. Reporte por centro de costo específico
```
GET /api/invoices/report?cost_center_id=2
```

#### 6. Reporte por mes y año
```
GET /api/invoices/report?month=8&year=2025
```

#### 7. Combinación de filtros
```
GET /api/invoices/report?status=PAGADA&provider_id=1&month=8&year=2025
```

### Estructura del Archivo Excel

El archivo Excel (.xlsx) generado contiene las siguientes columnas con formato profesional:

| Columna | Descripción |
|---------|-------------|
| Número | Número de la factura |
| Fecha | Fecha de la factura (formato: dd/mm/yyyy) |
| Monto Total | Monto total de la factura (formato: 1.234.567,89) |
| Estado | Estado de la factura (PENDIENTE/PAGADA) |
| Proveedor | Nombre del proveedor |
| Centro de Costo | Nombre del centro de costo |
| Fecha Vencimiento | Fecha de vencimiento (formato: dd/mm/yyyy) |
| Descripción | Descripción de la factura |

### Características del Archivo Excel

El archivo Excel generado incluye las siguientes características profesionales:

#### 🎨 **Formato Visual:**
- **Encabezados**: Fondo azul con texto blanco y negrita
- **Bordes**: Bordes delgados en todas las celdas
- **Ancho de columnas**: Ajustado automáticamente para mejor legibilidad
- **Primera fila congelada**: Para facilitar el desplazamiento
- **Colores de estado**: Verde para "PAGADA", Rojo para "PENDIENTE"

#### 📊 **Formato de Datos:**
- **Montos**: Formato numérico con separadores de miles (#,##0.00)
- **Fechas**: Formato dd/mm/yyyy
- **Estados**: Coloreados según su valor
- **Título de hoja**: "Reporte de Facturas"

### Nombres de Archivo

El nombre del archivo se genera automáticamente basado en los filtros aplicados:

- **Sin filtros**: `reporte_facturas_2025-01-15_14-30-25.xlsx`
- **Con estado**: `reporte_facturas_pendiente_2025-01-15_14-30-25.xlsx`
- **Con proveedor**: `reporte_facturas_proveedor_solphower-s-a-s_2025-01-15_14-30-25.xlsx`
- **Con centro de costo**: `reporte_facturas_centro_liberman_2025-01-15_14-30-25.xlsx`
- **Con fecha**: `reporte_facturas_2025_08_2025-01-15_14-30-25.xlsx`

### Respuesta

- **Tipo de contenido**: `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- **Disposición**: `attachment` (descarga automática)
- **Formato**: Excel nativo (.xlsx)

### Autenticación

Este endpoint requiere autenticación mediante token Bearer:

```
Authorization: Bearer {token}
```

### Ejemplos de Respuesta

#### Éxito
El endpoint retorna directamente el archivo CSV para descarga.

#### Error de Validación
```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "status": ["El campo status debe ser PENDIENTE o PAGADA."],
        "month": ["El campo month debe ser un número entre 1 y 12."]
    }
}
```

#### Error del Servidor
```json
{
    "success": false,
    "message": "Error al generar reporte",
    "error": "Mensaje de error específico"
}
```

### Notas Importantes

1. **Filtros Combinables**: Todos los filtros pueden combinarse para obtener reportes más específicos.

2. **Ordenamiento**: Las facturas se ordenan por fecha de factura (más recientes primero).

3. **Formato de Fechas**: Las fechas se muestran en formato dd/mm/yyyy para mejor legibilidad.

4. **Formato de Montos**: Los montos se formatean automáticamente con separadores de miles y decimales.

5. **Archivo Excel Nativo**: El archivo se genera en formato .xlsx nativo de Excel con todas las características profesionales.

6. **Estilos Aplicados**: El archivo incluye formato profesional con colores, bordes y estilos automáticos.

7. **Límites**: No hay límite en la cantidad de facturas que se pueden exportar, pero se recomienda usar filtros para reportes grandes.

8. **Compatibilidad**: El archivo es compatible con Excel, LibreOffice, Google Sheets y otros lectores de Excel.

### Casos de Uso Comunes

1. **Reporte Mensual**: Filtrar por mes y año para obtener todas las facturas de un período específico.

2. **Reporte por Proveedor**: Filtrar por proveedor para analizar facturas de un proveedor específico.

3. **Reporte por Centro de Costo**: Filtrar por centro de costo para análisis de gastos por departamento.

4. **Reporte de Pendientes**: Filtrar por estado PENDIENTE para seguimiento de pagos.

5. **Reporte de Pagadas**: Filtrar por estado PAGADA para análisis de pagos realizados.
