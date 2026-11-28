<?php

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

new class extends Component {
    #[Locked]
    public string $rootPath;

    #[Locked]
    public ?string $filePath = null;

    public bool $isOpen = false;

    public string $content = '';

    #[Locked]
    public string $originalContent = '';

    public function mount(string $rootPath): void
    {
        $rootPath = trim(str_replace('\\', '/', $rootPath), '/');

        abort_if($rootPath === '' || in_array('..', explode('/', $rootPath), true) || in_array('.', explode('/', $rootPath), true), 400);

        abort_unless(Storage::disk('local')->directoryExists($rootPath), 404);

        $this->rootPath = $rootPath;
    }

    private function editableExtensions(): array
    {
        return ['html', 'htm', 'css', 'scss', 'sass', 'less', 'js', 'mjs', 'cjs', 'ts', 'tsx', 'jsx', 'vue', 'php', 'phtml', 'py', 'json', 'xml', 'svg', 'md', 'txt', 'yml', 'yaml', 'toml', 'ini', 'sql', 'sh', 'bash', 'java', 'c', 'h', 'cpp', 'cs', 'go', 'rs', 'rb', 'dart', 'kt', 'swift'];
    }

    private function isInsideRoot(string $path): bool
    {
        return $path === $this->rootPath || str_starts_with($path, $this->rootPath . '/');
    }

    private function validateEditorPath(): string
    {
        abort_unless($this->filePath, 403);

        $path = str_replace('\\', '/', $this->filePath);

        abort_if(in_array('..', explode('/', $path), true) || in_array('.', explode('/', $path), true), 403);

        $path = trim($path, '/');

        abort_unless($this->isInsideRoot($path), 403);
        abort_if($path === $this->rootPath, 403);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        abort_unless(in_array($extension, $this->editableExtensions(), true), 422);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path) && !$disk->directoryExists($path), 404);

        abort_if($disk->size($path) > 2 * 1024 * 1024, 413, 'Ukuran file melebihi batas editor.');

        return $path;
    }

    #[On('open-project-file-editor')]
    public function openEditor(string $path): void
    {
        $path = str_replace('\\', '/', $path);

        abort_if(in_array('..', explode('/', $path), true) || in_array('.', explode('/', $path), true), 403);

        $path = trim($path, '/');

        abort_unless($this->isInsideRoot($path), 403);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path) && !$disk->directoryExists($path), 404);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        abort_unless(in_array($extension, $this->editableExtensions(), true), 422);

        abort_if($disk->size($path) > 2 * 1024 * 1024, 413, 'Ukuran file terlalu besar untuk dibuka.');

        $content = $disk->get($path);

        // Tolak file binary dan teks yang bukan UTF-8 valid.
        if (str_contains($content, "\0") || preg_match('//u', $content) !== 1) {
            abort(422, 'File bukan teks yang dapat diedit.');
        }

        $this->filePath = $path;
        $this->content = $content;
        $this->originalContent = $content;
        $this->isOpen = true;

        $this->resetValidation();
    }

    public function saveFile(): void
    {
        $this->validate(
            [
                'content' => ['present', 'string', 'max:2097152'],
            ],
            [
                'content.max' => 'Ukuran konten maksimal 2 MB.',
            ],
        );

        $path = $this->validateEditorPath();

        if (str_contains($this->content, "\0") || preg_match('//u', $this->content) !== 1) {
            throw ValidationException::withMessages([
                'content' => 'Konten file tidak valid.',
            ]);
        }

        $disk = Storage::disk('local');

        // Pastikan file belum berubah menjadi terlalu besar.
        $success = $disk->put($path, $this->content);

        abort_unless($success, 500, 'Gagal menyimpan file.');

        $this->originalContent = $this->content;

        $this->dispatch('project-file-editor-saved');

        $this->dispatch('project-file-editor-notification', message: 'File berhasil disimpan.');
    }

    public function closeEditor(): void
    {
        $this->isOpen = false;

        $this->filePath = null;
        $this->content = '';
        $this->originalContent = '';

        $this->resetValidation();
    }

    public function with(): array
    {
        return [
            'fileName' => $this->filePath ? basename($this->filePath) : null,

            'extension' => $this->filePath ? strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION)) : null,
        ];
    }
};

?>

<div>
    @if ($isOpen)
        <div class="fixed inset-0 z-[100] flex h-dvh w-screen flex-col bg-white dark:bg-gray-900" x-data
            x-on:keydown.escape.window="$wire.closeEditor()">
            {{-- Header --}}
            <header
                class="flex shrink-0 items-center justify-between border-b border-gray-200 px-5 py-3 dark:border-gray-700">

                <div class="flex min-w-0 items-center gap-3">

                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">

                        <x-lucide-file-text class="w-5 h-5" />
                    </div>

                    <div class="min-w-0">
                        <h2 class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $fileName }}
                        </h2>

                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $filePath }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">

                    <button type="button" wire:click="closeEditor"
                        @if ($content !== $originalContent) wire:confirm="Ada perubahan yang belum disimpan. Tutup editor dan buang perubahan?" @endif
                        class="rounded border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        Close
                    </button>

                    <button type="button" wire:click="saveFile" wire:loading.attr="disabled" wire:target="saveFile"
                        class="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">

                        <x-lucide-save class="w-4 h-4" />

                        <span wire:loading.remove wire:target="saveFile">
                            Save
                        </span>

                        <span wire:loading wire:target="saveFile">
                            Saving...
                        </span>
                    </button>

                </div>
            </header>

            {{-- Editor Toolbar --}}
            <div
                class="flex shrink-0 items-center justify-between border-b border-gray-200 bg-gray-50 px-5 py-2 dark:border-gray-700 dark:bg-gray-800">

                <div class="flex items-center gap-3">
                    <span
                        class="rounded bg-gray-200 px-2 py-1 text-xs font-semibold uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        {{ $extension }}
                    </span>

                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Plain Text Editor
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @if ($content !== $originalContent)
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        <span class="text-xs text-amber-600 dark:text-amber-400">
                            Unsaved changes
                        </span>
                    @else
                        <span class="text-xs text-green-600 dark:text-green-400">
                            All changes saved
                        </span>
                    @endif
                </div>

            </div>

            {{-- Editor Area --}}
            <div class="min-h-0 flex-1 overflow-hidden bg-white dark:bg-gray-900">

                {{-- <textarea wire:model="content" spellcheck="false" autocomplete="off" autocapitalize="off"
                    class="block size-full resize-none border-0 bg-transparent p-6 font-mono text-sm leading-6 text-gray-800 outline-none focus:ring-0 dark:text-gray-200"
                    placeholder="Start writing..." wrap="off" autofocus></textarea> --}}

                <div wire:ignore wire:key="project-code-editor-{{ md5($filePath ?? '') }}" x-data="projectCodeEditor(
                    $wire,
                    @js($content),
                    @js($extension)
                )"
                    x-init="init()" class="size-full overflow-hidden">
                    <div x-ref="editor" class="size-full"></div>
                </div>

            </div>

            {{-- Validation Error --}}
            @error('content')
                <div
                    class="shrink-0 border-t border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                    {{ $message }}
                </div>
            @enderror

            {{-- Footer --}}
            <footer
                class="flex shrink-0 items-center justify-between border-t border-gray-200 bg-gray-50 px-5 py-2 dark:border-gray-700 dark:bg-gray-800">

                <span class="truncate text-xs text-gray-500 dark:text-gray-400">
                    {{ $fileName }}
                </span>

                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ strlen($content) }} bytes
                </span>

            </footer>
        </div>
    @endif
</div>
