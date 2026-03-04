<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Limpiar caché de permisos de Spatie entre tests
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'instructor']);
        Role::create(['name' => 'aprendiz']);
    }

    #[Test]
    public function instructor_no_puede_acceder_panel_web()
    {
        // Arrange
        $instructor = User::factory()->create(['email_verified_at' => now()]);
        $instructor->assignRole('instructor');

        // Act: Intentar acceder al panel admin
        $response = $this->actingAs($instructor)->get(route('admin.zone'));

        // Assert: Debe ser bloqueado (403 Forbidden por middleware role)
        $response->assertForbidden();
    }

    #[Test]
    public function aprendiz_no_puede_acceder_panel_web()
    {
        // Arrange
        $aprendiz = User::factory()->create(['email_verified_at' => now()]);
        $aprendiz->assignRole('aprendiz');

        // Act
        $response = $this->actingAs($aprendiz)->get(route('admin.zone'));

        // Assert
        $response->assertForbidden();
    }

    #[Test]
    public function admin_puede_acceder_panel_web()
    {
        // Arrange - Admin sin 2FA ni passkey (simplificado para test)
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Deshabilitar middleware temporalmente para este test
        $this->withoutMiddleware([\App\Http\Middleware\EnsureTwoFactorEnabledForPrivilegedRoles::class, \App\Http\Middleware\EnsureAdminHasPasskey::class]);

        // Act
        $response = $this->actingAs($admin)->get(route('admin.zone'));

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function admin_no_puede_editar_otro_admin()
    {
        // Arrange
        $admin1 = User::factory()->create(['email_verified_at' => now()]);
        $admin1->assignRole('admin');
        
        $admin2 = User::factory()->create(['email_verified_at' => now()]);
        $admin2->assignRole('admin');

        $this->withoutMiddleware([\App\Http\Middleware\EnsureTwoFactorEnabledForPrivilegedRoles::class, \App\Http\Middleware\EnsureAdminHasPasskey::class]);

        // Act
        $response = $this->actingAs($admin1)->get(route('admin.users.edit', $admin2));

        // Assert
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function admin_puede_editar_su_propio_perfil()
    {
        // Arrange
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->withoutMiddleware([\App\Http\Middleware\EnsureTwoFactorEnabledForPrivilegedRoles::class, \App\Http\Middleware\EnsureAdminHasPasskey::class]);

        // Act
        $response = $this->actingAs($admin)->get(route('admin.users.edit', $admin));

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function permisos_vista_solo_aplican_a_no_admin()
    {
        // Arrange
        $admin = User::factory()->create([
            'can_access_ai_module' => true,
        ]);
        $admin->assignRole('admin');

        // Assert: Admin no debe tener acceso aunque tenga el flag
        $this->assertFalse($admin->canAccessAiModule());
    }

    #[Test]
    public function aprendiz_con_permiso_puede_acceder_modulo_ia()
    {
        // Arrange
        $aprendiz = User::factory()->create([
            'can_access_ai_module' => true,
        ]);
        $aprendiz->assignRole('aprendiz');

        // Assert
        $this->assertTrue($aprendiz->canAccessAiModule());
    }

    #[Test]
    public function aprendiz_sin_permiso_no_puede_acceder_modulo_ia()
    {
        // Arrange
        $aprendiz = User::factory()->create([
            'can_access_ai_module' => false,
        ]);
        $aprendiz->assignRole('aprendiz');

        // Assert
        $this->assertFalse($aprendiz->canAccessAiModule());
    }

    #[Test]
    public function solo_instructor_puede_ver_estadisticas()
    {
        // Arrange
        $instructor = User::factory()->create([
            'can_view_student_stats' => true,
        ]);
        $instructor->assignRole('instructor');

        $aprendiz = User::factory()->create([
            'can_view_student_stats' => true,
        ]);
        $aprendiz->assignRole('aprendiz');

        // Assert
        $this->assertTrue($instructor->canViewStudentStats());
        $this->assertFalse($aprendiz->canViewStudentStats());
    }
}
