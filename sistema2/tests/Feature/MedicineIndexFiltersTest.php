<?php

namespace Tests\Feature;

use App\Models\Medicine;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesUsers;
use Tests\TestCase;

class MedicineIndexFiltersTest extends TestCase
{
    use CreatesUsers;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('detail_sales');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('name');
            $table->char('dni', 8)->unique();
            $table->string('phone', 15)->nullable();
            $table->string('tuition', 20)->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
        });

        $this->actingAsRole('Administrador');
    }

    public function test_search_filters_medicines_by_name(): void
    {
        $categoryId = $this->createCategory('Analgésicos');
        $this->createMedicine($categoryId, 'Paracetamol');
        $this->createMedicine($categoryId, 'Ibuprofeno');

        $response = $this->get(route('medicines.index', ['search' => 'Paracetamol']))
            ->assertOk()
            ->assertSee('Paracetamol');

        $response->assertViewHas('medicines', function ($medicines) {
            return $medicines->total() === 1
                && $medicines->first()->name === 'Paracetamol';
        });
    }

    public function test_category_filter_only_shows_medicines_in_that_category(): void
    {
        $painReliefId = $this->createCategory('Analgésicos');
        $antibioticsId = $this->createCategory('Antibióticos');
        $this->createMedicine($painReliefId, 'Paracetamol');
        $this->createMedicine($antibioticsId, 'Amoxicilina');

        $response = $this->get(route('medicines.index', ['category_id' => $painReliefId]))
            ->assertOk()
            ->assertSee('Paracetamol');

        $response->assertViewHas('medicines', function ($medicines) {
            return $medicines->total() === 1
                && $medicines->first()->name === 'Paracetamol';
        });
    }

    public function test_low_stock_filter_marks_ten_as_low_and_eleven_as_normal(): void
    {
        $categoryId = $this->createCategory('Analgésicos');
        $this->createMedicine($categoryId, 'Stock bajo', 10);
        $this->createMedicine($categoryId, 'Stock normal', 11);

        $allMedicines = $this->get(route('medicines.index'))->assertOk();
        $this->assertSame(
            1,
            substr_count($allMedicines->getContent(), '<span class="badge bg-danger">Stock bajo: 10</span>')
        );
        $allMedicines->assertSee('Stock normal');
        $allMedicines->assertSee('<span class="badge bg-success">11</span>', false);

        $response = $this->get(route('medicines.index', ['low_stock' => 1]))
            ->assertOk()
            ->assertSee('Stock bajo')
            ->assertSee('<span class="badge bg-danger">Stock bajo: 10</span>', false);

        $response->assertViewHas('medicines', function ($medicines) {
            return $medicines->total() === 1
                && $medicines->first()->name === 'Stock bajo';
        });
    }

    public function test_search_category_and_low_stock_filters_can_be_combined(): void
    {
        $painReliefId = $this->createCategory('Analgésicos');
        $antibioticsId = $this->createCategory('Antibióticos');
        $this->createMedicine($painReliefId, 'Paracetamol 500', 10);
        $this->createMedicine($painReliefId, 'Paracetamol 1g', 11);
        $this->createMedicine($antibioticsId, 'Paracetamol infantil', 3);
        $this->createMedicine($painReliefId, 'Ibuprofeno', 3);

        $response = $this->get(route('medicines.index', [
            'search' => 'Paracetamol',
            'category_id' => $painReliefId,
            'low_stock' => 1,
        ]))
            ->assertOk()
            ->assertSee('Paracetamol 500');

        $response->assertViewHas('medicines', function ($medicines) {
            return $medicines->total() === 1
                && $medicines->first()->name === 'Paracetamol 500';
        });
    }

    public function test_pagination_keeps_filters_and_limits_each_page_to_fifteen(): void
    {
        $categoryId = $this->createCategory('Analgésicos');

        for ($number = 1; $number <= 16; $number++) {
            $this->createMedicine($categoryId, "Medicamento filtro {$number}", 2);
        }

        $filters = [
            'search' => 'Medicamento filtro',
            'category_id' => $categoryId,
            'low_stock' => 1,
        ];

        $firstPage = $this->get(route('medicines.index', $filters))
            ->assertOk()
            ->assertSee('search=Medicamento')
            ->assertSee('category_id='.$categoryId)
            ->assertSee('low_stock=1');

        $firstPage->assertViewHas('medicines', function ($medicines) {
            return $medicines->total() === 16 && $medicines->count() === 15;
        });

        $secondPage = $this->get(route('medicines.index', $filters + ['page' => 2]))
            ->assertOk()
            ->assertSee('Medicamento filtro 9')
            ->assertDontSee('Medicamento filtro 16');

        $secondPage->assertViewHas('medicines', function ($medicines) {
            return $medicines->total() === 16 && $medicines->count() === 1;
        });
    }

    public function test_medicine_can_be_created_with_an_image(): void
    {
        Storage::fake('public');
        $categoryId = $this->createCategory('Analgésicos');

        $this->post(route('medicines.store'), $this->medicineData($categoryId) + [
            'image' => UploadedFile::fake()->image('paracetamol.png'),
        ])->assertRedirect(route('medicines.index'));

        $medicine = Medicine::where('name', 'Paracetamol')->firstOrFail();

        $this->assertStringStartsWith('medicines/', $medicine->image);
        Storage::disk('public')->assertExists($medicine->image);
    }

    public function test_medicine_can_be_created_without_an_image(): void
    {
        $categoryId = $this->createCategory('Analgésicos');

        $this->post(route('medicines.store'), $this->medicineData($categoryId))
            ->assertRedirect(route('medicines.index'));

        $this->assertDatabaseHas('medicines', [
            'name' => 'Paracetamol',
            'image' => null,
        ]);
    }

    public function test_medicine_list_is_ordered_alphabetically(): void
    {
        $categoryId = $this->createCategory('Analgésicos');
        $this->createMedicine($categoryId, 'Zinc');
        $this->createMedicine($categoryId, 'Aspirina');

        $this->get(route('medicines.index'))
            ->assertOk()
            ->assertViewHas('medicines', function ($medicines) {
                return $medicines->pluck('name')->all() === ['Aspirina', 'Zinc'];
            });
    }

    public function test_medicine_requires_an_existing_category(): void
    {
        $this->from(route('medicines.create'))
            ->post(route('medicines.store'), $this->medicineData(999))
            ->assertRedirect(route('medicines.create'))
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('medicines', 0);
    }

    public function test_listing_shows_uploaded_images_and_a_placeholder_when_missing(): void
    {
        Storage::fake('public');
        $categoryId = $this->createCategory('Analgésicos');
        $imagePath = 'medicines/paracetamol.png';
        Storage::disk('public')->put($imagePath, 'image content');
        $this->createMedicineWithImage($categoryId, 'Con imagen', $imagePath);
        $this->createMedicine($categoryId, 'Sin imagen');

        $this->get(route('medicines.index'))
            ->assertOk()
            ->assertSee(asset('storage/'.$imagePath))
            ->assertSee('aria-label="Sin imagen"', false);
    }

    public function test_replacing_an_image_saves_the_new_file_then_removes_only_the_old_file(): void
    {
        Storage::fake('public');
        $categoryId = $this->createCategory('Analgésicos');
        $oldPath = 'medicines/old.png';
        $otherPath = 'medicines/other.png';
        Storage::disk('public')->put($oldPath, 'old image');
        Storage::disk('public')->put($otherPath, 'other medicine image');
        $medicineId = $this->createMedicineWithImage($categoryId, 'Paracetamol', $oldPath);
        $this->createMedicineWithImage($categoryId, 'Ibuprofeno', $otherPath);

        $editResponse = $this->get(route('medicines.edit', $medicineId))
            ->assertOk()
            ->assertSee(asset('storage/'.$oldPath));

        $editResponse->assertSee('enctype="multipart/form-data"', false);

        $this->put(route('medicines.update', $medicineId), $this->medicineData($categoryId) + [
            'image' => UploadedFile::fake()->image('replacement.jpg'),
        ])->assertRedirect(route('medicines.index'));

        $newPath = Medicine::findOrFail($medicineId)->image;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($otherPath);
    }

    public function test_updating_without_a_new_image_keeps_the_current_image(): void
    {
        Storage::fake('public');
        $categoryId = $this->createCategory('Analgésicos');
        $imagePath = 'medicines/current.png';
        Storage::disk('public')->put($imagePath, 'current image');
        $medicineId = $this->createMedicineWithImage($categoryId, 'Paracetamol', $imagePath);

        $this->put(route('medicines.update', $medicineId), $this->medicineData($categoryId))
            ->assertRedirect(route('medicines.index'));

        $this->assertDatabaseHas('medicines', [
            'id' => $medicineId,
            'image' => $imagePath,
        ]);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_deleting_a_medicine_removes_its_image_but_keeps_other_medicine_images(): void
    {
        Storage::fake('public');
        $categoryId = $this->createCategory('Analgésicos');
        $deletedImagePath = 'medicines/to-delete.png';
        $otherImagePath = 'medicines/keep.png';
        Storage::disk('public')->put($deletedImagePath, 'delete this image');
        Storage::disk('public')->put($otherImagePath, 'keep this image');
        $medicineId = $this->createMedicineWithImage($categoryId, 'Paracetamol', $deletedImagePath);
        $this->createMedicineWithImage($categoryId, 'Ibuprofeno', $otherImagePath);

        $this->delete(route('medicines.destroy', $medicineId))
            ->assertRedirect(route('medicines.index'))
            ->assertSessionHas('ok', 'Medicamento eliminado correctamente.');

        $this->assertDatabaseMissing('medicines', ['id' => $medicineId]);
        Storage::disk('public')->assertMissing($deletedImagePath);
        Storage::disk('public')->assertExists($otherImagePath);
    }

    public function test_deleting_a_medicine_keeps_an_image_still_referenced_by_another_medicine(): void
    {
        Storage::fake('public');
        $categoryId = $this->createCategory('Analgésicos');
        $sharedImagePath = 'medicines/shared.png';
        Storage::disk('public')->put($sharedImagePath, 'shared image');
        $deletedMedicineId = $this->createMedicineWithImage($categoryId, 'Paracetamol', $sharedImagePath);
        $remainingMedicineId = $this->createMedicineWithImage($categoryId, 'Ibuprofeno', $sharedImagePath);

        $this->delete(route('medicines.destroy', $deletedMedicineId))
            ->assertRedirect(route('medicines.index'))
            ->assertSessionHas('ok', 'Medicamento eliminado correctamente.');

        $this->assertDatabaseMissing('medicines', ['id' => $deletedMedicineId]);
        $this->assertDatabaseHas('medicines', ['id' => $remainingMedicineId, 'image' => $sharedImagePath]);
        Storage::disk('public')->assertExists($sharedImagePath);
    }

    public function test_a_medicine_with_sales_cannot_be_deleted_or_lose_its_image(): void
    {
        Storage::fake('public');
        $categoryId = $this->createCategory('Analgésicos');
        $imagePath = 'medicines/sold.png';
        Storage::disk('public')->put($imagePath, 'keep image');
        $medicineId = $this->createMedicineWithImage($categoryId, 'Paracetamol', $imagePath);
        DB::table('detail_sales')->insert(['medicine_id' => $medicineId]);

        $this->delete(route('medicines.destroy', $medicineId))
            ->assertRedirect(route('medicines.index'))
            ->assertSessionHasErrors('medicine');

        $this->assertDatabaseHas('medicines', ['id' => $medicineId]);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $categoryId = $this->createCategory('Analgésicos');

        $this->from(route('medicines.create'))
            ->post(route('medicines.store'), $this->medicineData($categoryId) + [
                'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('medicines.create'))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('medicines', 0);
    }

    public function test_image_larger_than_two_megabytes_is_rejected(): void
    {
        Storage::fake('public');
        $categoryId = $this->createCategory('Analgésicos');

        $this->from(route('medicines.create'))
            ->post(route('medicines.store'), $this->medicineData($categoryId) + [
                'image' => UploadedFile::fake()->image('large.png')->size(2049),
            ])
            ->assertRedirect(route('medicines.create'))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('medicines', 0);
    }

    private function createCategory(string $name): int
    {
        return DB::table('categories')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createMedicine(int $categoryId, string $name, int $stock = 10): void
    {
        $createdAt = now()->addSeconds(DB::table('medicines')->count());

        DB::table('medicines')->insert([
            'category_id' => $categoryId,
            'name' => $name,
            'price' => 10,
            'stock' => $stock,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function createMedicineWithImage(int $categoryId, string $name, string $imagePath): int
    {
        return DB::table('medicines')->insertGetId([
            'category_id' => $categoryId,
            'name' => $name,
            'price' => 10,
            'stock' => 10,
            'image' => $imagePath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function medicineData(int $categoryId): array
    {
        return [
            'category_id' => $categoryId,
            'name' => 'Paracetamol',
            'price' => 10,
            'stock' => 10,
        ];
    }
}
