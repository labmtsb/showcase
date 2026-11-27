@props([
    // Data
    'data' => null,
    'columns' => [],

    // action
    'rowActions' => null,
    'actionsLabel' => 'Actions',

    // Appearance
    'striped' => false,
    'hoverable' => true,
    'bordered' => true,
    'compact' => false,
    'rounded' => true,

    // Loading
    'loading' => false,
    'skeleton' => 3,

    // Empty state
    'emptyTitle' => 'No data found',
    'emptyDescription' => 'There is no data to display.',
    'emptyIcon' => null,

    // Table options
    'responsive' => true,
    'minWidth' => '640px',

    // Pagination
    'paginate' => false,

    // Search
    'searchable' => false,
    'searchPlaceholder' => 'Search...',

    // Sorting
    'sortable' => false,

    // Selection
    'selectable' => false,

    // Custom classes
    'tableClass' => '',
    'wrapperClass' => '',
    'theadClass' => '',
    'tbodyClass' => '',
])

@php
    $hasData = $data !== null && count($data) > 0;

    $columnCount = count($columns);

    $hasActions = $rowActions !== null;

    $totalColumns = $columnCount + ($selectable ? 1 : 0) + ($hasActions ? 1 : 0);

    $cellPadding = $compact ? 'px-4 py-2.5' : 'px-6 py-4';

    $wrapperRounded = $rounded ? 'rounded-base' : '';

    $wrapperBorder = $bordered ? 'border border-default' : '';

    $rowHover = $hoverable ? 'hover:bg-neutral-secondary-soft' : '';
@endphp

<div
    {{ $attributes->class([
        'relative bg-neutral-primary-soft shadow-xs',
        $wrapperRounded,
        $wrapperBorder,
        $wrapperClass,
    ]) }}>
    {{-- Toolbar --}}
    @if ($searchable || isset($toolbar))
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">

            @if ($searchable)
                <div class="relative w-full sm:max-w-xs">

                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-body" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                d="m21 21-4.3-4.3M19 10.5a8.5 8.5 0 1 1-17 0 8.5 8.5 0 0 1 17 0Z" />
                        </svg>
                    </div>

                    <input type="search" placeholder="{{ $searchPlaceholder }}"
                        {{ $attributes->whereStartsWith('wire:model') }}
                        class="block w-full ps-10 p-2.5 text-sm text-body bg-neutral-primary border border-default rounded-base focus:ring-2 focus:ring-brand-medium focus:border-brand" />
                </div>
            @endif

            @if (isset($toolbar))
                <div class="flex items-center gap-2">
                    {{ $toolbar }}
                </div>
            @endif

        </div>
    @endif

    {{-- Table --}}
    <div @class(['relative overflow-x-auto', 'rounded-base' => $rounded])>

        <table @class([
            'w-full text-sm text-left rtl:text-right text-body',
            $tableClass,
        ]) style="min-width: {{ $minWidth }}">

            {{-- Header --}}
            <thead @class([
                'text-sm dark:text-white text-dark bg-neutral-secondary-soft border-b border-default',
                $theadClass,
            ])>
                <tr>

                    @if ($selectable)
                        <th scope="col" class="{{ $cellPadding }} w-4">
                            <input type="checkbox"
                                class="w-4 h-4 text-brand bg-neutral-primary border-default rounded-sm focus:ring-brand-medium">
                        </th>
                    @endif

                    @foreach ($columns as $column)
                        <th scope="col" class="{{ $cellPadding }} font-medium whitespace-nowrap"
                            style="{{ isset($column['width']) ? 'width: ' . $column['width'] : '' }}">
                            {{ $column['label'] ?? '' }}
                        </th>
                    @endforeach

                    @if ($hasActions)
                        <th scope="col" class="{{ $cellPadding }} font-medium text-end">
                            {{ $actionsLabel ?? 'Actions' }}
                        </th>
                    @endif

                </tr>
            </thead>

            {{-- Body --}}
            <tbody @class(['divide-y divide-default', $tbodyClass])>

                {{-- Loading --}}
                @if ($loading)

                    @for ($i = 0; $i < $skeleton; $i++)
                        <tr class="animate-pulse bg-neutral-primary border-b border-default">

                            @if ($selectable)
                                <td class="{{ $cellPadding }}">
                                    <div class="h-4 w-4 bg-neutral-secondary-medium rounded"></div>
                                </td>
                            @endif

                            @foreach ($columns as $column)
                                <td class="{{ $cellPadding }}">
                                    <div class="h-4 bg-neutral-secondary-medium rounded"
                                        style="width: {{ $column['skeletonWidth'] ?? '70%' }}"></div>
                                </td>
                            @endforeach

                            @if ($hasActions)
                                <td class="{{ $cellPadding }}">
                                    <div class="flex justify-end">
                                        <div class="h-8 w-24 bg-neutral-secondary-medium rounded-base"></div>
                                    </div>
                                </td>
                            @endif

                        </tr>
                    @endfor

                    {{-- Empty --}}
                @elseif (!$hasData)
                    <tr class="bg-neutral-primary">
                        <td colspan="{{ max($totalColumns, 1) }}">
                            <div class="flex flex-col items-center justify-center py-12 px-4 text-center">

                                @if ($emptyIcon)
                                    <div class="mb-3 text-body">
                                        {{ $emptyIcon }}
                                    </div>
                                @endif

                                <h3 class="mb-1 text-sm font-semibold text-heading">
                                    {{ $emptyTitle }}
                                </h3>

                                <p class="text-sm text-body mb-4">
                                    {{ $emptyDescription }}
                                </p>

                                @if (isset($emptyAction))
                                    {{ $emptyAction }}
                                @endif

                            </div>
                        </td>
                    </tr>

                    {{-- Data --}}
                @else
                    @foreach ($data as $row)
                        <tr wire:key="data-table-row-{{ $row->id ?? $loop->index }}" @class([
                            'bg-neutral-primary border-b border-default',
                            $rowHover,
                            'even:bg-neutral-secondary-soft/30' => $striped,
                        ])>

                            @if ($selectable)
                                <td class="{{ $cellPadding }}">
                                    <input type="checkbox" value="{{ $row->id ?? $loop->index }}"
                                        class="w-4 h-4 text-brand bg-neutral-primary border-default rounded-sm focus:ring-brand-medium">
                                </td>
                            @endif

                            @foreach ($columns as $column)
                                <td class="{{ $cellPadding }}">

                                    @if (isset($column['slot']))
                                        {{ $column['slot']($row) }}
                                    @elseif (isset($column['field']))
                                        {{ data_get($row, $column['field']) ?? ($column['default'] ?? '-') }}
                                    @else
                                        -
                                    @endif

                                </td>
                            @endforeach

                            @if ($hasActions)
                                <td class="{{ $cellPadding }}">
                                    <div class="flex justify-end">
                                        {!! $rowActions($row)->render() !!}
                                    </div>
                                </td>
                            @endif

                        </tr>
                    @endforeach

                @endif

            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    @if (isset($footer) || ($paginate && method_exists($data, 'links')))
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-t border-default">

            @if (isset($footer))
                {{ $footer }}
            @endif

            @if ($paginate && method_exists($data, 'links'))
                <div>
                    {{ $data->links() }}
                </div>
            @endif

        </div>
    @endif

</div>
