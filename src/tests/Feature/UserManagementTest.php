<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Limpiar caché de permisos de Spatie entre tests
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Crear roles necesarios
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'instructor']);
        Role::create(['name' => 'aprendiz']);
        
        // Deshabilitar middleware problemáticos para testing
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\EnsureTwoFactorEnabledForPrivilegedRoles::class,
            \App\Http\Middleware\EnsureAdminHasPasskey::class,
        ]);
    }

    #[Test]
    public function admin_puede_registrar_nuevo_usuario()
    {
        // Arrange: Crear admin autenticado
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        // Act: Registrar nuevo usuario
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Juan Pérez',
            'email' => 'juan@bomberos.ec',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'aprendiz',
        ]);

        // Assert: Verificar que se creó el usuario
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Juan Pérez',
            'email' => 'juan@bomberos.ec',
        ]);
    }

    #[Test]
    public function admin_puede_desactivar_usuario()
    {
        // Arrange
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        
        $aprendiz = User::factory()->create();
        $aprendiz->assignRole('aprendiz');

        // Act: Desactivar usuario
        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $aprendiz));

        // Assert
        $response->assertRedirect(route('admin.users.index'));
        $aprendiz->refresh();
        $this->assertNotNull($aprendiz->disabled_at);
        $this->assertTrue($aprendiz->isDisabled());
    }

    #[Test]
    public function admin_puede_activar_usuario_desactivado()
    {
        // Arrange
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        
        $aprendiz = User::factory()->create([
            'disabled_at' => now(),
        ]);
        $aprendiz->assignRole('aprendiz');

        // Act: Activar usuario
        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $aprendiz));

        // Assert
        $response->assertRedirect(route('admin.users.index'));
        $aprendiz->refresh();
        $this->assertNull($aprendiz->disabled_at);
        $this->assertTrue($aprendiz->isActive());
    }

    #[Test]
    public function admin_no_puede_desactivarse_a_si_mismo()
    {
        // Arrange
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        // Act: Intentar auto-desactivarse
        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $admin->refresh();
        $this->assertNull($admin->disabled_at);
    }

    #[Test]
    public function admin_no_puede_desactivar_otro_admin()
    {
        // Arrange
        $admin1 = User::factory()->create(['email_verified_at' => now()]);
        $admin1->assignRole('admin');
        
        $admin2 = User::factory()->create(['email_verified_at' => now()]);
        $admin2->assignRole('admin');

        // Act: Intentar desactivar otro admin
        $response = $this->actingAs($admin1)->delete(route('admin.users.destroy', $admin2));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $admin2->refresh();
        $this->assertNull($admin2->disabled_at);
    }

    #[Test]
    public function admin_puede_asignar_permiso_modulo_ia()
    {
        // Arrange
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        
        $aprendiz = User::factory()->create();
        $aprendiz->assignRole('aprendiz');

        // Act: Asignar permiso de módulo IA
        $response = $this->actingAs($admin)->put(route('admin.users.update', $aprendiz), [
            'name' => $aprendiz->name,
            'email' => $aprendiz->email,
            'role' => 'aprendiz',
            'can_access_ai_module' => 1,
        ]);

        // Assert
        $response->assertRedirect(route('admin.users.index'));
        $aprendiz->refresh();
        $this->assertTrue($aprendiz->can_access_ai_module);
        $this->assertTrue($aprendiz->canAccessAiModule());
    }

    #[Test]
    public function admin_puede_asignar_permiso_estadisticas_a_instructor()
    {
        // Arrange
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        // Act: Asignar permiso de estadísticas
        $response = $this->actingAs($admin)->put(route('admin.users.update', $instructor), [
            'name' => $instructor->name,
            'email' => $instructor->email,
            'role' => 'instructor',
            'can_view_student_stats' => 1,
        ]);

        // Assert
        $response->assertRedirect(route('admin.users.index'));
        $instructor->refresh();
        $this->assertTrue($instructor->can_view_student_stats);
        $this->assertTrue($instructor->canViewStudentStats());
    }

    #[Test]
    public function registro_requiere_email_valido()
    {
        // Arrange
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        // Act: Intentar registrar con email inválido
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Juan Pérez',
            'email' => 'email-invalido',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'aprendiz',
        ]);

        // Assert
        $response->assertInvalid(['email']);
        $this->assertDatabaseMissing('users', [
            'name' => 'Juan Pérez',
        ]);
    }

    #[Test]
    public function registro_requiere_contrasena_confirmada()
    {
        // Arrange
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        // Act: Intentar registrar sin confirmar contraseña
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Juan Pérez',
            'email' => 'juan@bomberos.ec',
            'password' => 'Password123!',
            'password_confirmation' => 'Password456!',
            'role' => 'aprendiz',
        ]);

        // Assert
        $response->assertInvalid(['password']);
    }

    #[Test]
    public function usuario_desactivado_esta_marcado_correctamente()
    {
        // Arrange
        $user = User::factory()->create([
            'disabled_at' => now(),
        ]);
        $user->assignRole('aprendiz');

        // Assert: Usuario está desactivado
        $this->assertTrue($user->isDisabled());
        $this->assertFalse($user->isActive());
        $this->assertNotNull($user->disabled_at);
    }
}
