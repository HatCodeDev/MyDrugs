{{-- resources/views/filament/infolists/components/json-viewer.blade.php --}}
@php
    // Ya no se usa $getState() si pasamos los datos con viewData
    $dataToDisplay = $state ?? []; // Usar la variable $state que pasaste desde viewData
    $currentLevel = $level ?? 0; // Usar la variable $level que pasaste desde viewData
@endphp

@if(is_array($dataToDisplay) && !empty($dataToDisplay))
    <div class="@if($currentLevel > 0) ml-4 pl-4 border-l border-gray-200 dark:border-gray-700 py-1 @else mt-1 @endif">
        <dl class="@if($currentLevel === 0) divide-y divide-gray-200 dark:divide-gray-700 @endif">
            @foreach($dataToDisplay as $key => $value)
                <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-2 @if($currentLevel > 0) border-t border-gray-200 dark:border-gray-700 first:border-t-0 @endif">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 break-all">
                        {{ Str::title(str_replace(['_', '-'], ' ', $key)) }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">
                        @if(is_array($value))
                            {{-- Llamada recursiva para el sub-array, pasando 'state' y el nuevo 'level' --}}
                            @include('filament.infolists.components.json-viewer', ['state' => $value, 'level' => $currentLevel + 1])
                        @elseif(is_bool($value))
                            <span @class([
                                'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset',
                                'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20' => $value,
                                'bg-red-50 text-red-700 ring-red-600/10 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20' => !$value,
                            ])>
                                {{ $value ? 'Sí' : 'No' }}
                            </span>
                        @elseif(is_null($value))
                            <span class="italic text-gray-400 dark:text-gray-500">N/A</span>
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
@elseif(!is_null($dataToDisplay))
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $dataToDisplay }}</p>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400">No hay detalles disponibles.</p>
@endif