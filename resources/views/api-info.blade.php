<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Energy4Cero - Documentación</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 25%, #60a5fa 50%, #93c5fd 75%, #dbeafe 100%);
            background-size: 400% 400%;
            animation: gradientShift 10s ease infinite;
            min-height: 100vh;
            color: #1f2937;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .header h1 {
            color: #1e40af;
            font-size: 3rem;
            margin: 0 0 1rem 0;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.2rem;
            color: #6b7280;
            margin: 0;
        }
        
        .status-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 1rem;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card h3 {
            color: #1e40af;
            margin-top: 0;
            font-size: 1.5rem;
        }
        
        .endpoint {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin: 0.5rem 0;
            border-left: 4px solid #3b82f6;
        }
        
        .method {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }
        
        .method.get { background: #10b981; color: white; }
        .method.post { background: #f59e0b; color: white; }
        .method.put { background: #3b82f6; color: white; }
        .method.patch { background: #8b5cf6; color: white; }
        .method.delete { background: #ef4444; color: white; }
        
        .endpoint-url {
            font-family: 'Courier New', monospace;
            color: #1f2937;
            font-weight: 600;
        }
        
        .description {
            color: #6b7280;
            margin-top: 0.5rem;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e40af;
            margin: 0;
        }
        
        .stat-label {
            color: #6b7280;
            margin: 0.5rem 0 0 0;
        }
        
        .footer {
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 API Energy4Cero</h1>
            <p>Sistema de Gestión de Facturas y Contabilidad</p>
            <div class="status-badge">✅ API Funcionando Correctamente</div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">13</div>
                <div class="stat-label">Endpoints de Facturas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">15</div>
                <div class="stat-label">Columnas en Excel</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">4</div>
                <div class="stat-label">Roles de Usuario</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">3</div>
                <div class="stat-label">Métodos de Pago</div>
            </div>
        </div>
        
        <div class="grid">
            <div class="card">
                <h3>📄 Gestión de Facturas</h3>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="endpoint-url">/api/invoices</span>
                    <div class="description">Listar facturas con filtros</div>
                </div>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="endpoint-url">/api/invoices</span>
                    <div class="description">Crear nueva factura</div>
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="endpoint-url">/api/invoices/{id}</span>
                    <div class="description">Mostrar factura específica</div>
                </div>
                <div class="endpoint">
                    <span class="method put">PUT</span>
                    <span class="endpoint-url">/api/invoices/{id}</span>
                    <div class="description">Actualizar factura</div>
                </div>
                <div class="endpoint">
                    <span class="method delete">DELETE</span>
                    <span class="endpoint-url">/api/invoices/{id}</span>
                    <div class="description">Eliminar factura</div>
                </div>
            </div>
            
            <div class="card">
                <h3>📊 Reportes y Análisis</h3>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="endpoint-url">/api/invoices/export</span>
                    <div class="description">Exportar a Excel</div>
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="endpoint-url">/api/invoices/statistics</span>
                    <div class="description">Estadísticas de facturas</div>
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="endpoint-url">/api/invoices/test-report</span>
                    <div class="description">Probar consulta de reporte</div>
                </div>
            </div>
            
            <div class="card">
                <h3>🔧 Funciones Especializadas</h3>
                <div class="endpoint">
                    <span class="method patch">PATCH</span>
                    <span class="endpoint-url">/api/invoices/{id}/status</span>
                    <div class="description">Actualizar estado</div>
                </div>
                <div class="endpoint">
                    <span class="method patch">PATCH</span>
                    <span class="endpoint-url">/api/invoices/{id}/cost-center</span>
                    <div class="description">Cambiar centro de costo</div>
                </div>
                <div class="endpoint">
                    <span class="method patch">PATCH</span>
                    <span class="endpoint-url">/api/invoices/{id}/retention</span>
                    <div class="description">Aplicar/remover retención</div>
                </div>
            </div>
            
            <div class="card">
                <h3>📁 Gestión de Archivos</h3>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="endpoint-url">/api/invoices/{id}/upload-files</span>
                    <div class="description">Subir archivos a factura</div>
                </div>
                <div class="endpoint">
                    <span class="method delete">DELETE</span>
                    <span class="endpoint-url">/api/invoices/{id}/remove-files</span>
                    <div class="description">Eliminar archivos de factura</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>🎯 Características Principales</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 0.5rem 0; padding: 0.5rem; background: #f0f9ff; border-radius: 5px;">✅ <strong>Registro Progresivo:</strong> Crear facturas y agregar archivos después</li>
                <li style="margin: 0.5rem 0; padding: 0.5rem; background: #f0f9ff; border-radius: 5px;">✅ <strong>Exportación Excel:</strong> 15 columnas con filtros y colores</li>
                <li style="margin: 0.5rem 0; padding: 0.5rem; background: #f0f9ff; border-radius: 5px;">✅ <strong>Tipos de Compra:</strong> Contado y Crédito diferenciados</li>
                <li style="margin: 0.5rem 0; padding: 0.5rem; background: #f0f9ff; border-radius: 5px;">✅ <strong>Retención Opcional:</strong> Sí/No con colores</li>
                <li style="margin: 0.5rem 0; padding: 0.5rem; background: #f0f9ff; border-radius: 5px;">✅ <strong>Métodos de Pago:</strong> TCD, CP, EF</li>
                <li style="margin: 0.5rem 0; padding: 0.5rem; background: #f0f9ff; border-radius: 5px;">✅ <strong>Roles de Usuario:</strong> Administrador, Gerente, Técnico, Contador</li>
            </ul>
        </div>
        
        <div class="footer">
            <p><strong>📚 Documentación Completa:</strong> <a href="/docs">Ver Documentación</a></p>
            <p><strong>🔗 Estado de la API:</strong> <a href="/api-status">Verificar Estado</a></p>
            <p><strong>📅 Última actualización:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
