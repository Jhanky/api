# Endpoints de Autenticación APK - Energy4Cero

## Descripción
Endpoints de autenticación optimizados para la aplicación móvil React Native. Incluyen funcionalidades específicas para dispositivos móviles con tokens de larga duración y validaciones optimizadas.

## Base URL
```
/apk
```

## Autenticación
Los endpoints protegidos requieren autenticación mediante Sanctum con token Bearer.

## Endpoints de Autenticación

### 1. Login de Usuario
**POST** `/apk/auth/login`

Inicia sesión de usuario en la aplicación móvil.

#### Parámetros
| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `email` | string | Sí | Email del usuario |
| `password` | string | Sí | Contraseña del usuario |
| `device_name` | string | Sí | Nombre del dispositivo móvil |

#### Ejemplo de Petición
```json
{
  "email": "jhan.martinez@energy4cero.com",
  "password": "miPassword123",
  "device_name": "Samsung Galaxy S21"
}
```

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Jhan Martinez",
      "email": "jhan.martinez@energy4cero.com",
      "avatar": "https://tu-dominio.com/storage/avatars/user-1.jpg",
      "is_active": true,
      "created_at": "2024-01-15T10:30:00.000000Z"
    },
    "token": "1|abcdef1234567890abcdef1234567890abcdef12",
    "token_type": "Bearer",
    "expires_at": "2025-02-15T10:30:00.000000Z"
  },
  "message": "Login exitoso"
}
```

#### Respuestas de Error

**Credenciales Incorrectas (401)**
```json
{
  "success": false,
  "message": "Credenciales incorrectas"
}
```

**Usuario Inactivo (403)**
```json
{
  "success": false,
  "message": "Usuario inactivo"
}
```

**Datos de Validación Incorrectos (422)**
```json
{
  "success": false,
  "message": "Datos de validación incorrectos",
  "errors": {
    "email": ["El correo electrónico es obligatorio."],
    "password": ["La contraseña debe tener al menos 6 caracteres."],
    "device_name": ["El nombre del dispositivo es obligatorio."]
  }
}
```

### 2. Logout de Usuario
**POST** `/apk/auth/logout`

Cierra la sesión del usuario y elimina el token actual.

#### Headers Requeridos
```
Authorization: Bearer {token}
```

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Logout exitoso"
}
```

#### Respuesta de Error (401)
```json
{
  "success": false,
  "message": "Sesión expirada. Por favor, inicia sesión nuevamente.",
  "error": "Unauthenticated",
  "action_required": "login"
}
```

### 3. Información del Usuario
**GET** `/apk/auth/me`

Obtiene la información del usuario autenticado.

#### Headers Requeridos
```
Authorization: Bearer {token}
```

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Jhan Martinez",
    "email": "jhan.martinez@energy4cero.com",
    "avatar": "https://tu-dominio.com/storage/avatars/user-1.jpg",
    "is_active": true,
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "display_name": "Administrador"
      }
    ],
    "created_at": "2024-01-15T10:30:00.000000Z"
  },
  "message": "Usuario obtenido exitosamente"
}
```

### 4. Refrescar Token
**POST** `/apk/auth/refresh`

Renueva el token de autenticación del usuario.

#### Headers Requeridos
```
Authorization: Bearer {token}
```

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": {
    "token": "2|nuevoToken1234567890abcdef1234567890abcdef12",
    "token_type": "Bearer",
    "expires_at": "2025-02-15T10:30:00.000000Z"
  },
  "message": "Token refrescado exitosamente"
}
```

## Ejemplos de Uso

### JavaScript/React Native
```javascript
// Login
const loginUser = async (email, password, deviceName) => {
  try {
    const response = await fetch('http://tu-dominio.com/apk/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: email,
        password: password,
        device_name: deviceName
      })
    });

    const data = await response.json();
    
    if (data.success) {
      // Guardar token y datos del usuario
      await AsyncStorage.setItem('auth_token', data.data.token);
      await AsyncStorage.setItem('user_data', JSON.stringify(data.data.user));
      return { success: true, user: data.data.user, token: data.data.token };
    } else {
      return { success: false, message: data.message };
    }
  } catch (error) {
    return { success: false, message: 'Error de conexión' };
  }
};

// Petición autenticada
const fetchUserInfo = async () => {
  const token = await AsyncStorage.getItem('auth_token');
  
  const response = await fetch('http://tu-dominio.com/apk/auth/me', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  return await response.json();
};

// Logout
const logoutUser = async () => {
  const token = await AsyncStorage.getItem('auth_token');
  
  const response = await fetch('http://tu-dominio.com/apk/auth/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  if (response.ok) {
    await AsyncStorage.removeItem('auth_token');
    await AsyncStorage.removeItem('user_data');
  }
  
  return await response.json();
};
```

### Flutter/Dart
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class AuthService {
  static const String baseUrl = 'http://tu-dominio.com/apk';
  
  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
    required String deviceName,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
          'device_name': deviceName,
        }),
      );

      final data = jsonDecode(response.body);
      return data;
    } catch (e) {
      return {
        'success': false,
        'message': 'Error de conexión: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> getUserInfo(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/auth/me'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
    
    return jsonDecode(response.body);
  }
  
  Future<Map<String, dynamic>> logout(String token) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/logout'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
    
    return jsonDecode(response.body);
  }
}
```

### cURL
```bash
# Login
curl -X POST "http://tu-dominio.com/apk/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "jhan.martinez@energy4cero.com",
    "password": "miPassword123",
    "device_name": "Samsung Galaxy S21"
  }'

# Obtener información del usuario
curl -X GET "http://tu-dominio.com/apk/auth/me" \
  -H "Authorization: Bearer 1|abcdef1234567890abcdef1234567890abcdef12" \
  -H "Accept: application/json"

# Logout
curl -X POST "http://tu-dominio.com/apk/auth/logout" \
  -H "Authorization: Bearer 1|abcdef1234567890abcdef1234567890abcdef12" \
  -H "Accept: application/json"

# Refrescar token
curl -X POST "http://tu-dominio.com/apk/auth/refresh" \
  -H "Authorization: Bearer 1|abcdef1234567890abcdef1234567890abcdef12" \
  -H "Accept: application/json"
```

## Características Especiales para Móvil

### 1. Tokens de Larga Duración
- Los tokens expiran en **30 días** para reducir la frecuencia de login
- Incluyen scope `mobile` para identificar tokens de aplicación móvil

### 2. Optimizaciones de Red
- Respuestas comprimidas para reducir el uso de datos
- Headers de cache optimizados para móvil
- Middleware específico para aplicaciones móviles

### 3. Validaciones Específicas
- Verificación de usuario activo
- Validación de scope de token móvil
- Manejo de errores específico para móvil

### 4. Seguridad
- Tokens con scope limitado a móvil
- Verificación de dispositivo
- Rate limiting específico para móvil

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | Operación exitosa |
| 401 | No autenticado / Credenciales incorrectas |
| 403 | Usuario inactivo / Token no válido para móvil |
| 422 | Datos de validación incorrectos |
| 500 | Error interno del servidor |

## Manejo de Errores

### Errores de Autenticación
```json
{
  "success": false,
  "message": "Sesión expirada. Por favor, inicia sesión nuevamente.",
  "error": "Unauthenticated",
  "action_required": "login"
}
```

### Errores de Cuenta
```json
{
  "success": false,
  "message": "Tu cuenta ha sido desactivada. Contacta al administrador.",
  "error": "Account deactivated",
  "action_required": "contact_admin"
}
```

### Errores de Token
```json
{
  "success": false,
  "message": "Token no válido para aplicación móvil",
  "error": "Invalid token scope",
  "action_required": "refresh_token"
}
```

## Notas Importantes

1. **URL Base**: Reemplaza `http://tu-dominio.com` con tu dominio real
2. **Almacenamiento**: Guarda el token de forma segura en el dispositivo
3. **Expiración**: Los tokens expiran en 30 días
4. **Refresh**: Usa el endpoint de refresh antes de que expire el token
5. **Logout**: Siempre llama al logout al cerrar la aplicación
6. **Device Name**: Usa nombres descriptivos para identificar dispositivos
7. **Headers**: Incluye siempre los headers `Content-Type` y `Accept`
8. **Manejo de Errores**: Implementa manejo robusto de errores de red

## Endpoints Relacionados

- **Dashboard**: `/apk/dashboard/*`
- **Proyectos**: `/apk/projects/*`
- **Clientes**: `/apk/clients/*`

## Versión de API

- **Versión**: 1.0.0
- **Última actualización**: 2025-01-15
- **Compatibilidad**: React Native, Flutter, Xamarin
