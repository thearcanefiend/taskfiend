<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                {{ __('Projects') }}
            </h2>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                New Project
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($projects as $project)
                    <div class="bg-gray-800 border border-gray-700 p-6 rounded-lg shadow hover:shadow-md transition cursor-pointer"
                         onclick="window.location='{{ route('projects.show', $project) }}'">
                        <h3 class="font-semibold text-lg text-gray-100">{{ $project->name }}</h3>
                        @if($project->description)
                            <p class="text-sm text-gray-400 mt-2">{{ Str::limit($project->description, 100) }}</p>
                        @endif
                        <div class="flex items-center justify-between mt-4">
                            <span class="text-sm text-gray-500">{{ $project->tasks_count }} tasks</span>
                            <span class="inline-block px-2 py-1 text-xs rounded
                                @if($project->status === 'done') bg-green-100 text-green-800
                                @elseif($project->status === 'archived') bg-gray-100 text-gray-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ ucfirst($project->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-gray-800 border border-gray-700 p-8 rounded-lg text-center text-gray-500">
                        No projects yet. Create your first project!
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
