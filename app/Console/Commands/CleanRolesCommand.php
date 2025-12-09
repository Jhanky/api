<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\User;

class CleanRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina todos los roles excepto: administrador, gerente, tecnico, contador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $allowedRoles = ['administrador', 'gerente', 'tecnico', 'contador'];
        
        $this->info('Iniciando limpieza de roles...');
        
        // Obtener todos los roles que no están en la lista permitida
        $rolesToDelete = Role::whereNotIn('name', $allowedRoles)->get();
        
        if ($rolesToDelete->count() > 0) {
            $this->info('Roles a eliminar:');
            foreach ($rolesToDelete as $role) {
                $this->line("- {$role->name} ({$role->display_name})");
            }
            
            // Desasociar usuarios de estos roles antes de eliminarlos
            foreach ($rolesToDelete as $role) {
                $users = $role->users;
                if ($users->count() > 0) {
                    $this->warn("Desasociando {$users->count()} usuarios del rol '{$role->name}'...");
                    $role->users()->detach();
                }
            }
            
            // Eliminar los roles
            $deletedCount = Role::whereNotIn('name', $allowedRoles)->delete();
            $this->info("✅ Se eliminaron {$deletedCount} roles no deseados.");
        } else {
            $this->info('✅ No hay roles no deseados para eliminar.');
        }
        
        // Verificar roles actuales
        $currentRoles = Role::all();
        $this->info("\nRoles actuales en la base de datos:");
        foreach ($currentRoles as $role) {
            $this->line("- {$role->name} ({$role->display_name})");
        }
        
        $this->info("\n✅ Limpieza de roles completada.");
    }
}
