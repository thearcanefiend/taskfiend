<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                {{ $title }}
            </h2>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                Add Task
            </a>
        </div>
    </x-slot>

    <div class="py-12 markdown">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (empty($fileContents))
                I have nothing to say to you.
            @else
                {!! $fileContents !!}
            @endif
        </div>
    </div>
</x-app-layout>
