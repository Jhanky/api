<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VatioCore API - Estado y Servicios</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            color: #1f2937;
            line-height: 1.6;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 1400px;
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
            font-size: 3.5rem;
            margin: 0 0 1rem 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header p {
            font-size: 1.3rem;
            color: #6b7280;
            margin: 0 0 1rem 0;
        }

        .status-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            animation: pulse 2s infinite;
        }

        .status-badge .pulse {
            width: 12px;
            height: 12px;
            background: #fff;
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .version-badge {
            background: rgba(255, 255, 255, 0.9);
            color: #1e40af;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #1e40af;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            color: #6b7280;
            margin: 0.5rem 0 0 0;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .services-section {
            margin-bottom: 3rem;
        }

        .section-title {
            text-align: center;
            color: white;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .service-icon {
            font-size: 2rem;
        }

        .service-title {
            color: #1e40af;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
        }

        .endpoint {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin: 0.5rem 0;
            border-left: 4px solid #3b82f6;
            transition: background-color 0.3s ease;
        }

        .endpoint:hover {
            background: #e2e8f0;
        }

        .method {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.8rem;
            margin-right: 0.5rem;
            text-transform: uppercase;
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
            font-size: 0.9rem;
        }

        .description {
            color: #6b7280;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .footer {
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .footer p {
            margin: 0.5rem 0;
            color: #6b7280;
        }

        .footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .system-info {
            background: rgba(255, 255, 255, 0.9);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .system-info strong {
            color: #1e40af;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .status-container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 VatioCore API</h1>
            <p>Sistema de Gestión Integral de Proyectos Fotovoltaicos</p>
            <div class="status-container">
                <div class="status-badge">
                    <div class="pulse"></div>
                    API Funcionando Correctamente
                </div>
                <div class="version-badge">
                    Versión 1.0.0
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">12</div>
                <div class="stat-label">Servicios Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">50+</div>
                <div class="stat-label">Endpoints Disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">15</div>
                <div class="stat-label">Modelos de Datos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">99.9%</div>
                <div class="stat-label">Disponibilidad</div>
            </div>
        </div>

        <div class="services-section">
            <h2 class="section-title">🔧 Servicios Activos</h2>

            <div class="services-grid">
                <!-- Autenticación -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">🔐</span>
                        <h3 class="service-title">Autenticación</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span>
                        <span class="endpoint-url">/api/auth/login</span>
                        <div class="description">Inicio de sesión de usuarios</div>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span>
                        <span class="endpoint-url">/api/auth/logout</span>
                        <div class="description">Cerrar sesión</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/auth/me</span>
                        <div class="description">Información del usuario autenticado</div>
                    </div>
                </div>

                <!-- Usuarios y Roles -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">👥</span>
                        <h3 class="service-title">Usuarios & Roles</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/users</span>
                        <div class="description">Listar usuarios del sistema</div>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span>
                        <span class="endpoint-url">/api/users</span>
                        <div class="description">Crear nuevo usuario</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/roles</span>
                        <div class="description">Listar roles disponibles</div>
                    </div>
                </div>

                <!-- Clientes -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">🏢</span>
                        <h3 class="service-title">Clientes</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/clients</span>
                        <div class="description">Listar todos los clientes</div>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span>
                        <span class="endpoint-url">/api/clients</span>
                        <div class="description">Crear nuevo cliente</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/clients-statistics</span>
                        <div class="description">Estadísticas de clientes</div>
                    </div>
                </div>

                <!-- Componentes Técnicos -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">⚡</span>
                        <h3 class="service-title">Componentes Técnicos</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/panels</span>
                        <div class="description">Paneles solares disponibles</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/inverters</span>
                        <div class="description">Inversores fotovoltaicos</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/batteries</span>
                        <div class="description">Baterías de almacenamiento</div>
                    </div>
                </div>

                <!-- Cotizaciones -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">📋</span>
                        <h3 class="service-title">Cotizaciones</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/quotations</span>
                        <div class="description">Listar cotizaciones</div>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span>
                        <span class="endpoint-url">/api/quotations</span>
                        <div class="description">Crear nueva cotización</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/quotations/{id}/pdf</span>
                        <div class="description">Descargar PDF de cotización</div>
                    </div>
                </div>

                <!-- Ubicaciones -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">📍</span>
                        <h3 class="service-title">Ubicaciones</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/locations</span>
                        <div class="description">Listar ubicaciones</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/locations/departments</span>
                        <div class="description">Departamentos disponibles</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/locations/cities</span>
                        <div class="description">Ciudades por departamento</div>
                    </div>
                </div>

                <!-- Facturas -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">📄</span>
                        <h3 class="service-title">Facturas</h3>
                    </div>
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
                        <span class="endpoint-url">/api/invoices/export</span>
                        <div class="description">Exportar facturas a Excel</div>
                    </div>
                </div>

                <!-- Centros de Costo -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">💰</span>
                        <h3 class="service-title">Centros de Costo</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/cost-centers</span>
                        <div class="description">Listar centros de costo</div>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span>
                        <span class="endpoint-url">/api/cost-centers</span>
                        <div class="description">Crear centro de costo</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/cost-centers-statistics</span>
                        <div class="description">Estadísticas financieras</div>
                    </div>
                </div>

                <!-- Proveedores -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">🏭</span>
                        <h3 class="service-title">Proveedores</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/providers</span>
                        <div class="description">Listar proveedores</div>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span>
                        <span class="endpoint-url">/api/providers</span>
                        <div class="description">Crear nuevo proveedor</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/providers-statistics</span>
                        <div class="description">Estadísticas de proveedores</div>
                    </div>
                </div>

                <!-- Dashboard -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">📊</span>
                        <h3 class="service-title">Dashboard</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/dashboard/stats</span>
                        <div class="description">Estadísticas generales</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/dashboard/projects</span>
                        <div class="description">Datos de proyectos</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/health</span>
                        <div class="description">Verificación de salud API</div>
                    </div>
                </div>

                <!-- Proyectos -->
                <div class="service-card">
                    <div class="service-header">
                        <span class="service-icon">🏗️</span>
                        <h3 class="service-title">Proyectos</h3>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/projects</span>
                        <div class="description">Listar proyectos</div>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span>
                        <span class="endpoint-url">/api/projects</span>
                        <div class="description">Crear nuevo proyecto</div>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/api/projects/statistics</span>
                        <div class="description">Estadísticas de proyectos</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="system-info">
                <p><strong>Servidor:</strong> Laravel {{ app()->version() }} | <strong>PHP:</strong> {{ PHP_VERSION }}</p>
                <p><strong>Base de Datos:</strong> MySQL | <strong>Timestamp:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
                <p><strong>Zona Horaria:</strong> {{ config('app.timezone') }} | <strong>Entorno:</strong> {{ config('app.env') }}</p>
            </div>
            <p style="margin-top: 1rem;">
                <strong>📚 Documentación:</strong> <a href="/api-info">Ver documentación detallada</a> |
                <strong>🔗 Estado JSON:</strong> <a href="/api-status">Ver estado en JSON</a> |
                <strong>🌐 Frontend:</strong> <a href="{{ env('FRONTEND_URL', 'http://localhost:3000') }}" target="_blank">Ir al frontend React</a>
            </p>
        </div>
    </div>
</body>
</html>
