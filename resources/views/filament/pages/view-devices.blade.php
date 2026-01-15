<x-filament-panels::page>

    <div class="space-y-8">

        <!-- Devices Table -->
        <div
            class="fi-section bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 rounded-xl p-6">
            <h2 class="text-xl font-semibold mb-6">
                Devices
            </h2>

            {{ $this->table }}
        </div>

    </div>

</x-filament-panels::page>