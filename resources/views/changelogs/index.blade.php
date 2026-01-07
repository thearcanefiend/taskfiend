<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 leading-tight">
            @if(isset($task))
                Change Log - {{ $task->name }}
            @elseif(isset($project))
                Change Log - {{ $project->name }}
            @elseif(isset($tag))
                Change Log - {{ $tag->tag_name }}
            @else
                My Change Log
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 border border-gray-700 overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(isset($task))
                    <div class="mb-4">
                        <a href="{{ route('tasks.show', $task) }}" class="text-sm text-blue-400 hover:underline">
                            &larr; Back to Task
                        </a>
                    </div>
                @elseif(isset($project))
                    <div class="mb-4">
                        <a href="{{ route('projects.show', $project) }}" class="text-sm text-blue-400 hover:underline">
                            &larr; Back to Project
                        </a>
                    </div>
                @elseif(isset($tag))
                    <div class="mb-4">
                        <a href="{{ route('tags.show', $tag) }}" class="text-sm text-blue-400 hover:underline">
                            &larr; Back to Tag
                        </a>
                    </div>
                @endif

                <div class="space-y-4">
                    @forelse($changeLogs as $log)
                        <div class="border-l-2 border-gray-600 pl-4 py-2">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-300">
                                        {{ $log->user->name }} {{ $log->description }}
                                        @if(!isset($task) && !isset($project) && !isset($tag) && isset($log->entity))
                                            @if($log->entity_type === 'tasks')
                                                on task '<a href="{{ route('tasks.show', $log->entity) }}" class="text-blue-400 hover:underline">{{ $log->entity->name }}</a>'
                                            @elseif($log->entity_type === 'projects')
                                                on project '<a href="{{ route('projects.show', $log->entity) }}" class="text-blue-400 hover:underline">{{ $log->entity->name }}</a>'
                                            @elseif($log->entity_type === 'tags')
                                                on tag '<a href="{{ route('tags.show', $log->entity) }}" class="text-blue-400 hover:underline">{{ $log->entity->tag_name }}</a>'
                                            @endif
                                        @endif
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-gray-500">{{ $log->date->format('l, F j, Y g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            No changes recorded yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
