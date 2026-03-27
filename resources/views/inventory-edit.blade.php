<x-app-layout>
    <div class="p-6 text-gray-900">
        <h2 class="text-2xl font-semibold">Inventory (Manage)</h2>
        <p class="mt-2">Your role: {{ auth()->user()?->role ?? 'sales_user' }}</p>
        <p class="mt-2 text-sm text-gray-600">Allowed: inventory.manage (manager + super_admin)</p>
    </div>
</x-app-layout>
