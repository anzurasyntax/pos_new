<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $roots = Category::query()
            ->whereNull('parent_id')
            ->withCount('products')
            ->with([
                'children' => function ($q) {
                    $q->orderByRaw('sort_order IS NULL, sort_order ASC')
                        ->orderBy('name')
                        ->withCount('products');
                },
            ])
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('name')
            ->get();

        return view('categories.index', [
            'roots' => $roots,
        ]);
    }

    public function create(): View
    {
        return view('categories.create', [
            'parentOptions' => $this->parentOptionsExcluding(collect()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $slugInput = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        Category::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->ensureUniqueSlug($slugInput),
            'sort_order' => $data['sort_order'] ?? null,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        $forbidden = $this->selfAndDescendantIds($category);

        return view('categories.edit', [
            'category' => $category,
            'parentOptions' => $this->parentOptionsExcluding($forbidden),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $forbidden = $this->selfAndDescendantIds($category);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                Rule::notIn($forbidden->all()),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $slugInput = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        $newSlug = $slugInput === $category->slug
            ? $category->slug
            : $this->ensureUniqueSlug($slugInput, $category->id);

        $category->update([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $newSlug,
            'sort_order' => $data['sort_order'] ?? null,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Cannot delete a category that has subcategories. Remove or move them first.');
        }

        if ($category->products()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Cannot delete a category that still has products. Reassign or remove those products first.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted.');
    }

    /**
     * @return list<array{id: int, label: string}>
     */
    private function parentOptionsExcluding(Collection $excludeIds): array
    {
        $exclude = $excludeIds->flip();

        $options = [];
        $walk = function ($categories, int $depth = 0) use (&$walk, &$options, $exclude): void {
            foreach ($categories as $cat) {
                if ($exclude->has($cat->id)) {
                    continue;
                }
                $options[] = [
                    'id' => $cat->id,
                    'label' => str_repeat('— ', $depth).$cat->name,
                ];
                $children = Category::query()
                    ->where('parent_id', $cat->id)
                    ->orderByRaw('sort_order IS NULL, sort_order ASC')
                    ->orderBy('name')
                    ->get();
                $walk($children, $depth + 1);
            }
        };

        $roots = Category::query()
            ->whereNull('parent_id')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('name')
            ->get();

        $walk($roots);

        return $options;
    }

    private function selfAndDescendantIds(Category $category): Collection
    {
        $ids = collect([$category->id]);
        $queue = Category::query()->where('parent_id', $category->id)->pluck('id');

        while ($queue->isNotEmpty()) {
            $id = $queue->shift();
            $ids->push($id);
            $queue = $queue->merge(
                Category::query()->where('parent_id', $id)->pluck('id')
            );
        }

        return $ids->unique()->values();
    }

    private function ensureUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = $base !== '' ? $base : 'category';

        for ($n = 0; $n < 500; $n++) {
            $candidate = $n === 0 ? $base : $base.'-'.$n;
            $q = Category::query()->where('slug', $candidate);
            if ($ignoreId !== null) {
                $q->where('id', '!=', $ignoreId);
            }
            if (! $q->exists()) {
                return $candidate;
            }
        }

        return $base.'-'.Str::lower(Str::random(8));
    }
}
