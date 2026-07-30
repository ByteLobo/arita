<?php

namespace Tests\Feature\Admin;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Usuario
    {
        return Usuario::query()->where('email', 'admin@farmacia.test')->firstOrFail();
    }

    private function vendedor(): Usuario
    {
        return Usuario::query()->where('email', 'vendedor@farmacia.test')->firstOrFail();
    }

    public function test_administrador_puede_crear_usuario(): void
    {
        $this->seed();
        $rol = Rol::query()->where('nombre', 'Vendedor')->firstOrFail();

        $this->actingAs($this->admin())->post(route('admin.usuarios.store'), [
            'nombre' => 'Vendedor Nuevo',
            'email' => 'nuevo@farmacia.test',
            'password' => 'NuevaClave123!',
            'password_confirmation' => 'NuevaClave123!',
            'id_rol' => $rol->id_rol,
            'activo' => 1,
        ])->assertRedirectToRoute('admin.usuarios.index');

        $this->assertDatabaseHas('usuarios', ['email' => 'nuevo@farmacia.test', 'id_rol' => $rol->id_rol, 'activo' => true]);
    }

    public function test_vendedor_no_puede_gestionar_usuarios_ni_roles(): void
    {
        $this->seed();

        $this->actingAs($this->vendedor())->get(route('admin.usuarios.index'))->assertForbidden();
        $this->actingAs($this->vendedor())->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_no_se_puede_desactivar_el_usuario_actual(): void
    {
        $this->seed();

        $this->actingAs($this->admin())->delete(route('admin.usuarios.destroy', $this->admin()))
            ->assertSessionHasErrors('usuario');

        $this->assertTrue($this->admin()->fresh()->activo);
    }

    public function test_no_se_puede_asignar_un_rol_inactivo(): void
    {
        $this->seed();
        $rol = Rol::query()->where('nombre', 'Vendedor')->firstOrFail();
        $rol->update(['activo' => false]);

        $this->actingAs($this->admin())->post(route('admin.usuarios.store'), [
            'nombre' => 'Usuario con rol inactivo',
            'email' => 'rol-inactivo@farmacia.test',
            'password' => 'NuevaClave123!',
            'password_confirmation' => 'NuevaClave123!',
            'id_rol' => $rol->id_rol,
        ])->assertSessionHasErrors('id_rol');
    }

    public function test_administrador_puede_crear_rol(): void
    {
        $this->seed();

        $this->actingAs($this->admin())->post(route('admin.roles.store'), [
            'nombre' => 'Supervisor',
        ])->assertRedirectToRoute('admin.roles.index');

        $this->assertDatabaseHas('roles', ['nombre' => 'Supervisor', 'activo' => true]);
    }
}
