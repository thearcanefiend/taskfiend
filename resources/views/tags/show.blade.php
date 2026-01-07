<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight" style="color: {{ $tag->color }}">
                {{ $tag->tag_name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('tags.edit', $tag) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Tag Details -->
            <div class="bg-gray-800 border border-gray-700 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-lg" style="background-color: {{ $tag->color }}"></div>
                    <div>
                        <h3 class="font-semibold text-lg text-gray-100">{{ $tag->tag_name }}</h3>
                        <p class="text-sm text-gray-500">Color: {{ $tag->color }}</p>
                    </div>
                </div>
            </div>

            <!-- Tagged Tasks -->
            <div class="bg-gray-800 border border-gray-700 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-100 mb-4">Tagged Tasks</h3>
                <x-task-list :tasks="$tasks" />
            </div>
        </div>
    </div>
</x-app-layout>
