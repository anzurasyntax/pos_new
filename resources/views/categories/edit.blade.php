<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Edit category</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-soft ring-1 ring-slate-200/60 p-4 sm:p-6">
                <form method="POST" action="{{ route('categories.update', $category) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4">
                        <x-input
                            label="Name"
                            name="name"
                            type="text"
                            value="{{ old('name', $category->name) }}"
                            autofocus
                        />

                        <div>
                            <label for="parent_id" class="block text-sm font-semibold text-slate-700">Parent category</label>
                            <p class="text-xs text-slate-500 mt-0.5 mb-2">Cannot choose this category or its descendants.</p>
                            <select id="parent_id" name="parent_id"
                                class="block w-full rounded-lg border-slate-200 bg-white shadow-sm px-3 py-2.5 text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">— None (root) —</option>
                                @foreach ($parentOptions as $opt)
                                    <option value="{{ $opt['id'] }}" @selected(old('parent_id', $category->parent_id) == $opt['id'])>
                                        {{ $opt['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-input
                            label="Sort order"
                            name="sort_order"
                            type="number"
                            value="{{ old('sort_order', $category->sort_order) }}"
                            min="0"
                        />

                        <x-input
                            label="URL slug"
                            name="slug"
                            type="text"
                            placeholder="Leave blank to derive from name"
                            value="{{ old('slug', $category->slug) }}"
                        />
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 mt-6">
                        <a href="{{ route('categories.index') }}"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50">
                            Cancel
                        </a>
                        <x-button type="primary" htmlType="submit" text="Update category" loadingText="Saving..." />
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
