<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\DetailSale;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\Feature\Concerns\CreatesUsers;
use Tests\TestCase;
use ZipArchive;

class ReportExportTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_pdf_download_is_a_valid_pdf_with_the_selected_period(): void
    {
        $user = $this->createUserForRole('Administrador');
        $this->createSale($user);

        $today = today()->toDateString();
        $response = $this->actingAs($user)->get(route('reports.sales.pdf', [
            'from' => $today,
            'to' => $today,
        ]));

        $filename = 'reporte-ventas-'.today()->format('Ymd').'-'.today()->format('Ymd').'.pdf';
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString($filename, $response->headers->get('content-disposition'));

        $pdf = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(500, strlen($pdf));
    }

    public function test_excel_download_is_a_valid_workbook_containing_real_filtered_rows_and_totals(): void
    {
        $user = $this->createUserForRole('Químico farmacéutico');
        $sale = $this->createSale($user);
        $today = today()->toDateString();

        $response = $this->actingAs($user)->get(route('reports.sales.excel', [
            'from' => $today,
            'to' => $today,
            'customer_id' => $sale->customer_id,
            'user_id' => $user->id,
            'category_id' => $sale->details()->firstOrFail()->medicine->category_id,
        ]));

        $filename = 'reporte-ventas-'.today()->format('Ymd').'-'.today()->format('Ymd').'.xlsx';
        $response->assertOk();
        $this->assertStringContainsString($filename, $response->headers->get('content-disposition'));
        $this->assertStringContainsString(
            'spreadsheetml',
            $response->headers->get('content-type'),
        );

        $xlsx = $this->downloadedContent($response);
        $this->assertStringStartsWith('PK', $xlsx);

        $temporaryFile = tempnam(sys_get_temp_dir(), 'farmacia-report-');
        $this->assertNotFalse($temporaryFile);

        try {
            file_put_contents($temporaryFile, $xlsx);
            $archive = new ZipArchive;
            $this->assertSame(true, $archive->open($temporaryFile));

            $workbookText = ($archive->getFromName('xl/sharedStrings.xml') ?: '')
                .($archive->getFromName('xl/worksheets/sheet1.xml') ?: '');

            $this->assertStringContainsString('Cliente del reporte', $workbookText);
            $this->assertStringContainsString('Reporte de ventas', $workbookText);
            $this->assertStringContainsString('118', $workbookText);
            $archive->close();
        } finally {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    public function test_cashiers_cannot_download_sales_reports(): void
    {
        $cashier = $this->createUserForRole('Cajero');

        $this->actingAs($cashier)
            ->get(route('reports.sales.pdf'))
            ->assertForbidden();

        $this->actingAs($cashier)
            ->get(route('reports.sales.excel'))
            ->assertForbidden();
    }

    private function createSale(User $seller): Sale
    {
        $customer = Customer::create(['name' => 'Cliente del reporte', 'dni' => '11223344']);
        $category = Category::create(['name' => 'Analgésicos']);
        $medicine = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Medicamento del reporte',
            'price' => 118,
            'stock' => 10,
        ]);
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $seller->id,
            'date' => now(),
            'total' => 118,
            'igv' => 18,
        ]);
        DetailSale::create([
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
            'amount' => 1,
            'price_unit' => 118,
            'subtotal' => 118,
        ]);

        return $sale;
    }

    private function downloadedContent(TestResponse $response): string
    {
        if ($response->baseResponse instanceof BinaryFileResponse) {
            $content = file_get_contents($response->baseResponse->getFile()->getPathname());
            $this->assertIsString($content);

            return $content;
        }

        $content = $response->baseResponse->getContent();

        return is_string($content) ? $content : $response->streamedContent();
    }
}
