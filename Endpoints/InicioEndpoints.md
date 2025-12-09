# Endpoints del Dashboard - Página de Inicio

## Descripción
Endpoints para obtener información de proyectos y estadísticas para la página de inicio del sistema.

## Base URL
```
/api/dashboard
```

## Autenticación
Todos los endpoints requieren autenticación mediante Sanctum.

## Endpoints

### 1. Obtener Todos los Proyectos
**GET** `/api/dashboard/projects`

Obtiene todos los proyectos con información formateada para el dashboard.

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "nombre": "Casa del remate",
      "ubicacion": "Fundación, Magdalena",
      "coordenadas": [10.523257, -74.187122],
      "capacidad": 75,
      "potenciaActual": 7.5,
      "generacionHoy": 361.1,
      "estado": "activa",
      "eficiencia": 88,
      "ultimaActualizacion": "2025-09-15T15:46:44.000000Z",
      "fechaInicio": "2025-09-05",
      "fechaFin": null,
      "imagenPortada": "http://tu-dominio.com/storage/projects/5/uuid-imagen.jpg",
      "imagenPortadaAlt": "Imagen de la planta solar Casa del remate",
      "cliente": {
        "id": 6,
        "nombre": "Luz Amparo  Franco Quinchia",
        "tipo": "Comercial",
        "nic": "900454797-4",
        "ubicacion": "Fundación, Magdalena",
        "direccion": "Calle 4 No 8A 20, barrio centro",
        "consumo_mensual_kwh": 12390,
        "tarifa_energia": 1100,
        "tipo_red": "Trifásica 220V"
      },
      "gerenteProyecto": "Jhan Martinez"
    }
  ],
  "message": "Proyectos obtenidos exitosamente"
}
```

### 2. Obtener Proyectos Activos
**GET** `/api/dashboard/projects/active`

Obtiene solo los proyectos que están en estado activo.

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "nombre": "Casa del remate",
      "ubicacion": "Fundación, Magdalena",
      "coordenadas": [10.523257, -74.187122],
      "capacidad": 75,
      "potenciaActual": 7.5,
      "generacionHoy": 361.1,
      "estado": "activa",
      "eficiencia": 88,
      "ultimaActualizacion": "2025-09-15T15:46:44.000000Z",
      "fechaInicio": "2025-09-05",
      "fechaFin": null,
      "imagenPortada": "http://tu-dominio.com/storage/projects/5/uuid-imagen.jpg",
      "imagenPortadaAlt": "Imagen de la planta solar Casa del remate",
      "cliente": {
        "id": 6,
        "nombre": "Luz Amparo  Franco Quinchia",
        "tipo": "Comercial",
        "nic": "900454797-4",
        "ubicacion": "Fundación, Magdalena",
        "direccion": "Calle 4 No 8A 20, barrio centro",
        "consumo_mensual_kwh": 12390,
        "tarifa_energia": 1100,
        "tipo_red": "Trifásica 220V"
      },
      "gerenteProyecto": "Jhan Martinez"
    }
  ],
  "message": "Proyectos activos obtenidos exitosamente"
}
```

### 3. Obtener Estadísticas del Dashboard
**GET** `/api/dashboard/stats`

Obtiene estadísticas generales del sistema.

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": {
    "total_projects": 7,
    "active_projects": 7,
    "completed_projects": 0,
    "total_capacity_kwp": 525.0,
    "active_capacity_kwp": 525.0,
    "efficiency_average": 91.0,
    "last_updated": "2025-09-15T15:46:44.000000Z"
  },
  "message": "Estadísticas obtenidas exitosamente"
}
```

## Endpoints de Imágenes de Proyectos

### 4. Subir Imagen de Portada
**POST** `/api/projects/{project}/images/cover`

Sube una imagen de portada para un proyecto específico.

#### Parámetros
- `project`: ID del proyecto
- `cover_image`: Archivo de imagen (jpeg, png, jpg, gif, máximo 5MB)
- `cover_image_alt`: Texto alternativo para la imagen (opcional)

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Imagen de portada subida exitosamente",
  "data": {
    "cover_image": "http://tu-dominio.com/storage/projects/5/uuid-imagen.jpg",
    "cover_image_alt": "Imagen de la planta solar Casa del remate"
  }
}
```

### 5. Obtener Imagen de Portada
**GET** `/api/projects/{project}/images/cover`

Obtiene la imagen de portada de un proyecto.

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": {
    "cover_image": "http://tu-dominio.com/storage/projects/5/uuid-imagen.jpg",
    "cover_image_alt": "Imagen de la planta solar Casa del remate"
  }
}
```

### 6. Eliminar Imagen de Portada
**DELETE** `/api/projects/{project}/images/cover`

Elimina la imagen de portada de un proyecto.

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Imagen de portada eliminada exitosamente"
}
```

## Descripción de Campos

### Proyecto
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | integer | ID único del proyecto |
| `nombre` | string | Nombre del proyecto |
| `ubicacion` | string | Ubicación del proyecto (ciudad, departamento) |
| `coordenadas` | array | Array con [latitud, longitud] del proyecto |
| `capacidad` | integer | Capacidad del sistema en kWp |
| `potenciaActual` | float | Potencia actual generada en kW (simulada) |
| `generacionHoy` | float | Generación de energía de hoy en kWh (simulada) |
| `estado` | string | Estado del proyecto (activa, completada, pausada, etc.) |
| `eficiencia` | integer | Eficiencia del sistema en porcentaje |
| `ultimaActualizacion` | string | Fecha de última actualización en ISO 8601 |
| `fechaInicio` | string | Fecha de inicio del proyecto (YYYY-MM-DD) |
| `fechaFin` | string | Fecha de finalización del proyecto (YYYY-MM-DD) o null |
| `imagenPortada` | string | URL de la imagen de portada del proyecto (null si no tiene) |
| `imagenPortadaAlt` | string | Texto alternativo para la imagen de portada |
| `cliente` | object | Información completa del cliente (ver tabla Cliente) |
| `gerenteProyecto` | string | Nombre del gerente del proyecto |

### Cliente
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | integer | ID único del cliente |
| `nombre` | string | Nombre completo del cliente |
| `tipo` | string | Tipo de cliente (Comercial, Residencial, Industrial) |
| `nic` | string | Número de identificación del cliente |
| `ubicacion` | string | Ubicación del cliente (ciudad, departamento) |
| `direccion` | string | Dirección física del cliente |
| `consumo_mensual_kwh` | float | Consumo mensual en kWh |
| `tarifa_energia` | float | Tarifa de energía por kWh |
| `tipo_red` | string | Tipo de red eléctrica (Monofásica, Trifásica) |

### Estadísticas
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `total_projects` | integer | Total de proyectos en el sistema |
| `active_projects` | integer | Proyectos activos |
| `completed_projects` | integer | Proyectos completados |
| `total_capacity_kwp` | float | Capacidad total instalada en kWp |
| `active_capacity_kwp` | float | Capacidad activa en kWp |
| `efficiency_average` | float | Eficiencia promedio del sistema |
| `last_updated` | string | Fecha de última actualización en ISO 8601 |

## Estados de Proyecto
- `activa`: Proyecto en funcionamiento
- `completada`: Proyecto terminado exitosamente
- `pausada`: Proyecto pausado temporalmente
- `cancelada`: Proyecto cancelado
- `planificacion`: Proyecto en fase de planificación
- `desconocida`: Estado no reconocido

## Notas Importantes

1. **Cálculos Reales**: Los campos `potenciaActual`, `generacionHoy` y `eficiencia` se calculan usando fórmulas reales de ingeniería solar:
   - **Generación Diaria**: `G_diaria = P_pico × 4.5 × 0.85` (donde P_pico es la capacidad en kW, 4.5 son las horas pico de sol en Colombia, y 0.85 es el factor de corrección por pérdidas del 15%)
   - **Potencia Actual**: Se calcula basada en la hora del día usando una curva sinusoidal que simula el comportamiento real del sol
   - **Eficiencia**: Basada en el factor de pérdidas real (85% base) con variaciones realistas

2. **Fórmula de Generación Solar**: 
   - **G_diaria = P_pico × 4.5 × 0.85**
   - P_pico: Capacidad del sistema en kW
   - 4.5: Horas pico de sol promedio en Colombia
   - 0.85: Factor de corrección por pérdidas (15% de pérdidas del sistema)

3. **Coordenadas**: Si un proyecto no tiene coordenadas definidas, el array estará vacío.

4. **Ubicación del Proyecto vs Cliente**: 
   - `ubicacion`: Ubicación donde está instalado el proyecto
   - `cliente.ubicacion`: Ubicación del cliente (puede ser diferente)

5. **Información del Cliente**: Ahora incluye información completa del cliente con su ubicación, consumo y datos técnicos.

6. **Datos Reales**: Los ejemplos mostrados corresponden a datos reales del sistema, incluyendo proyectos en Fundación, Galapa, Barranquilla y Cartagena.

7. **Imágenes de Portada**: Los proyectos pueden tener una imagen de portada que se muestra al hacer clic en la ubicación. Las imágenes se almacenan en `storage/app/public/projects/{project_id}/`.

8. **Autenticación**: Todos los endpoints requieren un token de autenticación válido en el header `Authorization: Bearer {token}`.

## Ejemplos de Uso

### Obtener todos los proyectos
```bash
curl -X GET "http://tu-dominio.com/api/dashboard/projects" \
  -H "Authorization: Bearer tu-token-aqui" \
  -H "Accept: application/json"
```

### Obtener solo proyectos activos
```bash
curl -X GET "http://tu-dominio.com/api/dashboard/projects/active" \
  -H "Authorization: Bearer tu-token-aqui" \
  -H "Accept: application/json"
```

### Obtener estadísticas
```bash
curl -X GET "http://tu-dominio.com/api/dashboard/stats" \
  -H "Authorization: Bearer tu-token-aqui" \
  -H "Accept: application/json"
```

### Subir imagen de portada
```bash
curl -X POST "http://tu-dominio.com/api/projects/5/images/cover" \
  -H "Authorization: Bearer tu-token-aqui" \
  -F "cover_image=@/ruta/a/imagen.jpg" \
  -F "cover_image_alt=Imagen de la planta solar Casa del remate"
```

### Obtener imagen de portada
```bash
curl -X GET "http://tu-dominio.com/api/projects/5/images/cover" \
  -H "Authorization: Bearer tu-token-aqui" \
  -H "Accept: application/json"
```

### Eliminar imagen de portada
```bash
curl -X DELETE "http://tu-dominio.com/api/projects/5/images/cover" \
  -H "Authorization: Bearer tu-token-aqui" \
  -H "Accept: application/json"
```

## Código de Ejemplo para Frontend

### JavaScript/React
```javascript
// Obtener todos los proyectos
const fetchProjects = async () => {
  try {
    const response = await fetch('/api/dashboard/projects', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Error al obtener proyectos:', error);
  }
};

// Subir imagen de portada
const uploadCoverImage = async (projectId, imageFile, altText) => {
  try {
    const formData = new FormData();
    formData.append('cover_image', imageFile);
    formData.append('cover_image_alt', altText);
    
    const response = await fetch(`/api/projects/${projectId}/images/cover`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`
      },
      body: formData
    });
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error al subir imagen:', error);
  }
};

// Acceder a información del cliente y imagen
const displayProjectInfo = (project) => {
  console.log(`Proyecto: ${project.nombre}`);
  console.log(`Cliente: ${project.cliente.nombre}`);
  console.log(`Ubicación del cliente: ${project.cliente.ubicacion}`);
  console.log(`Imagen de portada: ${project.imagenPortada}`);
  console.log(`Alt text: ${project.imagenPortadaAlt}`);
};
```

### Vue.js
```javascript
// En un componente Vue
export default {
  data() {
    return {
      projects: [],
      activeProjects: [],
      stats: {},
      loading: false
    }
  },
  async mounted() {
    await this.loadDashboardData();
  },
  methods: {
    async loadDashboardData() {
      this.loading = true;
      try {
        const [projects, activeProjects, stats] = await Promise.all([
          this.fetchProjects(),
          this.fetchActiveProjects(),
          this.fetchDashboardStats()
        ]);
        
        this.projects = projects;
        this.activeProjects = activeProjects;
        this.stats = stats;
      } catch (error) {
        console.error('Error al cargar datos del dashboard:', error);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchProjects() {
      const response = await this.$http.get('/api/dashboard/projects');
      return response.data.data;
    },
    
    async fetchActiveProjects() {
      const response = await this.$http.get('/api/dashboard/projects/active');
      return response.data.data;
    },
    
    async fetchDashboardStats() {
      const response = await this.$http.get('/api/dashboard/stats');
      return response.data.data;
    },
    
    async uploadCoverImage(projectId, imageFile, altText) {
      const formData = new FormData();
      formData.append('cover_image', imageFile);
      formData.append('cover_image_alt', altText);
      
      const response = await this.$http.post(`/api/projects/${projectId}/images/cover`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      return response.data;
    }
  }
}
```