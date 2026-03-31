<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 leading-tight">Categories</h2>
                <p class="text-sm text-slate-500 mt-1">Organize products with parent and subcategories.</p>
            </div>
            <a href="{{ route('categories.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 shadow-sm">
                <svg class="w-5 h-5 me-2 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Add category
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200/80 px-4 py-3 text-emerald-900 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200/80 px-4 py-3 text-rose-900 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-soft ring-1 ring-slate-200/60 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/90">
                            <tr>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Slug</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Sort</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Products</th>
                                <th scope="col" class="px-4 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($roots as $root)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-semibold text-slate-900">
                                        {{ $root->name }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-600">
                                        {{ $root->slug }}
                                    </td>
                                    <td class="px-4 py-3 tabular-nums text-slate-700">
                                        {{ $root->sort_order ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 tabular-nums text-slate-700">
                                        {{ $root->products_count }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                            <a href="{{ route('categories.edit', $root) }}"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/80 hover:bg-emerald-100">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('categories.destroy', $root) }}" class="inline"
                                                onsubmit="return confirm('Delete this category? Subcategories and products must be moved first.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-rose-50 text-rose-800 ring-1 ring-rose-200/80 hover:bg-rose-100">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @foreach ($root->children as $child)
                                    <tr class="hover:bg-slate-50/80 bg-slate-50/40">
                                        <td class="px-4 py-3 text-slate-800">
                                            <span class="text-slate-400 mr-2" aria-hidden="true">└</span>
                                            {{ $child->name }}
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-slate-600">
                                            {{ $child->slug }}
                                        </td>
                                        <td class="px-4 py-3 tabular-nums text-slate-700">
                                            {{ $child->sort_order ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 tabular-nums text-slate-700">
                                            {{ $child->products_count }}
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                                <a href="{{ route('categories.edit', $child) }}"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/80 hover:bg-emerald-100">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('categories.destroy', $child) }}" class="inline"
                                                    onsubmit="return confirm('Delete this category?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-rose-50 text-rose-800 ring-1 ring-rose-200/80 hover:bg-rose-100">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">
                                        No categories yet. Create a root category, then add subcategories for your catalog.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-4 text-sm text-slate-500">
                Assign categories when you <a href="{{ route('products.index') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">add or edit a product</a>.
            </p>
        </div>
    </div>
</x-app-layout>
