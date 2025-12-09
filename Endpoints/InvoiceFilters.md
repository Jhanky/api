## Listado de Facturas - Filtros y Parámetros

### Endpoint
`GET /api/invoices`

### Parámetros de consulta (query string)
- **status**: `PENDIENTE` | `PAGADA`
- **provider_id**: ID del proveedor
- **cost_center_id**: ID del centro de costo
- **invoice_month**: mes de la factura `1..12`
- **invoice_year**: año de la factura `YYYY` (opcional, recomendado junto con `invoice_month`)
- **search**: texto libre (número de factura, proveedor o centro de costo)
- **overdue**: `1` para solo vencidas
- **sort_by**: campo de orden (por defecto `invoice_date`)
- **sort_order**: `asc` | `desc` (por defecto `desc`)
- **per_page**: tamaño de página (por defecto `15`)

### Reglas y notas
- `invoice_month` filtra por el mes del campo `invoice_date`.
- `invoice_year` filtra por el año de `invoice_date`. Úsalo junto a `invoice_month` para acotar un mes específico de un año.
- `overdue=1` devuelve facturas con `due_date` anterior a hoy y `status=PENDIENTE`.

### Ejemplos
Listado básico paginado:
```
GET /api/invoices
```

Con los 4 filtros solicitados:
```
GET /api/invoices?status=PENDIENTE&provider_id=3&cost_center_id=2&invoice_month=9&invoice_year=2025
```

Con búsqueda, vencidas y ordenamiento:
```
GET /api/invoices?search=FAC-2025&overdue=1&sort_by=invoice_date&sort_order=desc&per_page=20
```

### Respuesta (200) - estructura general
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "invoice_id": 1,
        "invoice_number": "FAC-001-2025",
        "invoice_date": "2025-09-10",
        "due_date": "2025-09-25",
        "total_amount": "1500000.00",
        "status": "PENDIENTE",
        "provider_id": 3,
        "cost_center_id": 2,
        "provider": { /* ... */ },
        "cost_center": { /* ... */ }
      }
    ],
    "per_page": 15,
    "total": 8
  }
}
```


