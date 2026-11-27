<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesUsers;
use Tests\TestCase;

class PwaAssetsTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_manifest_references_valid_install_icons_and_offline_assets(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('Sistema de Farmacia San Juan', $manifest['name']);
        $this->assertSame('Farmacia SJB', $manifest['short_name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('./login', $manifest['start_url']);

        foreach ([192, 512] as $size) {
            $iconPath = public_path("pwa/icon-{$size}.png");
            $this->assertFileExists($iconPath);
            $image = getimagesize($iconPath);
            $this->assertSame($size, $image[0]);
            $this->assertSame($size, $image[1]);
            $this->assertSame(IMAGETYPE_PNG, $image[2]);
        }

        $this->assertFileExists(public_path('offline.html'));
        $serviceWorker = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString("request.mode === 'navigate'", $serviceWorker);
        $this->assertStringContainsString('offline.html', $serviceWorker);
        $this->assertStringContainsString("'css/', 'js/', 'pwa/'", $serviceWorker);
        $this->assertStringNotContainsString('caches.put(request, response)', $serviceWorker);
    }

    public function test_login_page_has_manifest_service_worker_and_install_button(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('manifest.json')
            ->assertSee('service-worker')
            ->assertSee('Instalar aplicación');
    }

    public function test_authenticated_pages_register_the_same_pwa_assets(): void
    {
        $this->actingAs($this->createUserForRole('Administrador'))
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('manifest.json')
            ->assertSee('js/pwa.js')
            ->assertSee('data-pwa-install');
    }
}
