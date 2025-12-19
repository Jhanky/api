# Estándar de Respuestas API - Guía para Frontend React

## 📋 Información General

Esta documentación describe el estándar de respuestas de la API VatioCore para facilitar el desarrollo del frontend en React. Todas las respuestas siguen una estructura consistente que permite un manejo uniforme de datos y errores.

## 🏗️ Estructura General de Respuestas

### Campos Comunes en Todas las Respuestas

```typescript
interface ApiResponse<T = any> {
  success: boolean;           // Indica si la operación fue exitosa
  message: string;            // Mensaje descriptivo de la operación
  timestamp: string;          // Timestamp ISO 8601 de la respuesta
  request_id: string;         // ID único para rastreo de la petición
  // Campos adicionales según el tipo de respuesta
}
```

## 📤 Tipos de Respuestas

### 1. Respuesta de Éxito (Success Response)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com"
  },
  "message": "Usuario obtenido exitosamente",
  "timestamp": "2025-12-12T13:54:39.000000Z",
  "request_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

### 2. Respuesta de Error (Error Response)

```json
{
  "success": false,
  "message": "Los datos proporcionados no son válidos",
  "errors": {
    "email": ["El correo electrónico ya está registrado"],
    "password": ["La contraseña debe tener al menos 8 caracteres"]
  },
  "timestamp": "2025-12-12T13:54:39.000000Z",
  "request_id": "550e8400-e29b-41d4-a716-446655440001"
}
```

### 3. Respuesta Paginada (Pagination Response)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Usuario 1",
      "email": "usuario1@example.com"
    },
    {
      "id": 2,
      "name": "Usuario 2",
      "email": "usuario2@example.com"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10,
    "from": 1,
    "to": 15,
    "has_more_pages": true
  },
  "message": "Usuarios obtenidos exitosamente",
  "timestamp": "2025-12-12T13:54:39.000000Z",
  "request_id": "550e8400-e29b-41d4-a716-446655440002"
}
```

### 4. Respuesta de Creación (Created Response)

```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "Nuevo Usuario",
    "email": "nuevo@example.com",
    "created_at": "2025-12-12T13:54:39.000000Z"
  },
  "message": "Usuario creado exitosamente",
  "timestamp": "2025-12-12T13:54:39.000000Z",
  "request_id": "550e8400-e29b-41d4-a716-446655440003"
}
```

## 🔢 Códigos HTTP

| Código | Significado | Acción en Frontend |
|--------|-------------|-------------------|
| 200 | OK | Procesar datos normalmente |
| 201 | Created | Mostrar mensaje de éxito, redirigir si es necesario |
| 202 | Accepted | Operación aceptada (útil para procesos asíncronos) |
| 204 | No Content | No hay contenido que mostrar |
| 400 | Bad Request | Mostrar errores de validación |
| 401 | Unauthorized | Redirigir a login, limpiar tokens |
| 403 | Forbidden | Mostrar mensaje de permisos insuficientes |
| 404 | Not Found | Mostrar página 404 o mensaje de recurso no encontrado |
| 409 | Conflict | Mostrar mensaje de conflicto (ej: recurso ya existe) |
| 422 | Unprocessable Entity | Mostrar errores de validación detallados |
| 429 | Too Many Requests | Mostrar mensaje de rate limiting |
| 500 | Internal Server Error | Mostrar mensaje genérico de error |

## ⚛️ Implementación en React

### 1. Tipos TypeScript

```typescript
// types/api.ts
export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  timestamp: string;
  request_id: string;
  data?: T;
  errors?: Record<string, string[]>;
  pagination?: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
    has_more_pages: boolean;
  };
}

export interface PaginatedData<T> {
  data: T[];
  pagination: ApiResponse['pagination'];
}

export interface ValidationErrors {
  [field: string]: string[];
}
```

### 2. Custom Hook para API Calls

```typescript
// hooks/useApi.ts
import { useState, useCallback } from 'react';
import { ApiResponse, ValidationErrors } from '../types/api';

interface UseApiState<T> {
  data: T | null;
  loading: boolean;
  error: string | null;
  validationErrors: ValidationErrors | null;
}

export function useApi<T = any>() {
  const [state, setState] = useState<UseApiState<T>>({
    data: null,
    loading: false,
    error: null,
    validationErrors: null,
  });

  const execute = useCallback(async (
    apiCall: () => Promise<Response>
  ): Promise<ApiResponse<T> | null> => {
    setState(prev => ({
      ...prev,
      loading: true,
      error: null,
      validationErrors: null,
    }));

    try {
      const response = await apiCall();
      const result: ApiResponse<T> = await response.json();

      if (result.success) {
        setState(prev => ({
          ...prev,
          data: result.data || null,
          loading: false,
        }));
        return result;
      } else {
        // Manejar errores
        if (response.status === 422 && result.errors) {
          setState(prev => ({
            ...prev,
            validationErrors: result.errors,
            loading: false,
          }));
        } else {
          setState(prev => ({
            ...prev,
            error: result.message,
            loading: false,
          }));
        }

        // Manejar errores de autenticación
        if (response.status === 401) {
          // Limpiar tokens y redirigir a login
          localStorage.removeItem('token');
          window.location.href = '/login';
        }

        return result;
      }
    } catch (error) {
      const errorMessage = 'Error de conexión. Inténtalo de nuevo.';
      setState(prev => ({
        ...prev,
        error: errorMessage,
        loading: false,
      }));
      return null;
    }
  }, []);

  const reset = useCallback(() => {
    setState({
      data: null,
      loading: false,
      error: null,
      validationErrors: null,
    });
  }, []);

  return {
    ...state,
    execute,
    reset,
  };
}
```

### 3. Hook para Paginación

```typescript
// hooks/usePagination.ts
import { useState, useCallback } from 'react';
import { ApiResponse, PaginatedData } from '../types/api';

interface UsePaginationState<T> {
  data: T[];
  pagination: ApiResponse['pagination'] | null;
  loading: boolean;
  error: string | null;
}

export function usePagination<T = any>(initialPerPage = 15) {
  const [state, setState] = useState<UsePaginationState<T>>({
    data: [],
    pagination: null,
    loading: false,
    error: null,
  });

  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerPage] = useState(initialPerPage);

  const fetchPage = useCallback(async (
    apiCall: (page: number, perPage: number) => Promise<Response>
  ) => {
    setState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const response = await apiCall(currentPage, perPage);
      const result: ApiResponse<PaginatedData<T>> = await response.json();

      if (result.success && result.data) {
        setState(prev => ({
          ...prev,
          data: result.data.data,
          pagination: result.data.pagination,
          loading: false,
        }));
      } else {
        setState(prev => ({
          ...prev,
          error: result.message,
          loading: false,
        }));
      }
    } catch (error) {
      setState(prev => ({
        ...prev,
        error: 'Error al cargar los datos',
        loading: false,
      }));
    }
  }, [currentPage, perPage]);

  const goToPage = useCallback((page: number) => {
    setCurrentPage(page);
  }, []);

  const changePerPage = useCallback((newPerPage: number) => {
    setPerPage(newPerPage);
    setCurrentPage(1); // Reset to first page
  }, []);

  return {
    ...state,
    currentPage,
    perPage,
    goToPage,
    changePerPage,
    fetchPage,
  };
}
```

### 4. Servicio Base para API

```typescript
// services/api.ts
const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

class ApiService {
  private getAuthHeaders(): HeadersInit {
    const token = localStorage.getItem('token');
    return {
      'Content-Type': 'application/json',
      ...(token && { 'Authorization': `Bearer ${token}` }),
    };
  }

  async get<T = any>(endpoint: string): Promise<Response> {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'GET',
      headers: this.getAuthHeaders(),
    });
    return response;
  }

  async post<T = any>(endpoint: string, data: any): Promise<Response> {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'POST',
      headers: this.getAuthHeaders(),
      body: JSON.stringify(data),
    });
    return response;
  }

  async put<T = any>(endpoint: string, data: any): Promise<Response> {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'PUT',
      headers: this.getAuthHeaders(),
      body: JSON.stringify(data),
    });
    return response;
  }

  async delete(endpoint: string): Promise<Response> {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'DELETE',
      headers: this.getAuthHeaders(),
    });
    return response;
  }
}

export const apiService = new ApiService();
```

### 5. Ejemplo de Componente con Manejo de Errores

```typescript
// components/UserForm.tsx
import React, { useState } from 'react';
import { useApi } from '../hooks/useApi';
import { apiService } from '../services/api';
import { ApiResponse, ValidationErrors } from '../types/api';

interface UserFormData {
  name: string;
  email: string;
  password: string;
}

export const UserForm: React.FC = () => {
  const { execute, loading, error, validationErrors } = useApi();
  const [formData, setFormData] = useState<UserFormData>({
    name: '',
    email: '',
    password: '',
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    const result = await execute(() =>
      apiService.post('/users', formData)
    );

    if (result?.success) {
      // Éxito - mostrar notificación y resetear formulario
      alert('Usuario creado exitosamente');
      setFormData({ name: '', email: '', password: '' });
    }
    // Los errores ya se manejan automáticamente en el hook
  };

  const handleInputChange = (field: keyof UserFormData) =>
    (e: React.ChangeEvent<HTMLInputElement>) => {
      setFormData(prev => ({
        ...prev,
        [field]: e.target.value,
      }));
    };

  return (
    <form onSubmit={handleSubmit}>
      <div>
        <label>Nombre:</label>
        <input
          type="text"
          value={formData.name}
          onChange={handleInputChange('name')}
          disabled={loading}
        />
        {validationErrors?.name && (
          <span className="error">{validationErrors.name[0]}</span>
        )}
      </div>

      <div>
        <label>Email:</label>
        <input
          type="email"
          value={formData.email}
          onChange={handleInputChange('email')}
          disabled={loading}
        />
        {validationErrors?.email && (
          <span className="error">{validationErrors.email[0]}</span>
        )}
      </div>

      <div>
        <label>Contraseña:</label>
        <input
          type="password"
          value={formData.password}
          onChange={handleInputChange('password')}
          disabled={loading}
        />
        {validationErrors?.password && (
          <span className="error">{validationErrors.password[0]}</span>
        )}
      </div>

      {error && <div className="error-message">{error}</div>}

      <button type="submit" disabled={loading}>
        {loading ? 'Creando...' : 'Crear Usuario'}
      </button>
    </form>
  );
};
```

### 6. Componente de Lista con Paginación

```typescript
// components/UserList.tsx
import React, { useEffect } from 'react';
import { usePagination } from '../hooks/usePagination';
import { apiService } from '../services/api';

interface User {
  id: number;
  name: string;
  email: string;
  role_name: string;
}

export const UserList: React.FC = () => {
  const {
    data: users,
    pagination,
    loading,
    error,
    currentPage,
    perPage,
    goToPage,
    changePerPage,
    fetchPage,
  } = usePagination<User>();

  useEffect(() => {
    fetchPage((page, perPage) =>
      apiService.get(`/users?page=${page}&per_page=${perPage}`)
    );
  }, [fetchPage, currentPage, perPage]);

  if (loading) return <div>Cargando...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div>
      <h2>Lista de Usuarios</h2>

      <div className="per-page-selector">
        <label>Mostrar:</label>
        <select
          value={perPage}
          onChange={(e) => changePerPage(Number(e.target.value))}
        >
          <option value={10}>10</option>
          <option value={15}>15</option>
          <option value={25}>25</option>
          <option value={50}>50</option>
        </select>
        <span>por página</span>
      </div>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
          </tr>
        </thead>
        <tbody>
          {users.map(user => (
            <tr key={user.id}>
              <td>{user.id}</td>
              <td>{user.name}</td>
              <td>{user.email}</td>
              <td>{user.role_name}</td>
            </tr>
          ))}
        </tbody>
      </table>

      {pagination && (
        <div className="pagination">
          <button
            onClick={() => goToPage(currentPage - 1)}
            disabled={currentPage === 1}
          >
            Anterior
          </button>

          <span>
            Página {pagination.current_page} de {pagination.last_page}
            ({pagination.total} total)
          </span>

          <button
            onClick={() => goToPage(currentPage + 1)}
            disabled={!pagination.has_more_pages}
          >
            Siguiente
          </button>
        </div>
      )}
    </div>
  );
};
```

### 7. Interceptor Global para Manejo de Errores

```typescript
// utils/apiInterceptor.ts
import { ApiResponse } from '../types/api';

export class ApiInterceptor {
  static setupInterceptors() {
    // Interceptar todas las respuestas
    const originalFetch = window.fetch;
    window.fetch = async (...args) => {
      const response = await originalFetch(...args);

      if (!response.ok) {
        const result: ApiResponse = await response.clone().json();

        // Manejar errores específicos
        switch (response.status) {
          case 401:
            // Token expirado
            localStorage.removeItem('token');
            window.location.href = '/login';
            throw new Error('Sesión expirada. Redirigiendo al login...');
            break;

          case 403:
            throw new Error('No tienes permisos para realizar esta acción');
            break;

          case 422:
            // Errores de validación - dejar que se manejen en el componente
            break;

          case 429:
            throw new Error('Demasiadas solicitudes. Inténtalo más tarde');
            break;

          default:
            throw new Error(result.message || 'Ha ocurrido un error inesperado');
        }
      }

      return response;
    };
  }
}

// Inicializar interceptores
ApiInterceptor.setupInterceptors();
```

## 🎯 Casos de Uso Comunes

### Autenticación

```typescript
// Login con email
const handleLoginWithEmail = async (credentials: { email: string; password: string }) => {
  const result = await execute(() =>
    apiService.post('/login', credentials)
  );

  if (result?.success) {
    localStorage.setItem('token', result.data.token);
    localStorage.setItem('user', JSON.stringify(result.data.user));
    // Redirigir al dashboard
  }
};

// Login con username
const handleLoginWithUsername = async (credentials: { username: string; password: string }) => {
  const result = await execute(() =>
    apiService.post('/login', credentials)
  );

  if (result?.success) {
    localStorage.setItem('token', result.data.token);
    localStorage.setItem('user', JSON.stringify(result.data.user));
    // Redirigir al dashboard
  }
};

// Registro de usuario
const handleRegister = async (userData: {
  name: string;
  username: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
  mobile?: string;
  position?: string;
}) => {
  const result = await execute(() =>
    apiService.post('/register', userData)
  );

  if (result?.success) {
    localStorage.setItem('token', result.data.token);
    localStorage.setItem('user', JSON.stringify(result.data.user));
    // Redirigir al dashboard
  }
};

// Logout
const handleLogout = async () => {
  const result = await execute(() =>
    apiService.post('/logout')
  );

  if (result?.success) {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/login';
  }
};

// Obtener información del usuario actual
const getCurrentUser = async () => {
  const result = await execute(() =>
    apiService.get('/me')
  );

  if (result?.success) {
    return result.data.user;
  }
  return null;
};

// Refresh token
const refreshToken = async () => {
  const result = await execute(() =>
    apiService.post('/refresh')
  );

  if (result?.success) {
    localStorage.setItem('token', result.data.token);
    return result.data.token;
  }
  return null;
};
```

### Manejo de Formularios

```typescript
// Crear/Actualizar recursos
const handleSave = async (data: any, isEdit = false) => {
  const endpoint = isEdit ? `/resource/${data.id}` : '/resource';
  const method = isEdit ? 'put' : 'post';

  const result = await execute(() =>
    apiService[method](endpoint, data)
  );

  if (result?.success) {
    // Mostrar éxito y actualizar estado
    onSuccess(result.data);
  }
};
```

### Carga de Datos Asíncrona

```typescript
// Cargar datos al montar componente
useEffect(() => {
  const loadData = async () => {
    const result = await execute(() =>
      apiService.get('/data')
    );

    if (result?.success) {
      setData(result.data);
    }
  };

  loadData();
}, [execute]);
```

## 🛠️ Mejores Prácticas

### 1. Manejo de Loading States
```typescript
const { loading } = useApi();
// Mostrar spinners o skeletons durante loading
```

### 2. Manejo de Errores Global
```typescript
// Usar un context para manejar errores globales
const ErrorContext = createContext();

// Mostrar toasts o modales para errores
```

### 3. Reintentos Automáticos
```typescript
// Para operaciones fallidas por red
const retryApiCall = async (apiCall, maxRetries = 3) => {
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await apiCall();
    } catch (error) {
      if (i === maxRetries - 1) throw error;
      await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
    }
  }
};
```

### 4. Cache de Respuestas
```typescript
// Implementar cache para datos que no cambian frecuentemente
const cache = new Map();

const cachedApiCall = async (key, apiCall) => {
  if (cache.has(key)) {
    return cache.get(key);
  }

  const result = await apiCall();
  cache.set(key, result);
  return result;
};
```

## 📚 Referencias

- [Configuración API](../config/api_responses.php)
- [Trait de Respuestas API](../app/Traits/ApiResponseTrait.php)
- [Controlador Base](../app/Http/Controllers/Controller.php)

---

**Nota**: Esta documentación se mantiene actualizada con los cambios en la API. Para cualquier duda o sugerencia, contactar al equipo de desarrollo backend.
