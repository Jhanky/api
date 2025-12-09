# Endpoints de Proyectos

## Descripción General

Endpoints para listar proyectos, obtener información específica de un proyecto y actualizar proyectos con imagen de portada.

## Base URL

```
/api/projects
```

## Autenticación

Todos los endpoints requieren autenticación mediante token Bearer.

## Endpoints Disponibles

### 1. Listar Proyectos

**GET** `/api/projects`

Obtiene una lista paginada de todos los proyectos con sus relaciones.

#### Parámetros de Query (opcionales):
- `status` (string): Filtrar por nombre del estado
- `client_id` (integer): Filtrar por ID del cliente
- `search` (string): Búsqueda por nombre del proyecto o nombre del cliente
- `page` (integer): Número de página para paginación

### 2. Ver Proyecto Específico

**GET** `/api/projects/{project_id}`

Obtiene la información detallada de un proyecto específico.

### 3. Actualizar Proyecto

**PUT** `/api/projects/{project_id}`

Actualiza la información de un proyecto existente, incluyendo la posibilidad de subir una imagen de portada.

#### Request Body (Form Data - para incluir imagen):
```bash
curl -X PUT "http://tu-dominio.com/api/projects/2" \
  -H "Authorization: Bearer tu-token" \
  -F "notes=Proyecto actualizado con imagen" \
  -F "cover_image=@/ruta/a/imagen.jpg" \
  -F "cover_image_alt=Imagen de la planta solar Jhanky"
```

#### Validaciones:
- `cover_image` (file, opcional): Imagen de portada (jpeg, png, jpg, gif, máximo 5MB)
- `cover_image_alt` (string, opcional, máximo 255 caracteres): Texto alternativo para la imagen

## Endpoints de Imágenes de Proyectos

### 4. Subir Imagen de Portada

**POST** `/api/projects/{project}/images/cover`

Sube una imagen de portada para un proyecto específico.

### 5. Obtener Imagen de Portada

**GET** `/api/projects/{project}/images/cover`

Obtiene la imagen de portada de un proyecto.

### 6. Eliminar Imagen de Portada

**DELETE** `/api/projects/{project}/images/cover`

Elimina la imagen de portada de un proyecto.

## Notas Importantes

1. **Imágenes de Portada**: Los proyectos pueden tener una imagen de portada que se muestra al hacer clic en la ubicación.
2. **Autenticación**: Todos los endpoints requieren un token de autenticación válido.
3. **Paginación**: El endpoint de listar proyectos devuelve resultados paginados con 15 elementos por página.

