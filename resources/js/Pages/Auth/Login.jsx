import { useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../Components/ui/card';
import { Input } from '../../Components/ui/input';
import { Button } from '../../Components/ui/button';
import { Label } from '../../Components/ui/label';
import { Alert, AlertDescription } from '../../Components/ui/alert';
import { Loader2 } from 'lucide-react';

const Login = () => {
  const { data, setData, post, processing, errors, reset } = useForm({
    identifier: '',
    password: '',
    login_method: 'email',
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    post('/login', {
      onFinish: () => reset('password'),
    });
  };

  return (
    <div className="min-h-screen bg-background flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-primary rounded-2xl mb-4 shadow-lg">
            <span className="text-primary-foreground text-2xl">⚡</span>
          </div>
          <h1 className="text-3xl font-bold text-foreground mb-2">VatioCore</h1>
          <p className="text-muted-foreground">Sistema de Gestión de Proyectos Fotovoltaicos</p>
        </div>

        <Card className="shadow-xl">
          <CardHeader className="text-center">
            <CardTitle className="text-2xl">Iniciar Sesión</CardTitle>
            <CardDescription>Acceda al sistema de gestión</CardDescription>
          </CardHeader>

          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              {errors.identifier && (
                <Alert variant="destructive">
                  <AlertDescription>{errors.identifier}</AlertDescription>
                </Alert>
              )}

              {/* Login Method Selector */}
              <div className="flex gap-2">
                <Button
                  type="button"
                  variant={data.login_method === 'email' ? 'default' : 'outline'}
                  onClick={() => setData('login_method', 'email')}
                  className="flex-1"
                  size="sm"
                >
                  Correo
                </Button>
                <Button
                  type="button"
                  variant={data.login_method === 'username' ? 'default' : 'outline'}
                  onClick={() => setData('login_method', 'username')}
                  className="flex-1"
                  size="sm"
                >
                  Usuario
                </Button>
              </div>

              <div className="space-y-2">
                <Label htmlFor="identifier">
                  {data.login_method === 'email' ? 'Correo Electrónico' : 'Nombre de Usuario'}
                </Label>
                <Input
                  id="identifier"
                  type={data.login_method === 'email' ? 'email' : 'text'}
                  value={data.identifier}
                  onChange={(e) => setData('identifier', e.target.value)}
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="password">Contraseña</Label>
                <Input
                  id="password"
                  type="password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  required
                />
              </div>

              <Button
                type="submit"
                disabled={processing}
                className="w-full"
                size="lg"
              >
                {processing ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Iniciando sesión...
                  </>
                ) : (
                  'Iniciar Sesión'
                )}
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default Login;
