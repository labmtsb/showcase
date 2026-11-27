<?php

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    #[Locked]
    public string $rootPath;

    public array $expanded = [];

    public function mount(string $path): void
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        abort_if($path === '' || in_array('..', explode('/', $path), true), 400);

        $disk = Storage::disk('local');

        abort_unless($disk->directoryExists($path), 404);

        $this->rootPath = $path;
    }

    public function toggleFolder(string $path): void
    {
        abort_unless($this->isInsideRoot($path), 403);

        $disk = Storage::disk('local');

        abort_unless($disk->directoryExists($path), 404);

        if (isset($this->expanded[$path])) {
            unset($this->expanded[$path]);
        } else {
            $this->expanded[$path] = true;
        }
    }

    private function isInsideRoot(string $path): bool
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        return $path === $this->rootPath || str_starts_with($path, $this->rootPath . '/');
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];

        $bytes /= 1024;

        foreach ($units as $unit) {
            if ($bytes < 1024 || $unit === 'TB') {
                return round($bytes, 2) . ' ' . $unit;
            }

            $bytes /= 1024;
        }

        return round($bytes, 2) . ' TB';
    }

    private function buildTree(string $directory, int $depth = 0): array
    {
        $disk = Storage::disk('local');

        $directories = $disk->directories($directory);
        $files = $disk->files($directory);

        usort($directories, fn($a, $b) => strcasecmp(basename($a), basename($b)));

        usort($files, fn($a, $b) => strcasecmp(basename($a), basename($b)));

        $nodes = [];

        foreach ($directories as $folder) {
            $nodes[] = [
                'name' => basename($folder),
                'path' => $folder,
                'type' => 'folder',
                'depth' => $depth,
                'expanded' => isset($this->expanded[$folder]),
            ];

            if (isset($this->expanded[$folder])) {
                $nodes = array_merge($nodes, $this->buildTree($folder, $depth + 1));
            }
        }

        foreach ($files as $file) {
            $nodes[] = [
                'name' => basename($file),
                'path' => $file,
                'type' => 'file',
                'depth' => $depth,
                'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                'size' => $this->formatBytes($disk->size($file)),
                'modified' => date('d M Y H:i', $disk->lastModified($file)),
            ];
        }

        return $nodes;
    }

    public function with(): array
    {
        return [
            'nodes' => $this->buildTree($this->rootPath),
        ];
    }
};
?>

<div
    {{ $attributes->class([
        'w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800',
    ]) }}>

    {{-- Header --}}
    <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-700">

        <div class="min-w-0">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                Project Files
            </h3>

            <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                {{ $rootPath }}/
            </p>
        </div>

        <span
            class="inline-flex shrink-0 items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
            File Explorer
        </span>
    </div>

    {{-- Column Header --}}
    <div
        class="grid grid-cols-[minmax(0,1fr)_90px_130px] gap-3 border-b border-gray-200 bg-gray-50 px-5 py-2.5 text-xs font-medium text-gray-500 dark:border-gray-700 dark:bg-gray-700/40 dark:text-gray-400">
        <span>Name</span>
        <span class="text-right">Size</span>
        <span class="text-right">Modified</span>
    </div>

    {{-- File Tree --}}
    <div class="divide-y divide-gray-100 dark:divide-gray-700/70 min-h-[350px]">

        @forelse ($nodes as $node)
            <div wire:key="file-node-{{ md5($node['path']) }}"
                class="grid grid-cols-[minmax(0,1fr)_90px_130px] items-center gap-3 px-5 py-2.5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">

                {{-- Name --}}
                <div class="flex min-w-0 items-center gap-2" style="padding-left: {{ $node['depth'] * 1.25 }}rem">

                    @if ($node['type'] === 'folder')
                        <button type="button" wire:click="toggleFolder(@js($node['path']))"
                            class="flex min-w-0 items-center gap-2 text-left">

                            <x-lucide-chevron-right
                                class="size-4 shrink-0 text-gray-400 transition-transform duration-200 {{ $node['expanded'] ? 'rotate-90' : '' }}" />

                            <svg class="size-5 shrink-0 {{ $node['expanded'] ? 'text-green-500' : 'text-yellow-500' }}"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 6a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v1H8a3 3 0 0 0-2.8 2L3 17.5V6Z" />
                                <path
                                    d="M7.1 10.5A2 2 0 0 1 9 9h12a1 1 0 0 1 1 1.3l-2.1 8A2 2 0 0 1 18 20H4a1 1 0 0 1-1-1.3l4.1-8.2Z" />
                            </svg>

                            <span class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $node['name'] }}
                            </span>
                        </button>
                    @else
                        @php
                            $extension = $node['extension'];

                            $iconColor = match (true) {
                                in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']) => 'text-purple-500',
                                $extension === 'pdf' => 'text-red-500',
                                in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz']) => 'text-yellow-600',
                                in_array($extension, ['php', 'js', 'ts', 'html', 'css', 'json', 'py'])
                                    => 'text-blue-500',
                                in_array($extension, ['doc', 'docx', 'txt', 'md']) => 'text-sky-500',
                                default => 'text-gray-400',
                            };
                        @endphp

                        <span class="w-4 shrink-0"></span>

                        <svg class="size-5 shrink-0 {{ $iconColor }}" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M6 2.25A2.25 2.25 0 0 0 3.75 4.5v15A2.25 2.25 0 0 0 6 21.75h12a2.25 2.25 0 0 0 2.25-2.25V8.56a2.25 2.25 0 0 0-.66-1.59l-4.31-4.31A2.25 2.25 0 0 0 13.69 2H6Zm7.5 1.5v4.5h4.5l-4.5-4.5Z"
                                clip-rule="evenodd" />
                        </svg>

                        <span class="truncate text-sm text-gray-700 dark:text-gray-300">
                            {{ $node['name'] }}
                        </span>
                    @endif

                </div>

                {{-- Size --}}
                <div class="truncate text-right text-xs text-gray-500 dark:text-gray-400">
                    {{ $node['type'] === 'file' ? $node['size'] : '—' }}
                </div>

                {{-- Modified --}}
                <div class="truncate text-right text-xs text-gray-500 dark:text-gray-400">
                    {{ $node['type'] === 'file' ? $node['modified'] : '—' }}
                </div>

            </div>

        @empty

            <div class="flex flex-col items-center justify-center px-5 py-12 text-center">
                <x-lucide-folder class="mb-3 size-10 text-gray-300 dark:text-gray-600" />

                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Folder is empty
                </p>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    There are no files or folders in this directory yet.
                </p>
            </div>
        @endforelse

    </div>

    {{-- Footer --}}
    <div class="border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-700/30">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ count($nodes) }} items shown
        </p>
    </div>

</div>
