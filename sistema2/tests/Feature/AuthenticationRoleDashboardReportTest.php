<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\DetailSale;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Concerns\CreatesUsers;
use Tests\TestCase;

class AuthenticationRoleDashboardReportTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_login_checks_credentials_preserves_intended_url_and_redirects_authenticated_guests(): void
    {
        $user = $this->createUserForRole('Administrador', [
            'email' => 'admin@example.test',
        ]);

        $this->get(route('medicines.index'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Iniciar sesión');
        $sessionIdBeforeLogin = $this->app['session.store']->getId();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => '1',
        ])->assertRedirect(route('medicines.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionIdBeforeLogin, $this->app['session.store']->getId());
        $this->get(route('login'))->assertRedirect(route('dashboard.index'));
    }

    public function test_invalid_login_uses_a_generic_message_and_keeps_only_the_email(): void
    {
        $this->createUserForRole('Administrador', [
            'email' => 'admin@example.test',
        ]);

        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'admin@example.test',
            'password' => 'incorrecta',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'El correo o la contraseña no son correctos.',
            ])
            ->assertSessionHas('_old_input.email', 'admin@example.test')
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    public function test_logout_invalidates_access_and_returns_to_login(): void
    {
        $this->withSession(['private-value' => 'must be removed'])
            ->actingAs($this->createUserForRole('Administrador'));
        $this->get(route('dashboard.index'))->assertOk();

        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('ok', 'Cerraste sesión correctamente.')
            ->assertSessionMissing('private-value');

        $this->assertGuest();
        $this->get(route('customers.index'))->assertRedirect(route('login'));
    }

    public function test_role_route_permissions_and_forbidden_page_match_the_guide(): void
    {
        $routesByRole = [
            'Administrador' => [
                'dashboard.index',
                'customers.index',
                'sales.index',
                'medicines.index',
                'reports.sales',
                'reports.sales.pdf',
                'reports.sales.excel',
                'categories.index',
                'roles.index',
                'users.index',
            ],
            'Químico farmacéutico' => [
                'dashboard.index',
                'customers.index',
                'sales.index',
                'medicines.index',
                'reports.sales',
                'reports.sales.pdf',
                'reports.sales.excel',
            ],
            'Cajero' => [
                'dashboard.index',
                'customers.index',
                'sales.index',
            ],
        ];

        foreach ($routesByRole as $role => $allowedRoutes) {
            $user = $this->createUserForRole($role);
            $this->actingAs($user);

            foreach ($allowedRoutes as $routeName) {
                $this->get(route($routeName))->assertOk();
            }

            $forbiddenRoutes = array_diff([
                'medicines.index',
                'reports.sales',
                'reports.sales.pdf',
                'reports.sales.excel',
                'categories.index',
                'roles.index',
                'users.index',
            ], $allowedRoutes);

            foreach ($forbiddenRoutes as $routeName) {
                $this->get(route($routeName))
                    ->assertForbidden()
                    ->assertSee('Acceso denegado');
            }
        }
    }

    public function test_sidebar_shows_only_links_allowed_for_the_current_role(): void
    {
        $cases = [
            'Administrador' => ['medicines.index', 'reports.sales', 'roles.index'],
            'Químico farmacéutico' => ['medicines.index', 'reports.sales'],
            'Cajero' => [],
        ];

        foreach ($cases as $role => $visibleLinks) {
            $this->actingAs($this->createUserForRole($role));
            $response = $this->get(route('dashboard.index'))->assertOk();

            foreach (['medicines.index', 'reports.sales', 'roles.index'] as $routeName) {
                if (in_array($routeName, $visibleLinks, true)) {
                    $response->assertSee(route($routeName), false);
                } else {
                    $response->assertDontSee(route($routeName), false);
                }
            }
        }
    }

    public function test_only_an_administrator_can_anul_a_sale(): void
    {
        $customer = Customer::create(['name' => 'Cliente', 'dni' => '12345678']);
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $this->createUserForRole('Cajero')->id,
            'date' => now(),
            'total' => 10,
            'igv' => 1.53,
        ]);

        $this->actingAs($this->createUserForRole('Cajero'));
        $this->delete(route('sales.destroy', $sale))
            ->assertForbidden()
            ->assertSee('Acceso denegado');

        $this->assertDatabaseHas('sales', ['id' => $sale->id]);
    }

    public function test_sales_list_hides_the_annul_action_from_non_administrators(): void
    {
        $seller = $this->createUserForRole('Cajero');
        $customer = Customer::create(['name' => 'Cliente', 'dni' => '12345678']);
        Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $seller->id,
            'date' => now(),
            'total' => 10,
            'igv' => 1.53,
        ]);

        $this->actingAs($seller)->get(route('sales.index'))
            ->assertOk()
            ->assertSee('Ver detalle')
            ->assertDontSee('Anular');

        $this->actingAs($this->createUserForRole('Administrador'))
            ->get(route('sales.index'))
            ->assertSee('Anular');
    }

    public function test_dashboard_calculates_four_indicators_and_three_real_graph_datasets(): void
    {
        $admin = $this->createUserForRole('Administrador');
        $customer = Customer::create(['name' => 'Cliente dashboard', 'dni' => '12345678']);
        $category = Category::create(['name' => 'Analgésicos']);
        $medicine = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Paracetamol',
            'price' => 59,
            'stock' => 10,
        ]);
        Medicine::create([
            'category_id' => $category->id,
            'name' => 'Ibuprofeno',
            'price' => 20,
            'stock' => 11,
        ]);
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $admin->id,
            'date' => now(),
            'total' => 118,
            'igv' => 18,
        ]);
        DetailSale::create([
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
            'amount' => 2,
            'price_unit' => 59,
            'subtotal' => 118,
        ]);

        $this->actingAs($admin)->get(route('dashboard.index'))
            ->assertOk()
            ->assertViewHas('salesToday', 118)
            ->assertViewHas('salesMonth', 118)
            ->assertViewHas('customers', 1)
            ->assertViewHas('lowStock', 1)
            ->assertViewHas('days', fn ($days) => end($days) === today()->format('d/m'))
            ->assertViewHas('totals', fn ($totals) => end($totals) === 118.0)
            ->assertViewHas('byCategory', fn ($rows) => $rows->first()->name === 'Analgésicos'
                && (float) $rows->first()->total === 118.0)
            ->assertViewHas('topMedicines', fn ($rows) => $rows->first()->name === 'Paracetamol'
                && (int) $rows->first()->units === 2)
            ->assertSee('chartDays')
            ->assertSee('chartCategories')
            ->assertSee('chartMedicines')
            ->assertSee('js/chart.umd.min.js');
    }

    public function test_report_filters_by_date_customer_seller_and_category_and_summarizes_results(): void
    {
        $seller = $this->createUserForRole('Químico farmacéutico');
        $otherSeller = $this->createUserForRole('Cajero');
        $customer = Customer::create(['name' => 'Cliente filtrado', 'dni' => '12345678']);
        $otherCustomer = Customer::create(['name' => 'Otro cliente', 'dni' => '87654321']);
        $category = Category::create(['name' => 'Analgésicos']);
        $otherCategory = Category::create(['name' => 'Antibióticos']);
        $medicine = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Paracetamol',
            'price' => 118,
            'stock' => 10,
        ]);
        $otherMedicine = Medicine::create([
            'category_id' => $otherCategory->id,
            'name' => 'Amoxicilina',
            'price' => 59,
            'stock' => 10,
        ]);
        $date = today()->setTime(23, 30);
        $matchingSale = $this->createSaleWithDetail($customer, $seller, $medicine, $date, 118, 18);
        $this->createSaleWithDetail($otherCustomer, $otherSeller, $otherMedicine, $date, 59, 9);

        $this->actingAs($seller)->get(route('reports.sales', [
            'from' => today()->toDateString(),
            'to' => today()->toDateString(),
            'customer_id' => $customer->id,
            'user_id' => $seller->id,
            'category_id' => $category->id,
        ]))
            ->assertOk()
            ->assertViewHas('summary', [
                'count' => 1,
                'total' => 118.0,
                'igv' => 18.0,
                'average' => 118.0,
            ])
            ->assertViewHas('sales', fn ($sales) => $sales->total() === 1
                && $sales->first()->id === $matchingSale->id)
            ->assertViewHas('chart', fn ($chart) => $chart->count() === 1
                && (float) $chart->first() === 118.0)
            ->assertSee('Cliente filtrado')
            ->assertSee('Ventas por rango de fechas y filtros');
    }

    public function test_report_defaults_to_current_month_and_rejects_invalid_dates_and_filters_in_spanish(): void
    {
        $this->actingAsRole('Administrador');

        $this->get(route('reports.sales'))
            ->assertOk()
            ->assertSee(today()->startOfMonth()->toDateString())
            ->assertSee(today()->toDateString());

        $this->from(route('reports.sales'))->get(route('reports.sales', [
            'from' => '2026-11-20',
            'to' => '2026-11-01',
        ]))
            ->assertRedirect(route('reports.sales'))
            ->assertSessionHasErrors([
                'to' => 'El campo fecha final debe ser una fecha igual o posterior a fecha inicial.',
            ]);

        $this->from(route('reports.sales'))->get(route('reports.sales', [
            'customer_id' => 999,
            'user_id' => 999,
            'category_id' => 999,
        ]))->assertSessionHasErrors(['customer_id', 'user_id', 'category_id']);
    }

    public function test_seeders_are_repeatable_and_do_not_overwrite_existing_user_data(): void
    {
        $role = Role::create(['name' => 'Administrador', 'description' => 'Descripción existente']);
        $existing = User::create([
            'role_id' => $role->id,
            'name' => 'Cuenta conservada',
            'dni' => '90000001',
            'email' => 'admin@sanjuan.com',
            'password' => 'mi-clave-segura',
        ]);
        User::create([
            'role_id' => $role->id,
            'name' => 'DNI ya utilizado',
            'dni' => '90000002',
            'email' => 'dni-ocupado@example.test',
            'password' => 'otra-clave',
        ]);
        $passwordHash = $existing->password;

        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('roles', 3);
        $this->assertDatabaseCount('users', 5);
        $this->assertSame('Descripción existente', $role->fresh()->description);
        $this->assertSame('Cuenta conservada', $existing->fresh()->name);
        $this->assertSame($passwordHash, $existing->fresh()->password);
        $this->assertTrue(Hash::check('mi-clave-segura', $existing->fresh()->password));
        $this->assertNotSame('90000002', User::query()->where('email', 'rquispe@sanjuan.com')->value('dni'));
    }

    public function test_database_seeder_creates_sixty_demo_sales_and_is_idempotent_on_an_empty_database(): void
    {
        $this->seed();

        $this->assertDatabaseCount('roles', 3);
        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('customers', 4);
        $this->assertDatabaseCount('medicines', 12);
        $this->assertDatabaseCount('sales', 60);
        $this->assertDatabaseCount('detail_sales', 60);
        $this->assertSame(
            1200,
            (int) Medicine::query()->sum('stock') + (int) DetailSale::query()->sum('amount'),
        );

        $sale = Sale::query()->firstOrFail();
        $this->assertSame(
            round((float) $sale->total * 18 / 118, 2),
            (float) $sale->igv,
        );

        $stockAfterFirstRun = (int) Medicine::query()->sum('stock');
        $this->seed();

        $this->assertDatabaseCount('sales', 60);
        $this->assertSame($stockAfterFirstRun, (int) Medicine::query()->sum('stock'));
        $this->assertSame('Químico farmacéutico', User::query()
            ->where('email', 'rquispe@sanjuan.com')
            ->firstOrFail()
            ->role->name);
        $this->assertTrue(Hash::check('password', User::query()
            ->where('email', 'rquispe@sanjuan.com')
            ->value('password')));
    }

    private function createSaleWithDetail(
        Customer $customer,
        User $seller,
        Medicine $medicine,
        mixed $date,
        float $total,
        float $igv,
    ): Sale {
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $seller->id,
            'date' => $date,
            'total' => $total,
            'igv' => $igv,
        ]);

        DetailSale::create([
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
            'amount' => 1,
            'price_unit' => $total,
            'subtotal' => $total,
        ]);

        return $sale;
    }
}
