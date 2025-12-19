import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../Components/ui/card';
import { Button } from '../../Components/ui/button';
import { router } from '@inertiajs/react';

const Dashboard = ({ user }) => {
  const handleLogout = () => {
    router.post('/logout');
  };

  return (
    <>
      <Head title="Dashboard - VatioCore" />

      <div className="min-h-screen bg-background p-4">
        <div className="max-w-7xl mx-auto">
          <div className="flex justify-between items-center mb-8">
            <div>
              <h1 className="text-3xl font-bold text-foreground">Dashboard</h1>
              <p className="text-muted-foreground">Bienvenido al sistema de gestión</p>
            </div>
            <Button onClick={handleLogout} variant="outline">
              Cerrar Sesión
            </Button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Usuario</CardTitle>
                <CardDescription>Información del usuario actual</CardDescription>
              </CardHeader>
              <CardContent>
                <p><strong>Nombre:</strong> {user.name}</p>
                <p><strong>Email:</strong> {user.email}</p>
                <p><strong>Usuario:</strong> {user.username}</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Estado del Sistema</CardTitle>
                <CardDescription>Información del sistema</CardDescription>
              </CardHeader>
              <CardContent>
                <p>✅ Autenticación funcionando</p>
                <p>✅ Inertia.js integrado</p>
                <p>🔄 Migración en progreso</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Próximos Pasos</CardTitle>
                <CardDescription>Módulos por migrar</CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="space-y-1 text-sm">
                  <li>• Gestión de Clientes</li>
                  <li>• Cotizaciones</li>
                  <li>• Inventario</li>
                  <li>• Proyectos</li>
                </ul>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </>
  );
};

export default Dashboard;
