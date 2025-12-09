# 📮 Guía de Pruebas en Postman - Reportes de Facturas

## 🎯 Configuración Inicial en Postman

### 1. **Configurar Variables de Entorno**

#### Crear Nueva Colección
1. Abre Postman
2. Clic en "New" → "Collection"
3. Nombre: `Reportes de Facturas - API`
4. Descripción: `Colección para probar reportes de facturas en Excel`

#### Configurar Variables de Entorno
1. Clic en "Environments" → "Create Environment"
2. Nombre: `Backend Local`
3. Agregar variables:

| Variable | Initial Value | Current Value |
|----------|---------------|---------------|
| `base_url` | `http://localhost:8000` | `http://localhost:8000` |
| `token` | `tu_token_aqui` | `tu_token_aqui` |
| `api_prefix` | `/api` | `/api` |

---

## 📊 **Request 1: Reporte Básico (Todas las Facturas)**

### **Configuración del Request**

#### **Método y URL**
```
GET {{base_url}}{{api_prefix}}/invoices/report
```

#### **Headers Requeridos**
| Key | Value |
|-----|-------|
| `Authorization` | `Bearer {{token}}` |
| `Accept` | `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` |
| `Content-Type` | `application/json` |

#### **Parámetros de Query**
*No se requieren parámetros para el reporte básico*

#### **Configuración de Descarga**
1. En la pestaña "Tests", agregar:
```javascript
// Verificar que la respuesta es exitosa
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Verificar que es un archivo Excel
pm.test("Content-Type is Excel", function () {
    pm.expect(pm.response.headers.get("Content-Type")).to.include("spreadsheetml");
});

// Verificar que hay contenido
pm.test("Response has content", function () {
    pm.expect(pm.response.text()).to.not.be.empty;
});

// Guardar el archivo
if (pm.response.code === 200) {
    const response = pm.response;
    const filename = "reporte_facturas_" + new Date().toISOString().slice(0,19).replace(/:/g, '-') + ".xlsx";
    
    // Crear blob y descargar
    const blob = new Blob([response.body], { 
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
    });
    
    // Nota: En Postman, el archivo se descarga automáticamente
    console.log("Archivo Excel generado: " + filename);
}
```

---

## 🔍 **Request 2: Reporte con Filtro de Estado**

### **Configuración del Request**

#### **Método y URL**
```
GET {{base_url}}{{api_prefix}}/invoices/report
```

#### **Headers**
| Key | Value |
|-----|-------|
| `Authorization` | `Bearer {{token}}` |
| `Accept` | `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` |

#### **Parámetros de Query**
| Key | Value | Description |
|-----|-------|-------------|
| `status` | `PENDIENTE` | Solo facturas pendientes |

#### **Tests para Validación**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Content-Type is Excel", function () {
    pm.expect(pm.response.headers.get("Content-Type")).to.include("spreadsheetml");
});

// Verificar que el nombre del archivo contiene el filtro
pm.test("Filename contains filter", function () {
    const contentDisposition = pm.response.headers.get("Content-Disposition");
    pm.expect(contentDisposition).to.include("pendiente");
});
```

---

## 🏢 **Request 3: Reporte por Proveedor**

### **Configuración del Request**

#### **Método y URL**
```
GET {{base_url}}{{api_prefix}}/invoices/report
```

#### **Parámetros de Query**
| Key | Value | Description |
|-----|-------|-------------|
| `provider_id` | `1` | ID del proveedor específico |

#### **Tests para Validación**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Content-Type is Excel", function () {
    pm.expect(pm.response.headers.get("Content-Type")).to.include("spreadsheetml");
});

// Verificar que el archivo se puede descargar
pm.test("File is downloadable", function () {
    pm.expect(pm.response.body).to.not.be.empty;
    pm.expect(pm.response.body.length).to.be.above(1000); // Archivo Excel mínimo
});
```

---

## 📅 **Request 4: Reporte por Fecha**

### **Configuración del Request**

#### **Método y URL**
```
GET {{base_url}}{{api_prefix}}/invoices/report
```

#### **Parámetros de Query**
| Key | Value | Description |
|-----|-------|-------------|
| `month` | `8` | Agosto |
| `year` | `2025` | Año 2025 |

#### **Tests para Validación**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Content-Type is Excel", function () {
    pm.expect(pm.response.headers.get("Content-Type")).to.include("spreadsheetml");
});

// Verificar que el nombre del archivo contiene la fecha
pm.test("Filename contains date", function () {
    const contentDisposition = pm.response.headers.get("Content-Disposition");
    pm.expect(contentDisposition).to.include("2025_08");
});
```

---

## 🔄 **Request 5: Reporte con Filtros Combinados**

### **Configuración del Request**

#### **Método y URL**
```
GET {{base_url}}{{api_prefix}}/invoices/report
```

#### **Parámetros de Query**
| Key | Value | Description |
|-----|-------|-------------|
| `status` | `PAGADA` | Solo facturas pagadas |
| `provider_id` | `1` | Proveedor específico |
| `month` | `8` | Agosto |
| `year` | `2025` | Año 2025 |

#### **Tests para Validación**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Content-Type is Excel", function () {
    pm.expect(pm.response.headers.get("Content-Type")).to.include("spreadsheetml");
});

// Verificar que el archivo contiene todos los filtros
pm.test("Filename contains all filters", function () {
    const contentDisposition = pm.response.headers.get("Content-Disposition");
    pm.expect(contentDisposition).to.include("pagada");
    pm.expect(contentDisposition).to.include("2025_08");
});
```

---

## 🧪 **Request 6: Endpoint de Prueba (JSON)**

### **Configuración del Request**

#### **Método y URL**
```
GET {{base_url}}{{api_prefix}}/invoices/test-report
```

#### **Headers**
| Key | Value |
|-----|-------|
| `Authorization` | `Bearer {{token}}` |
| `Accept` | `application/json` |

#### **Parámetros de Query**
| Key | Value | Description |
|-----|-------|-------------|
| `status` | `PENDIENTE` | Solo facturas pendientes |

#### **Tests para Validación**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response is JSON", function () {
    pm.response.to.be.json;
});

pm.test("Response has success true", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Response has count", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.count).to.be.a('number');
});

pm.test("Response has data array", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.be.an('array');
});

// Mostrar información en consola
pm.test("Log response info", function () {
    const jsonData = pm.response.json();
    console.log("Facturas encontradas:", jsonData.count);
    console.log("Filtros aplicados:", jsonData.filters_applied);
    if (jsonData.data.length > 0) {
        console.log("Primera factura:", jsonData.data[0]);
    }
});
```

---

## ⚠️ **Request 7: Manejo de Errores**

### **Request con Filtros Inválidos**

#### **Método y URL**
```
GET {{base_url}}{{api_prefix}}/invoices/report
```

#### **Parámetros de Query (Inválidos)**
| Key | Value | Description |
|-----|-------|-------------|
| `status` | `INVALIDO` | Estado inválido |
| `month` | `15` | Mes inválido |

#### **Tests para Validación de Errores**
```javascript
pm.test("Status code is 422 (Validation Error)", function () {
    pm.response.to.have.status(422);
});

pm.test("Response has error message", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.false;
    pm.expect(jsonData.message).to.include("Error de validación");
});

pm.test("Response has validation errors", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.errors).to.be.an('object');
});
```

---

## 🔧 **Configuración de Colección**

### **Pre-request Scripts (Colección)**
```javascript
// Verificar que el token existe
if (!pm.environment.get("token") || pm.environment.get("token") === "tu_token_aqui") {
    console.log("⚠️  ADVERTENCIA: Configura tu token en las variables de entorno");
    console.log("Token actual:", pm.environment.get("token"));
}

// Verificar que la URL base está configurada
if (!pm.environment.get("base_url")) {
    console.log("⚠️  ADVERTENCIA: Configura la URL base en las variables de entorno");
}
```

### **Tests Globales (Colección)**
```javascript
// Test global para verificar que el servidor responde
pm.test("Server is responding", function () {
    pm.expect(pm.response.code).to.be.oneOf([200, 201, 422, 404, 500]);
});

// Test global para verificar tiempo de respuesta
pm.test("Response time is acceptable", function () {
    pm.expect(pm.response.responseTime).to.be.below(10000); // 10 segundos
});
```

---

## 📋 **Checklist de Pruebas**

### **✅ Pruebas Básicas**
- [ ] Reporte sin filtros genera archivo Excel
- [ ] Reporte con filtro de estado funciona
- [ ] Reporte con filtro de proveedor funciona
- [ ] Reporte con filtro de fecha funciona
- [ ] Reporte con filtros combinados funciona

### **✅ Pruebas de Validación**
- [ ] Endpoint de prueba devuelve JSON válido
- [ ] Filtros inválidos devuelven error 422
- [ ] Token inválido devuelve error 401
- [ ] Sin token devuelve error 401

### **✅ Pruebas de Archivo**
- [ ] Archivo Excel se descarga correctamente
- [ ] Archivo tiene contenido válido
- [ ] Nombre del archivo incluye filtros aplicados
- [ ] Content-Type es correcto

### **✅ Pruebas de Rendimiento**
- [ ] Tiempo de respuesta < 10 segundos
- [ ] Archivo se genera sin errores
- [ ] Memoria no se agota

---

## 🚀 **Ejecutar Todas las Pruebas**

### **Runner de Colección**
1. Clic en "Runner" en Postman
2. Seleccionar la colección "Reportes de Facturas - API"
3. Seleccionar el entorno "Backend Local"
4. Clic en "Start Test"

### **Resultados Esperados**
- ✅ Todas las pruebas pasan
- ✅ Archivos Excel se descargan
- ✅ Respuestas JSON son válidas
- ✅ Errores se manejan correctamente

---

## 📞 **Solución de Problemas**

### **Error 401: Unauthorized**
```javascript
// Verificar token en Tests
pm.test("Token is valid", function () {
    pm.expect(pm.environment.get("token")).to.not.equal("tu_token_aqui");
});
```

### **Error 404: Not Found**
```javascript
// Verificar URL en Tests
pm.test("URL is correct", function () {
    pm.expect(pm.request.url.toString()).to.include("/api/invoices/report");
});
```

### **Error 500: Internal Server Error**
```javascript
// Verificar logs del servidor
pm.test("Server error details", function () {
    if (pm.response.code === 500) {
        console.log("Error del servidor:", pm.response.text());
    }
});
```

---

**📅 Última actualización**: Enero 2025  
**📮 Versión Postman**: 1.0  
**👨‍💻 Desarrollado por**: Equipo de Desarrollo Backend
