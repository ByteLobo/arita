<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_invitado_es_redirigido_al_login(): void
    {
        $this->get('/dashboard')->assertRedirectToRoute('login');
    }

    public function test_administrador_puede_iniciar_sesion_y_ver_dashboard(): void
    {
        $this->seed();

        $respuesta = $this->post(route('login.store'), [
            'email' => 'admin@farmacia.test',
            'password' => 'Admin123!',
        ]);

        $respuesta->assertRedirectToRoute('dashboard');
        $this->assertAuthenticatedAs(Usuario::query()->where('email', 'admin@farmacia.test')->first());
        $this->get(route('dashboard'))->assertOk()->assertSee('Medicamentos activos');
    }

    public function test_credenciales_invalidas_no_crean_sesion(): void
    {
        $this->seed();

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => 'admin@farmacia.test', 'password' => 'incorrecta'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_limitado_rechaza_demasiados_intentos(): void
    {
        $this->seed();

        foreach (range(1, 5) as $intento) {
            $this->post(route('login.store'), [
                'email' => 'admin@farmacia.test',
                'password' => 'incorrecta',
            ])->assertRedirect(route('login'));
        }

        $this->post(route('login.store'), [
            'email' => 'admin@farmacia.test',
            'password' => 'incorrecta',
        ])->assertTooManyRequests();
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $this->seed();
        Usuario::query()->where('email', 'vendedor@farmacia.test')->update(['activo' => false]);

        $this->post(route('login.store'), [
            'email' => 'vendedor@farmacia.test',
            'password' => 'Vendedor123!',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_vendedor_puede_ver_operaciones_pero_no_panel_administrativo(): void
    {
        $this->seed();
        $vendedor = Usuario::query()->where('email', 'vendedor@farmacia.test')->firstOrFail();

        $this->actingAs($vendedor)->get(route('vendedor.panel'))->assertOk()->assertSee('Operaciones de vendedor');
        $this->actingAs($vendedor)->get(route('admin.panel'))->assertForbidden();
    }

    public function test_administrador_puede_ver_panel_administrativo(): void
    {
        $this->seed();
        $admin = Usuario::query()->where('email', 'admin@farmacia.test')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.panel'))->assertOk()->assertSee('Módulos administrativos');
    }

    public function test_logout_invalida_la_sesion(): void
    {
        $this->seed();
        $admin = Usuario::query()->where('email', 'admin@farmacia.test')->firstOrFail();

        $this->actingAs($admin)->post(route('logout'))->assertRedirectToRoute('login');
        $this->assertGuest();
    }
}
