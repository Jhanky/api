# VatioCore MCP Server

Servidor MCP (Model Context Protocol) para integrar VatioCore con modelos LLM y n8n.

## Endpoints Disponibles

### HTTP Integrado (Laravel routing)
- **SSE**: `GET /mcp/sse`
- **Message**: `POST /mcp/message`

### HTTP Dedicado (servidor standalone)
- **Puerto**: `8090` (configurable)
- **Endpoint**: `http://127.0.0.1:8090/mcp`

## Herramientas Disponibles

| Nombre | Descripción |
|--------|-------------|
| `list_clients` | Lista clientes con filtros (search, clientTypeId, isActive, limit) |
| `get_client` | Obtiene detalle de un cliente por ID |
| `create_client` | Crea un nuevo cliente |
| `update_client` | Actualiza un cliente existente |

## Configuración en n8n

1. Agregar nodo **MCP Client**
2. Configurar el endpoint:
   - **URL**: `http://TU_SERVIDOR:8090/mcp` (HTTP dedicado) o `http://TU_SERVIDOR/mcp/message` (HTTP integrado)
   - **Transport**: HTTP

## Iniciar el Servidor

```bash
# HTTP dedicado (recomendado para n8n)
php artisan mcp:serve --transport=http

# STDIO (para Claude Desktop/Cursor)
php artisan mcp:serve --transport=stdio
```

## Comandos Útiles

```bash
# Descubrir herramientas
php artisan mcp:discover

# Listar herramientas registradas
php artisan mcp:list

# Probar con inspector
php artisan mcp:inspector
```

## Ejemplo de Uso

### list_clients
```json
{
  "search": "empresa",
  "isActive": true,
  "limit": 10
}
```

### create_client
```json
{
  "name": "Nueva Empresa S.A.",
  "email": "contacto@empresa.com",
  "phone": "3001234567",
  "nic": "123456789"
}
```

### update_client
```json
{
  "clientId": 1,
  "name": "Empresa Actualizada",
  "isActive": false
}
```
