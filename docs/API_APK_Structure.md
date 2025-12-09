# Estructura de API y APK

## Descripción General

El proyecto ha sido reorganizado para separar claramente los endpoints destinados al frontend web (React) y la aplicación móvil (React Native).

## Estructura de Carpetas

```
app/Http/Controllers/
├── Api/           # Controladores para frontend web (React)
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── ProjectController.php
│   ├── ClientController.php
│   └── ... (todos los controladores existentes)
└── Apk/           # Controladores para aplicación móvil (React Native)
    ├── AuthController.php
    ├── DashboardController.php
    ├── ProjectController.php
    ├── ClientController.php
    └── ... (controladores optimizados para móvil)
```

## Rutas

### API (Frontend Web - React)
- **Prefijo:** `/api/`
- **Archivo:** `routes/api.php`
- **Propósito:** Funcionalidades completas de administración
- **Características:**
  - CRUD completo
  - Funcionalidades avanzadas
  - Dashboard completo
  - Gestión de archivos
  - Integraciones complejas

### APK (Aplicación Móvil - React Native)
- **Prefijo:** `/apk/`
- **Archivo:** `routes/apk.php`
- **Propósito:** Funcionalidades optimizadas para móvil
- **Características:**
  - Respuestas simplificadas
  - Paginación optimizada
  - Datos esenciales
  - Funcionalidades de consulta

## Diferencias Principales

### API (Web)
```php
// Respuesta completa con todos los datos
return response()->json([
    'success' => true,
    'data' => [
        'project' => $project,
        'quotation' => $quotation,
        'client' => $client,
        'location' => $location,
        'status' => $status,
        'project_manager' => $projectManager,
        'used_products' => $usedProducts,
        'items' => $items,
        // ... más datos
    ],
    'message' => 'Proyecto obtenido exitosamente'
]);
```

### APK (Móvil)
```php
// Respuesta simplificada para móvil
return response()->json([
    'success' => true,
    'data' => [
        'id' => $project->project_id,
        'name' => $project->quotation->project_name,
        'location' => $project->location->municipality . ', ' . $project->location->department,
        'capacity' => round($project->quotation->power_kwp, 1),
        'status' => [
            'name' => $project->status->name,
            'color' => $project->status->color
        ],
        'client' => $project->client->name
    ],
    'message' => 'Proyecto obtenido exitosamente'
]);
```

## Middleware de Detección de Plataforma

### PlatformDetection Middleware
- Detecta automáticamente si la petición viene del web o móvil
- Agrega headers de respuesta para identificar la plataforma
- Basado en:
  - Prefijo de URL (`/api/` vs `/apk/`)
  - User-Agent del dispositivo
  - Headers personalizados

### Headers de Respuesta
```
X-Platform: mobile|web
X-API-Version: 1.0.0
```

## Endpoints Disponibles

### API (Web)
```
GET    /api/dashboard/projects          # Todos los proyectos
GET    /api/dashboard/projects/active   # Proyectos activos
GET    /api/dashboard/stats             # Estadísticas completas
GET    /api/projects                    # CRUD completo de proyectos
POST   /api/projects                    # Crear proyecto
PUT    /api/projects/{id}               # Actualizar proyecto
DELETE /api/projects/{id}               # Eliminar proyecto
```

### APK (Móvil)
```
GET    /apk/dashboard/summary           # Resumen simplificado
GET    /apk/dashboard/projects/active   # Proyectos activos (simplificado)
GET    /apk/dashboard/realtime          # Datos en tiempo real
GET    /apk/projects                    # Lista de proyectos (paginada)
GET    /apk/projects/{id}               # Proyecto específico (simplificado)
GET    /apk/projects/status/{status}    # Proyectos por estado
```

## Ventajas de esta Estructura

### 1. **Separación Clara**
- Endpoints específicos para cada plataforma
- Controladores optimizados para cada caso de uso
- Mantenimiento más fácil

### 2. **Optimización de Rendimiento**
- Respuestas más ligeras para móvil
- Menos datos transferidos
- Mejor experiencia de usuario

### 3. **Escalabilidad**
- Desarrollo independiente de cada plataforma
- Actualizaciones específicas por plataforma
- Testing separado

### 4. **Mantenibilidad**
- Código más organizado
- Responsabilidades claras
- Debugging más fácil

## Uso en el Frontend

### React (Web)
```javascript
// Usar endpoints API
const response = await fetch('/api/dashboard/projects', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
});
```

### React Native (Móvil)
```javascript
// Usar endpoints APK
const response = await fetch('/apk/dashboard/summary', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'X-Platform': 'mobile'
    }
});
```

## Próximos Pasos

1. **Migrar controladores existentes** a la carpeta APK
2. **Crear controladores específicos** para funcionalidades móviles
3. **Optimizar respuestas** para cada plataforma
4. **Implementar testing** separado para cada plataforma
5. **Documentar endpoints** específicos de cada plataforma

## Notas Importantes

- Los controladores API mantienen toda la funcionalidad existente
- Los controladores APK son versiones simplificadas
- El middleware de detección de plataforma funciona automáticamente
- Las rutas están registradas en `RouteServiceProvider`
- La autenticación funciona igual en ambas plataformas
