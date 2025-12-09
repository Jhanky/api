# 📊 Guía de Usuario - Reportes de Facturas

## 🎯 Descripción General

El sistema de reportes de facturas permite generar archivos Excel profesionales con información detallada de las facturas, aplicando diversos filtros para obtener reportes específicos según las necesidades del usuario.

## 🚀 Cómo Acceder al Servicio

### URL del Endpoint
```
GET /api/invoices/report
```

### Autenticación Requerida
- **Token Bearer** necesario en el header de autorización
- **Ejemplo**: `Authorization: Bearer tu_token_aqui`

## 🔧 Cómo Aplicar Filtros

### 1. **Filtro por Estado de Factura**

#### Facturas Pendientes
```bash
GET /api/invoices/report?status=PENDIENTE
```

#### Facturas Pagadas
```bash
GET /api/invoices/report?status=PAGADA
```

**💡 Casos de Uso:**
- Seguimiento de pagos pendientes
- Análisis de facturas ya procesadas
- Reportes de cobranza

---

### 2. **Filtro por Proveedor**

```bash
GET /api/invoices/report?provider_id=1
```

**💡 Casos de Uso:**
- Análisis de gastos por proveedor específico
- Seguimiento de facturas de un proveedor
- Reportes para auditorías de proveedores

**🔍 Cómo obtener el ID del proveedor:**
```bash
GET /api/providers
```

---

### 3. **Filtro por Centro de Costo**

```bash
GET /api/invoices/report?cost_center_id=2
```

**💡 Casos de Uso:**
- Análisis de gastos por departamento
- Reportes de presupuesto por área
- Seguimiento de costos por proyecto

**🔍 Cómo obtener el ID del centro de costo:**
```bash
GET /api/cost-centers
```

---

### 4. **Filtro por Mes**

```bash
GET /api/invoices/report?month=8
```

**💡 Casos de Uso:**
- Reportes mensuales
- Análisis de gastos por mes
- Seguimiento de facturación mensual

**📅 Valores válidos:** 1-12 (Enero = 1, Diciembre = 12)

---

### 5. **Filtro por Año**

```bash
GET /api/invoices/report?year=2025
```

**💡 Casos de Uso:**
- Reportes anuales
- Análisis de tendencias por año
- Reportes fiscales

**📅 Valores válidos:** 2020-2030

---

### 6. **Filtro por Mes y Año (Combinado)**

```bash
GET /api/invoices/report?month=8&year=2025
```

**💡 Casos de Uso:**
- Reportes específicos de un mes
- Análisis de gastos de agosto 2025
- Seguimiento mensual detallado

---

## 🔄 Combinación de Filtros

### Ejemplos Prácticos

#### 1. **Facturas Pendientes de un Proveedor**
```bash
GET /api/invoices/report?status=PENDIENTE&provider_id=1
```

#### 2. **Facturas Pagadas de un Centro de Costo**
```bash
GET /api/invoices/report?status=PAGADA&cost_center_id=2
```

#### 3. **Todas las Facturas de Agosto 2025**
```bash
GET /api/invoices/report?month=8&year=2025
```

#### 4. **Facturas Pendientes de un Proveedor en Agosto 2025**
```bash
GET /api/invoices/report?status=PENDIENTE&provider_id=1&month=8&year=2025
```

#### 5. **Facturas Pagadas de un Centro de Costo en 2025**
```bash
GET /api/invoices/report?status=PAGADA&cost_center_id=2&year=2025
```

## 📋 Estructura del Reporte Generado

### Columnas del Archivo Excel

| Columna | Descripción | Formato |
|---------|-------------|---------|
| **Número** | Número de la factura | Texto |
| **Fecha** | Fecha de emisión | dd/mm/yyyy |
| **Monto Total** | Valor de la factura | #,##0.00 |
| **Estado** | PENDIENTE o PAGADA | Coloreado |
| **Proveedor** | Nombre del proveedor | Texto |
| **Centro de Costo** | Nombre del centro | Texto |
| **Fecha Vencimiento** | Fecha de vencimiento | dd/mm/yyyy |
| **Descripción** | Descripción de la factura | Texto |

### Características Visuales

- 🎨 **Encabezados**: Fondo azul con texto blanco
- 📊 **Montos**: Formato numérico con separadores
- 🟢 **Estados PAGADA**: Texto verde
- 🔴 **Estados PENDIENTE**: Texto rojo
- 📌 **Primera fila congelada**: Para navegación fácil
- 📏 **Columnas ajustadas**: Ancho optimizado

## 📁 Nombres de Archivo

### Patrón de Nombres
```
reporte_facturas_[filtros]_[fecha_generacion].xlsx
```

### Ejemplos de Nombres

| Filtros Aplicados | Nombre del Archivo |
|-------------------|-------------------|
| Sin filtros | `reporte_facturas_2025-01-15_14-30-25.xlsx` |
| Estado PENDIENTE | `reporte_facturas_pendiente_2025-01-15_14-30-25.xlsx` |
| Proveedor ID 1 | `reporte_facturas_proveedor_solphower-s-a-s_2025-01-15_14-30-25.xlsx` |
| Centro de Costo ID 2 | `reporte_facturas_centro_liberman_2025-01-15_14-30-25.xlsx` |
| Agosto 2025 | `reporte_facturas_2025_08_2025-01-15_14-30-25.xlsx` |
| Combinación | `reporte_facturas_pendiente_proveedor_solphower-s-a-s_2025_08_2025-01-15_14-30-25.xlsx` |

## 🛠️ Ejemplos de Uso con cURL

### 1. **Reporte Básico (Todas las Facturas)**
```bash
curl -X GET "https://tu-dominio.com/api/invoices/report" \
  -H "Authorization: Bearer tu_token_aqui" \
  -H "Accept: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" \
  --output "reporte_facturas.xlsx"
```

### 2. **Facturas Pendientes**
```bash
curl -X GET "https://tu-dominio.com/api/invoices/report?status=PENDIENTE" \
  -H "Authorization: Bearer tu_token_aqui" \
  -H "Accept: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" \
  --output "facturas_pendientes.xlsx"
```

### 3. **Reporte Mensual**
```bash
curl -X GET "https://tu-dominio.com/api/invoices/report?month=8&year=2025" \
  -H "Authorization: Bearer tu_token_aqui" \
  -H "Accept: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" \
  --output "reporte_agosto_2025.xlsx"
```

## 🌐 Ejemplos de Uso con JavaScript/Fetch

### 1. **Función Básica de Descarga**
```javascript
async function descargarReporte(filtros = {}) {
  try {
    const params = new URLSearchParams(filtros);
    const response = await fetch(`/api/invoices/report?${params}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      }
    });
    
    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'reporte_facturas.xlsx';
      a.click();
      window.URL.revokeObjectURL(url);
    }
  } catch (error) {
    console.error('Error al descargar reporte:', error);
  }
}
```

### 2. **Ejemplos de Uso de la Función**
```javascript
// Todas las facturas
descargarReporte();

// Solo facturas pendientes
descargarReporte({ status: 'PENDIENTE' });

// Facturas de un proveedor específico
descargarReporte({ provider_id: 1 });

// Facturas de agosto 2025
descargarReporte({ month: 8, year: 2025 });

// Combinación de filtros
descargarReporte({ 
  status: 'PAGADA', 
  provider_id: 1, 
  month: 8, 
  year: 2025 
});
```

## 📱 Ejemplos de Uso con React

### 1. **Componente de Filtros**
```jsx
import React, { useState } from 'react';

const ReporteFacturas = () => {
  const [filtros, setFiltros] = useState({
    status: '',
    provider_id: '',
    cost_center_id: '',
    month: '',
    year: ''
  });

  const descargarReporte = async () => {
    try {
      const params = new URLSearchParams(
        Object.fromEntries(
          Object.entries(filtros).filter(([_, value]) => value !== '')
        )
      );
      
      const response = await fetch(`/api/invoices/report?${params}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        }
      });
      
      if (response.ok) {
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'reporte_facturas.xlsx';
        a.click();
        window.URL.revokeObjectURL(url);
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  return (
    <div>
      <select 
        value={filtros.status} 
        onChange={(e) => setFiltros({...filtros, status: e.target.value})}
      >
        <option value="">Todos los estados</option>
        <option value="PENDIENTE">Pendientes</option>
        <option value="PAGADA">Pagadas</option>
      </select>
      
      <input 
        type="number" 
        placeholder="ID Proveedor"
        value={filtros.provider_id}
        onChange={(e) => setFiltros({...filtros, provider_id: e.target.value})}
      />
      
      <input 
        type="number" 
        placeholder="Mes (1-12)"
        min="1" 
        max="12"
        value={filtros.month}
        onChange={(e) => setFiltros({...filtros, month: e.target.value})}
      />
      
      <input 
        type="number" 
        placeholder="Año"
        min="2020" 
        max="2030"
        value={filtros.year}
        onChange={(e) => setFiltros({...filtros, year: e.target.value})}
      />
      
      <button onClick={descargarReporte}>
        📊 Descargar Reporte
      </button>
    </div>
  );
};
```

## ⚠️ Consideraciones Importantes

### 1. **Límites y Rendimiento**
- No hay límite en la cantidad de facturas exportadas
- Para reportes grandes, se recomienda usar filtros
- El archivo se genera en tiempo real

### 2. **Formato de Fechas**
- **Entrada**: Año y mes como números
- **Salida**: Fechas en formato dd/mm/yyyy
- **Zona horaria**: UTC

### 3. **Formato de Montos**
- **Entrada**: Números decimales
- **Salida**: Formato #,##0.00 con separadores
- **Moneda**: Sin símbolo de moneda (solo números)

### 4. **Manejo de Errores**
- **400**: Parámetros inválidos
- **401**: Token de autenticación inválido
- **500**: Error interno del servidor

## 🔍 Casos de Uso Comunes

### 1. **Reporte Mensual para Contabilidad**
```bash
GET /api/invoices/report?month=8&year=2025
```
**Uso**: Cierre mensual de contabilidad

### 2. **Seguimiento de Pagos Pendientes**
```bash
GET /api/invoices/report?status=PENDIENTE
```
**Uso**: Lista de facturas por pagar

### 3. **Análisis de Gastos por Proveedor**
```bash
GET /api/invoices/report?provider_id=1&year=2025
```
**Uso**: Evaluación de gastos con proveedor específico

### 4. **Reporte por Centro de Costo**
```bash
GET /api/invoices/report?cost_center_id=2&status=PAGADA
```
**Uso**: Análisis de gastos por departamento

### 5. **Reporte Completo con Filtros**
```bash
GET /api/invoices/report?status=PAGADA&provider_id=1&month=8&year=2025
```
**Uso**: Reporte específico para auditoría

## 📞 Soporte y Ayuda

Si tienes problemas con el servicio de reportes:

1. **Verifica la autenticación**: Asegúrate de tener un token válido
2. **Revisa los parámetros**: Los filtros deben tener valores válidos
3. **Consulta la documentación**: Revisa los endpoints disponibles
4. **Contacta al administrador**: Para problemas técnicos específicos

---

**📅 Última actualización**: Enero 2025  
** Versión**: 1.0  
**👨‍💻 Desarrollado por**: Equipo de Desarrollo Backend
