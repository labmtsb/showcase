<?php

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    #[Locked]
    public string $rootPath;

    public array $expanded = [];

    public array $uploads = [];

    public ?string $successMessage = null;

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

    public function uploadFiles(): void
    {
        $this->validate(
            [
                'uploads' => ['required', 'array', 'min:1'],
                'uploads.*' => ['required', 'file', 'max:20480'],
            ],
            [
                'uploads.required' => 'Pilih minimal satu file.',
                'uploads.*.file' => 'File yang diunggah tidak valid.',
                'uploads.*.max' => 'Ukuran file maksimal 20 MB.',
            ],
        );

        $disk = Storage::disk('local');

        abort_unless($disk->directoryExists($this->rootPath), 404);

        $uploadedCount = 0;

        foreach ($this->uploads as $file) {
            $originalName = basename($file->getClientOriginalName());

            // Amankan nama file.
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);

            $safeName = $safeName ?: Str::uuid()->toString();

            $filename = $safeName;
            $counter = 1;

            // Hindari menimpa file yang sudah ada.
            while ($disk->exists($this->rootPath . '/' . $filename)) {
                $extension = pathinfo($safeName, PATHINFO_EXTENSION);
                $name = pathinfo($safeName, PATHINFO_FILENAME);

                $filename = $name . '-' . $counter++;

                if ($extension !== '') {
                    $filename .= '.' . $extension;
                }
            }

            $file->storeAs($this->rootPath, $filename, 'local');

            $uploadedCount++;
        }

        $this->reset('uploads');

        $this->successMessage = $uploadedCount . ' file berhasil diunggah.';

        $this->dispatch('upload-completed');
    }

    public function deleteItem(string $path, string $type): void
    {
        abort_unless($this->isInsideRoot($path), 403);

        // Tidak boleh menghapus direktori utama project.
        abort_if($path === $this->rootPath, 403);

        $disk = Storage::disk('local');

        if ($type === 'folder') {
            abort_unless($disk->directoryExists($path), 404);

            // Hapus seluruh isi folder secara rekursif.
            $disk->deleteDirectory($path);

            // Bersihkan state folder yang sudah dihapus.
            foreach (array_keys($this->expanded) as $expandedPath) {
                if ($expandedPath === $path || str_starts_with($expandedPath, $path . '/')) {
                    unset($this->expanded[$expandedPath]);
                }
            }

            $this->successMessage = 'Folder berhasil dihapus.';

            return;
        }

        if ($type === 'file') {
            abort_unless($disk->exists($path) && !$disk->directoryExists($path), 404);

            $disk->delete($path);

            $this->successMessage = 'File berhasil dihapus.';

            return;
        }

        abort(400);
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

    {{-- Success Message --}}
    @if ($successMessage)
        <div class="m-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400"
            role="alert">
            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
            </svg>

            <span class="flex-1">
                {{ $successMessage }}
            </span>

            <button type="button" wire:click="$set('successMessage', null)"
                class="text-green-600 hover:text-green-800 dark:text-green-400">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Upload Section --}}
    <div class="flex justify-end p-3">
        <button type="button" @click="$dispatch('open-upload-modal')"
            class="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2.5
               text-sm font-medium text-white transition-colors
               hover:bg-blue-700 focus:outline-none focus:ring-4
               focus:ring-blue-300 dark:focus:ring-blue-800">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 16V4m0 0L8 8m4-4 4 4M4 16v3a1 1 0 001 1h14a1 1 0 001-1v-3" />
            </svg>
            Upload Files
        </button>
    </div>


    {{-- modal upload --}}
    <div x-on:upload-completed.window="open = false" x-data="{ open: false }" x-on:open-upload-modal.window="open = true"
        x-on:keydown.escape.window="open = false">
        <div x-show="open" x-cloak x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4"
            @click.self="open = false">
            <div x-show="open" x-transition class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                {{-- Modal Header --}}
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Upload Files
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Pilih file yang ingin ditambahkan ke project.
                        </p>
                    </div>

                    <button type="button" @click="open = false"
                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Upload Form --}}
                <form wire:submit="uploadFiles">
                    <div class="mb-5">
                        <label for="project-files-upload"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Select files
                        </label>

                        <input id="project-files-upload" type="file" wire:model="uploads" multiple
                            class="block w-full cursor-pointer rounded border border-gray-300 bg-gray-50 text-sm text-gray-900 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">

                        @error('uploads')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        @error('uploads.*')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Maksimal ukuran setiap file adalah 20 MB.
                        </p>
                    </div>

                    {{-- Upload Progress --}}
                    <div wire:loading wire:target="uploads" class="mb-4">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-full w-full animate-pulse rounded-full bg-blue-600"></div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Mengunggah file sementara...
                        </p>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="open = false"
                            class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>

                        <button type="submit" wire:loading.attr="disabled" wire:target="uploads,uploadFiles"
                            class="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <span wire:loading.remove wire:target="uploadFiles">
                                Upload
                            </span>

                            <span wire:loading wire:target="uploadFiles">
                                Uploading...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Column Header --}}
    <div
        class="grid grid-cols-[minmax(0,1fr)_70px_70px_130px] gap-3 border-b border-gray-200 bg-gray-50 px-5 py-2.5 text-xs font-medium text-gray-500 dark:border-gray-700 dark:bg-gray-700/40 dark:text-gray-400">
        <span>Name</span>
        <span class="text-right">Size</span>
        <span class="text-right">Actions</span>
        <span class="text-right">Modified</span>
    </div>

    {{-- File Tree --}}
    <div class="divide-y divide-gray-100 dark:divide-gray-700/70 min-h-[350px]">

        @forelse ($nodes as $node)
            <div wire:key="file-node-{{ md5($node['path']) }}"
                class="grid grid-cols-[minmax(0,1fr)_70px_70px_130px] items-center gap-3 px-5 py-2.5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">

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

                {{-- Actions --}}
                <div class="flex items-center justify-end">

                    <button type="button"
                        wire:click="deleteItem(
                            @js($node['path']),
                            @js($node['type'])
                        )"
                        wire:confirm="Yakin ingin menghapus {{ $node['name'] }}? {{ $node['type'] === 'folder' ? 'Seluruh isi folder juga akan dihapus.' : '' }}"
                        wire:loading.attr="disabled" wire:target="deleteItem" title="Delete {{ $node['type'] }}"
                        class="inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-red-100 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:opacity-50 dark:text-gray-400 dark:hover:bg-red-900/30 dark:hover:text-red-400">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 7h12m-10 0V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m-9 0 1 13h8l1-13M10 11v5m4-5v5" />
                        </svg>
                    </button>

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
    {{-- <div class="border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-700/30">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ count($nodes) }} items shown
        </p>
    </div> --}}

    <div class="border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-700/30">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ count($nodes) }} item(s) shown
        </p>
    </div>

</div>
