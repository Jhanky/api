<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class MigrateUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:migrate-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra los roles de usuarios del campo role_id directo a la tabla pivot role_user (Sistema Simplificado)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando migración de roles (Sistema Simplificado - Solo Roles)...');

        // Obtener todos los usuarios que tienen role_id asignado
        $usersWithRoleId = User::whereNotNull('role_id')->get();

        if ($usersWithRoleId->isEmpty()) {
            $this->info('✅ No hay usuarios con role_id directo para migrar.');
            return;
        }

        $this->info("📊 Encontrados {$usersWithRoleId->count()} usuarios con role_id asignado.");

        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($usersWithRoleId as $user) {
            try {
                // Verificar si el rol existe
                $role = Role::find($user->role_id);

                if (!$role) {
                    $this->error("❌ Rol con ID {$user->role_id} no encontrado para usuario {$user->email}");
                    $errors++;
                    continue;
                }

                // Verificar si ya existe la relación en la tabla pivot
                $existingPivot = DB::table('role_user')
                    ->where('user_id', $user->id)
                    ->where('role_id', $user->role_id)
                    ->exists();

                if ($existingPivot) {
                    $this->warn("⚠️  Usuario {$user->email} ya tiene el rol '{$role->name}' asignado. Omitiendo...");
                    $skipped++;
                    continue;
                }

                // Crear la relación en la tabla pivot
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $user->role_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->line("✅ Migrado: {$user->email} → {$role->name}");
                $migrated++;

            } catch (\Exception $e) {
                $this->error("❌ Error migrando usuario {$user->email}: {$e->getMessage()}");
                $errors++;
            }
        }

        // Limpiar el campo role_id de todos los usuarios (recomendado en sistema simplificado)
        if ($this->confirm('¿Deseas limpiar el campo role_id de todos los usuarios? (Recomendado)', true)) {
            User::whereNotNull('role_id')->update(['role_id' => null]);
            $this->info('🧹 Campo role_id limpiado de todos los usuarios.');
        }

        // Mostrar resumen
        $this->info("\n📈 Resumen de migración:");
        $this->line("✅ Migrados: {$migrated}");
        $this->line("⚠️  Omitidos: {$skipped}");
        $this->line("❌ Errores: {$errors}");

        // Verificar relaciones actuales
        $totalRelations = DB::table('role_user')->count();
        $this->info("📊 Total de relaciones rol-usuario: {$totalRelations}");

        $this->info("\n🎉 Migración completada exitosamente!");
        $this->info("💡 Sistema simplificado: Ahora solo se usan roles, sin permisos específicos.");
    }
}
