<div class="space-y-4">
    <div class="flex items-start gap-4 flex-wrap">

        <div class="flex-shrink-0">
            @php
                $ext = strtolower(pathinfo($imageUrl ?? '', PATHINFO_EXTENSION));
            @endphp
            @if(!empty($imageUrl) && $ext === 'mp4')
                <video controls class="h-28 w-28 object-contain rounded border bg-black">
                    <source src="{{ $imageUrl }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            @elseif(!empty($imageUrl))
                <a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ $imageUrl }}" alt="{{ $slide->name }}" class="h-28 w-28 object-contain rounded border" />
                </a>
            @else
                <div
                    class="h-28 w-28 bg-gray-100 dark:bg-gray-800 rounded border flex items-center justify-center text-sm text-gray-500">
                    No image</div>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex gap-6 items-center flex-wrap">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <div class="font-medium text-gray-900 dark:text-gray-100">
                        {{ __('filament.pages.view_slides.columns.name') }}
                    </div>
                    <div>{{ $slide->name }}</div>
                </div>

                @if(!empty($imageUrl))
                    <a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary-600 text-white rounded hover:opacity-90">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V8.414A2 2 0 0016.586 7L12 2.414A2 2 0 0010.586 2H4z" />
                        </svg>
                        <span>{{ __('filament.pages.view_slides.modal.view_image') }}</span>
                    </a>
                @endif

                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <div class="font-medium text-gray-900 dark:text-gray-100">
                        {{ __('filament.pages.view_slides.columns.type') }}
                    </div>
                    <div>{{ $slide->type }}</div>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <div class="font-medium text-gray-900 dark:text-gray-100">
                        {{ __('filament.pages.view_slides.columns.slide_id') }}
                    </div>
                    <div>{{ $slide->slide_id }}</div>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button type="button"
                    onclick="document.getElementById('raw-json-{{ $slide->id }}').classList.toggle('hidden')"
                    class="inline-flex items-center gap-2 px-3 py-1.5 border rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V8.414A2 2 0 0016.586 7L12 2.414A2 2 0 0010.586 2H4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>{{ __('filament.pages.view_slides.modal.raw_record') }}</span>
                </button>
            </div>

            <div id="raw-json-{{ $slide->id }}" class="mt-4 hidden">
                <pre
                    class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-xs overflow-auto"><code>{{ json_encode($slide->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    </div>
</div>