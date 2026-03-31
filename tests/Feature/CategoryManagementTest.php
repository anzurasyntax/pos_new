<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    public function test_guest_cannot_view_categories(): void
    {
        $this->get(route('categories.index'))->assertRedirect();
    }

    public function test_manager_can_create_and_list_category(): void
    {
        $this->actingAs($this->manager());

        $this->post(route('categories.store'), [
            'name' => 'Test Root',
            'parent_id' => null,
            'sort_order' => 1,
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Root',
            'parent_id' => null,
        ]);
    }

    public function test_cannot_delete_category_with_children(): void
    {
        $this->actingAs($this->manager());

        $root = Category::create([
            'name' => 'Root',
            'slug' => 'root',
            'parent_id' => null,
            'sort_order' => 1,
        ]);

        Category::create([
            'name' => 'Child',
            'slug' => 'child',
            'parent_id' => $root->id,
            'sort_order' => 1,
        ]);

        $this->delete(route('categories.destroy', $root))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $root->id]);
    }
}
