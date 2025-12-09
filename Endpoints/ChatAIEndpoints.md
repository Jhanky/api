# Sistema de IA para Estimaciones Rápidas de Sistemas Fotovoltaicos

## 📋 Descripción General

El sistema de IA/Chat es una **herramienta de estimación rápida** que permite obtener valores aproximados de sistemas fotovoltaicos sin necesidad de autenticación. Está diseñado para proporcionar estimaciones iniciales que posteriormente se pueden detallar usando el sistema normal de cotizaciones.

### 🎯 Propósito Principal
- **Estimación Rápida**: Obtener valores aproximados en segundos
- **Datos Mínimos**: Solo requiere información básica del cliente y sistema
- **Sin Autenticación**: Acceso público para integración con chatbots, apps móviles, etc.
- **Siguiente Paso**: Usar el sistema normal de cotizaciones para detallar

### 🔄 Flujo de Trabajo Recomendado
1. **Estimación Rápida** → Usar endpoints de IA para obtener valor aproximado
2. **Cotización Detallada** → Usar sistema normal de cotizaciones para detallar

## 🌐 Configuración de API

### Base URL
```
http://localhost:8000/api/chat/ia
```

### Autenticación
**🔓 Sin autenticación requerida** - Estos endpoints son públicos y pueden ser utilizados por cualquier sistema externo.

### Headers Recomendados
```json
{
    "Content-Type": "application/json",
    "User-Agent": "[Nombre del Sistema]",
    "X-Source": "[Origen de la Solicitud]"
}
```

## 🚀 Endpoints Disponibles

El sistema cuenta con **4 endpoints principales** organizados de manera simple y eficiente:

### 1. 📝 Crear Estimación Rápida

**POST** `/api/chat/ia/create`

Crea una estimación rápida de un sistema fotovoltaico con datos básicos. El sistema aplica automáticamente utilidades y crea items estándar.

#### 📋 Campos Requeridos:

**👤 Datos del Cliente:**
- `client_name` (string, max:255): Nombre completo del cliente

**📍 Datos de Ubicación:**
- `location_department` (string, max:100): Departamento de la instalación
- `location_municipality` (string, max:100): Municipio de la instalación  
- `location_radiation` (numeric, 0-10): Radiación solar en kWh/m²/día

**⚡ Datos del Sistema:**
- `project_name` (string, max:255): Nombre del proyecto
- `system_type` (enum): Tipo de sistema - `On-grid`, `Off-grid`, `Híbrido`
- `power_kwp` (numeric, 0.1-1000): Potencia del sistema en kWp
- `panel_count` (integer, 1-10000): Cantidad de paneles

**🔧 Productos Utilizados:**
- `products` (array, min:2): Array de productos con estructura:
  - `product_type` (enum): `panel`, `inverter`, `battery`
  - `product_id` (integer, min:1): ID del producto en el catálogo
  - `quantity` (integer, min:1): Cantidad del producto
  - `unit_price` (numeric, min:0): **Precio SIN utilidad** (se aplica automáticamente 25%)

#### 💡 Ejemplo de Request:

```json
{
    "client_name": "María González",
    "location_department": "Antioquia",
    "location_municipality": "Medellín",
    "location_radiation": 5.2,
    "project_name": "Sistema Residencial María González",
    "system_type": "On-grid",
    "power_kwp": 6.0,
    "panel_count": 15,
    "products": [
        {
            "product_type": "panel",
            "product_id": 1,
            "quantity": 15,
            "unit_price": 960000
        },
        {
            "product_type": "inverter",
            "product_id": 3,
            "quantity": 1,
            "unit_price": 6400000
        }
    ]
}
```

#### ✅ Ejemplo de Response (Éxito):

```json
{
    "success": true,
    "message": "Cotización creada exitosamente desde IA",
    "data": {
        "quotation": {
            "quotation_id": 10,
            "client_id": 3,
            "user_id": null,
            "project_name": "Sistema Residencial María González",
            "system_type": "On-grid",
            "power_kwp": "6.00",
            "panel_count": 15,
            "total_value": "40941591.19",
            "status_id": 1,
            "client": {
                "client_id": 3,
                "nic": "IA_1756994965_9533",
                "name": "María González",
                "department": "Antioquia",
                "city": "Medellín"
            },
            "used_products": [
                {
                    "used_product_id": 16,
                    "product_type": "panel",
                    "product_id": 1,
                    "quantity": 15,
                    "unit_price": "1200000.00",
                    "total_value": "18000000.00"
                }
            ],
            "items": [
                {
                    "item_id": 27,
                    "description": "Conductor fotovoltaico",
                    "quantity": "72.00",
                    "unit_price": "4047.00",
                    "total_value": "364230.00"
                }
            ]
        },
        "quotation_id": 10,
        "client_id": 3,
        "location_id": 1127,
        "total_products": 2,
        "total_items": 5,
        "created_at": "2025-09-04 14:09:25"
    }
}
```

#### ❌ Ejemplo de Response (Error):

```json
{
    "success": false,
    "message": "Datos de validación incorrectos",
    "errors": {
        "client_name": [
            "El campo client name es obligatorio."
        ],
        "power_kwp": [
            "El campo power kwp debe ser un número mayor que 0."
        ]
    }
}
```

### 2. 📋 Listar Estimaciones de IA

**GET** `/api/chat/ia/list`

Obtiene las últimas 50 estimaciones creadas por IA (identificadas por `user_id = null`).

#### ✅ Ejemplo de Response:

```json
{
    "success": true,
    "data": [
        {
            "quotation_id": 10,
            "client_id": 3,
            "user_id": null,
            "project_name": "Sistema Residencial María González",
            "system_type": "On-grid",
            "power_kwp": "6.00",
            "panel_count": 15,
            "total_value": "40941591.19",
            "status_id": 1,
            "created_at": "2025-09-04T14:09:25.000000Z",
            "updated_at": "2025-09-04T14:09:25.000000Z",
            "client": {
                "client_id": 3,
                "nic": "IA_1756994965_9533",
                "name": "María González",
                "department": "Antioquia",
                "city": "Medellín"
            },
            "status": {
                "status_id": 1,
                "name": "Pendiente",
                "description": "Cotización en estado inicial",
                "color": "#F59E0B"
            },
            "used_products": [
                {
                    "used_product_id": 16,
                    "product_type": "panel",
                    "product_id": 1,
                    "quantity": 15,
                    "unit_price": "1200000.00",
                    "total_value": "18000000.00"
                }
            ],
            "items": [
                {
                    "item_id": 27,
                    "description": "Conductor fotovoltaico",
                    "quantity": "72.00",
                    "unit_price": "4047.00",
                    "total_value": "364230.00"
                }
            ]
        }
    ]
}
```

### 3. 🗑️ Eliminar Estimación de IA

**DELETE** `/api/chat/ia/delete/{id}`

Elimina una estimación específica creada por IA. Solo permite eliminar cotizaciones con `user_id = null`.

#### ✅ Ejemplo de Response:

```json
{
    "success": true,
    "message": "Estimación eliminada exitosamente"
}
```

#### ❌ Ejemplo de Response (Error):

```json
{
    "success": false,
    "message": "Cotización no encontrada o no es de IA"
}
```

### 4. ℹ️ Información de Productos Disponibles

**GET** `/api/chat/ia/info`

Obtiene la lista de productos disponibles para crear estimaciones.

#### ✅ Ejemplo de Response:

```json
{
    "success": true,
    "data": {
        "panels": [
            {
                "panel_id": 1,
                "brand": "Canadian Solar",
                "model": "CS6K-300MS",
                "power": "300 W",
                "type": "Monocristalino",
                "price": "1200000.00"
            }
        ],
        "inverters": [
            {
                "inverter_id": 1,
                "brand": "Fronius",
                "model": "Primo 8.0-1",
                "power": "8000 W",
                "system_type": "String",
                "price": "9800000.00"
            }
        ],
        "batteries": [
            {
                "battery_id": 1,
                "brand": "Tesla",
                "model": "Powerwall 2",
                "capacity": "13.5 kWh",
                "voltage": "48V",
                "price": "25000000.00"
            }
        ]
    }
}
```

## 🔧 Características Especiales

### 1. 🚀 Estimación Rápida Simplificada

- **Propósito**: Obtener valores aproximados de sistemas fotovoltaicos de manera rápida
- **Datos mínimos**: Solo requiere información básica del cliente y sistema
- **Resultado**: Estimación con productos e items estándar automáticos
- **Siguiente paso**: Usar el sistema normal de cotizaciones para detallar

### 2. 👤 Gestión Automática de Clientes y Ubicaciones

- **Cliente**: Se crea automáticamente con solo el nombre. El NIC se genera automáticamente con formato `IA_[timestamp]_[random]`. Los datos de departamento y ciudad se toman de la ubicación de instalación. Se guarda como cliente normal en la tabla `clients`.
- **Ubicación**: Se crea nueva cada vez con los datos proporcionados en la tabla `locations`.

### 3. 📊 Estado Inicial de Cotización

- Todas las cotizaciones creadas por IA se crean con `status_id = 1` (Pendiente)
- Se guardan como cotizaciones normales en la tabla `quotations`
- El campo `user_id` se establece como `null` para identificar que fue creada por IA
- Se aplican todos los cálculos automáticos de la cotización (IVA, gestión comercial, etc.)

### 4. ✅ Validaciones Automáticas

- Verificación de que los productos existan en el catálogo
- Validación de que las cantidades y precios sean coherentes
- Verificación de que el NIC del cliente sea único
- Validación de rangos para potencia y radiación

### 5. 🔒 Transacciones de Base de Datos

- Todas las operaciones se ejecutan dentro de una transacción
- Si algo falla, se revierten todos los cambios
- Garantiza la integridad de los datos

### 6. 💰 Porcentajes de Utilidad

**Productos (Paneles, Inversores, Baterías):**
- Utilidad estándar: **25%** (se aplica automáticamente)
- Los precios enviados en `unit_price` son SIN utilidad
- El sistema aplica automáticamente el 25% de utilidad

**Items Estándar Automáticos:**
- Conductor fotovoltaico: **25%**
- Cableado fotovoltaico: **25%**
- Estructura de soporte: **25%**
- Mano de obra instalación: **25%**
- **Costo de legalización: 5%** (diferente al resto)

## 🎯 Casos de Uso

### 1. 🤖 Chatbot de Cotizaciones

```bash
# El chatbot puede crear estimaciones automáticamente
POST /api/chat/ia/create
{
    "client_name": "Cliente del Chat",
    "location_department": "Antioquia",
    "location_municipality": "Medellín",
    "location_radiation": 5.2,
    "project_name": "Sistema Chatbot",
    "system_type": "On-grid",
    "power_kwp": 3.0,
    "panel_count": 8,
    "products": [
        {
            "product_type": "panel",
            "product_id": 1,
            "quantity": 8,
            "unit_price": 960000
        },
        {
            "product_type": "inverter",
            "product_id": 1,
            "quantity": 1,
            "unit_price": 5000000
        }
    ]
}
```

### 2. 🔗 Integración con IA Externa

```bash
# Sistema de IA puede obtener productos disponibles
GET /api/chat/ia/info

# Luego crear estimación con productos seleccionados
POST /api/chat/ia/create
```

### 3. 📱 Aplicaciones Móviles

```bash
# App móvil puede crear estimaciones sin login
POST /api/chat/ia/create
```

### 4. 🎯 Sistemas de Lead Generation

```bash
# Sistema captura leads y crea estimaciones automáticamente
POST /api/chat/ia/create
```

## ⚠️ Códigos de Error

### Errores Comunes:

- **422**: Datos de validación incorrectos
- **404**: Recurso no encontrado (para DELETE)
- **500**: Error interno del servidor

### Ejemplos de Errores:

```json
{
    "success": false,
    "message": "Error al crear cotización desde IA",
    "error": "SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails"
}
```

## 📝 Notas Importantes

1. **Sin Autenticación**: Estos endpoints son públicos y no requieren token
2. **Validaciones Completas**: Se validan todos los datos antes de crear la cotización
3. **Transacciones Seguras**: Uso de transacciones de base de datos para garantizar integridad
4. **Gestión Automática**: Clientes y ubicaciones se crean/actualizan automáticamente
5. **Identificación de IA**: Las cotizaciones creadas por IA tienen `user_id = null`
6. **Estado Inicial**: Todas las cotizaciones se crean como "Pendiente"
7. **Validez Automática**: Se establece validez de 30 días automáticamente
8. **Límite de Productos**: Mínimo 1 producto, máximo según capacidad del sistema

## 🔒 Seguridad

- **Validación Estricta**: Todos los datos de entrada se validan exhaustivamente
- **Sanitización**: Los datos se procesan de forma segura
- **Transacciones**: Uso de transacciones para prevenir datos inconsistentes
- **Límites**: Validación de rangos para valores críticos (potencia, radiación, etc.)

## 📊 Integración con Sistemas Externos

### Rate Limiting:

- Se recomienda no exceder 10 solicitudes por minuto por IP
- Para uso intensivo, contactar al administrador del sistema

### Logs y Monitoreo:

- Todas las cotizaciones creadas por IA se registran en los logs del sistema
- Se pueden rastrear a través del campo `user_id = null`
- Se recomienda monitorear el uso para detectar patrones anómalos

## 🚀 Ejemplos de Uso Rápido

### Postman Collection:

```json
{
    "info": {
        "name": "Sistema IA Estimaciones",
        "description": "Endpoints para estimaciones rápidas de sistemas fotovoltaicos"
    },
    "item": [
        {
            "name": "Crear Estimación",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"client_name\": \"Cliente Prueba\",\n    \"location_department\": \"Antioquia\",\n    \"location_municipality\": \"Medellín\",\n    \"location_radiation\": 5.2,\n    \"project_name\": \"Sistema Prueba\",\n    \"system_type\": \"On-grid\",\n    \"power_kwp\": 3.0,\n    \"panel_count\": 8,\n    \"products\": [\n        {\n            \"product_type\": \"panel\",\n            \"product_id\": 1,\n            \"quantity\": 8,\n            \"unit_price\": 960000\n        },\n        {\n            \"product_type\": \"inverter\",\n            \"product_id\": 1,\n            \"quantity\": 1,\n            \"unit_price\": 5000000\n        }\n    ]\n}"
                },
                "url": {
                    "raw": "{{base_url}}/api/chat/ia/create",
                    "host": ["{{base_url}}"],
                    "path": ["api", "chat", "ia", "create"]
                }
            }
        }
    ]
}
```

---

**📞 Soporte**: Para dudas o problemas, contactar al equipo de desarrollo.

**🔄 Última actualización**: 2025-09-04