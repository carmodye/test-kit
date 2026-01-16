@props(['record', 'url', 'isVideo'])

<div class="space-y-4">
    <div>
        <h3 class="text-lg font-semibold mb-2">Preview</h3>
        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800 flex justify-center">
            @if($isVideo)
                <video controls class="max-w-full max-h-96 rounded">
                    <source src="{{ $url }}" type="video/{{ strtolower(pathinfo($record->name, PATHINFO_EXTENSION)) }}">
                    Your browser does not support the video tag.
                </video>
            @else
                <img src="{{ $url }}" alt="{{ $record->name }}" class="max-w-full max-h-96 object-contain rounded">
            @endif
        </div>
    </div>

    <div class="max-w-md mx-auto">
        <h3 class="text-lg font-semibold mb-2">Slide Details</h3>
        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">Client:</dt>
                    <dd>{{ $record->client }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">Slide ID:</dt>
                    <dd>{{ $record->slide_id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">Name:</dt>
                    <dd>{{ $record->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">Type:</dt>
                    <dd>{{ $record->type }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">Duration:</dt>
                    <dd>{{ $record->duration }}s</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">Hold:</dt>
                    <dd>{{ $record->hold }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">Not Before:</dt>
                    <dd>{{ $record->notbefore ? \Carbon\Carbon::parse($record->notbefore)->format('Y-m-d H:i') : 'N/A' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">Not After:</dt>
                    <dd>{{ $record->notafter ? \Carbon\Carbon::parse($record->notafter)->format('Y-m-d H:i') : 'N/A' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">Last Updated:</dt>
                    <dd>{{ $record->updated_at->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>