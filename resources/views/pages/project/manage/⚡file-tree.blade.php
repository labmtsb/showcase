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

    #[Locked]
    public string $currentDirectory;

    #[Locked]
    public ?string $transferSourcePath = null;

    #[Locked]
    public ?string $transferSourceType = null;

    #[Locked]
    public ?string $transferOperation = null;

    #[Locked]
    public string $transferDestination = '';

    #[Locked]
    public ?string $renamePath = null;

    public string $renameName = '';

    public array $expanded = [];

    public array $uploads = [];

    public string $newItemType = 'file';

    public string $newItemName = '';

    public function mount(string $path): void
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        abort_if($path === '' || in_array('..', explode('/', $path), true), 400);

        $disk = Storage::disk('local');

        abort_unless($disk->directoryExists($path), 404);

        $this->rootPath = $path;

        $this->currentDirectory = $path;

        $this->rootPath = $path;

        $this->currentDirectory = $path;

        $this->transferDestination = $path;
    }

    public function toggleFolder(string $path): void
    {
        abort_unless($this->isInsideRoot($path), 403);

        $disk = Storage::disk('local');
        abort_unless($disk->directoryExists($path), 404);

        if (isset($this->expanded[$path])) {
            unset($this->expanded[$path]);

            // Jika folder yang ditutup adalah direktori aktif
            // atau merupakan induk dari direktori aktif,
            // kembalikan direktori aktif ke folder induknya.
            if ($this->currentDirectory === $path || str_starts_with($this->currentDirectory, $path . '/')) {
                $this->currentDirectory = dirname($path);
            }

            // Tutup juga seluruh subfolder di dalamnya.
            foreach (array_keys($this->expanded) as $expandedPath) {
                if (str_starts_with($expandedPath, $path . '/')) {
                    unset($this->expanded[$expandedPath]);
                }
            }
        } else {
            $this->expanded[$path] = true;

            // Jadikan folder yang dibuka sebagai direktori aktif.
            $this->currentDirectory = $path;
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

    public function with(): array
    {
        return [
            'nodes' => $this->getCurrentDirectoryItems(),
            'breadcrumbs' => $this->getBreadcrumbs(),

            'transferBreadcrumbs' => $this->getTransferBreadcrumbs(),
            'transferDirectories' => $this->getTransferDirectories(),
        ];
    }

    public function navigateTo(string $path): void
    {
        $path = str_replace('\\', '/', $path);

        // Tolak path traversal.
        abort_if(in_array('..', explode('/', $path), true) || in_array('.', explode('/', $path), true), 403);

        $path = trim($path, '/');

        // Pastikan tujuan tidak keluar dari root project.
        abort_unless($this->isInsideRoot($path), 403);

        $disk = Storage::disk('local');

        abort_unless($disk->directoryExists($path), 404);

        $this->currentDirectory = $path;
    }

    public function goUp(): void
    {
        // Tidak boleh keluar dari root project.
        if ($this->currentDirectory === $this->rootPath) {
            return;
        }

        $parent = dirname($this->currentDirectory);

        // Pengaman tambahan.
        if (!$this->isInsideRoot($parent)) {
            $parent = $this->rootPath;
        }

        $this->currentDirectory = $parent;
    }

    private function getCurrentDirectoryItems(): array
    {
        $disk = Storage::disk('local');

        $directories = $disk->directories($this->currentDirectory);
        $files = $disk->files($this->currentDirectory);

        usort($directories, fn($a, $b) => strcasecmp(basename($a), basename($b)));

        usort($files, fn($a, $b) => strcasecmp(basename($a), basename($b)));

        $nodes = [];

        foreach ($directories as $directory) {
            $nodes[] = [
                'name' => basename($directory),
                'path' => $directory,
                'type' => 'folder',
            ];
        }

        foreach ($files as $file) {
            $nodes[] = [
                'name' => basename($file),
                'path' => $file,
                'type' => 'file',
                'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                'size' => $this->formatBytes($disk->size($file)),
                'modified' => date('d M Y H:i', $disk->lastModified($file)),
            ];
        }

        return $nodes;
    }

    private function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            [
                'name' => basename($this->rootPath),
                'path' => $this->rootPath,
            ],
        ];

        $relativePath = trim(substr($this->currentDirectory, strlen($this->rootPath)), '/');

        if ($relativePath === '') {
            return $breadcrumbs;
        }

        $currentPath = $this->rootPath;

        foreach (explode('/', $relativePath) as $segment) {
            $currentPath .= '/' . $segment;

            $breadcrumbs[] = [
                'name' => $segment,
                'path' => $currentPath,
            ];
        }

        return $breadcrumbs;
    }

    private function getTransferBreadcrumbs(): array
    {
        $breadcrumbs = [
            [
                'name' => basename($this->rootPath),
                'path' => $this->rootPath,
            ],
        ];

        $relativePath = trim(substr($this->transferDestination, strlen($this->rootPath)), '/');

        if ($relativePath === '') {
            return $breadcrumbs;
        }

        $currentPath = $this->rootPath;

        foreach (explode('/', $relativePath) as $segment) {
            $currentPath .= '/' . $segment;

            $breadcrumbs[] = [
                'name' => $segment,
                'path' => $currentPath,
            ];
        }

        return $breadcrumbs;
    }

    private function getTransferDirectories(): array
    {
        $disk = Storage::disk('local');

        $directories = $disk->directories($this->transferDestination);

        $items = [];

        foreach ($directories as $directory) {
            $items[] = [
                'name' => basename($directory),
                'path' => $directory,
            ];
        }

        usort($items, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        return $items;
    }

    public function createItem(): void
    {
        $this->newItemName = trim($this->newItemName);

        $this->validate(
            [
                'newItemType' => ['required', 'in:file,folder'],
                'newItemName' => ['required', 'string', 'max:255', 'not_regex:/[\/\\\\]/', 'not_in:.,..', 'regex:/^[^<>:"|?*\x00-\x1F]+$/u'],
            ],
            [
                'newItemType.in' => 'Jenis item tidak valid.',
                'newItemName.required' => 'Nama file atau folder wajib diisi.',
                'newItemName.max' => 'Nama maksimal 255 karakter.',
                'newItemName.not_regex' => 'Nama tidak boleh mengandung garis miring.',
                'newItemName.not_in' => 'Nama tersebut tidak diperbolehkan.',
                'newItemName.regex' => 'Nama mengandung karakter yang tidak diperbolehkan.',
            ],
        );

        abort_unless($this->isInsideRoot($this->currentDirectory), 403);

        $disk = Storage::disk('local');

        abort_unless($disk->directoryExists($this->currentDirectory), 404);

        $path = $this->currentDirectory . '/' . $this->newItemName;

        // Jangan menimpa file atau folder yang sudah ada.
        // abort_if($disk->exists($path) || $disk->directoryExists($path), 409, 'File atau folder dengan nama tersebut sudah ada.');

        if ($disk->exists($path) || $disk->directoryExists($path)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'newItemName' => 'File atau folder dengan nama tersebut sudah ada.',
            ]);
        }

        if ($this->newItemType === 'folder') {
            $disk->makeDirectory($path);

            $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'Folder successfully created.');
        } else {
            // Membuat file kosong.
            $disk->put($path, '');

            $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'File successfully deleted.');
        }

        $this->reset('newItemName');
        $this->newItemType = 'file';

        $this->dispatch('item-created');
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

        // Pastikan direktori tujuan masih berada di dalam root.
        abort_unless($this->isInsideRoot($this->currentDirectory), 403);

        abort_unless($disk->directoryExists($this->currentDirectory), 404);

        $uploadedCount = 0;

        foreach ($this->uploads as $file) {
            $originalName = basename($file->getClientOriginalName());

            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);

            $safeName = $safeName ?: Str::uuid()->toString();

            $filename = $safeName;
            $counter = 1;

            while ($disk->exists($this->currentDirectory . '/' . $filename)) {
                $extension = pathinfo($safeName, PATHINFO_EXTENSION);
                $name = pathinfo($safeName, PATHINFO_FILENAME);

                $filename = $name . '-' . $counter++;

                if ($extension !== '') {
                    $filename .= '.' . $extension;
                }
            }

            $file->storeAs($this->currentDirectory, $filename, 'local');

            $uploadedCount++;
        }

        $this->reset('uploads');

        $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'file(s) successfully uploaded.');

        $this->dispatch('upload-completed');
    }

    public function startTransfer(string $path, string $operation): void
    {
        abort_unless(in_array($operation, ['copy', 'move'], true), 400);

        $path = str_replace('\\', '/', $path);

        abort_if(in_array('..', explode('/', $path), true) || in_array('.', explode('/', $path), true), 403);

        $path = trim($path, '/');

        abort_unless($this->isInsideRoot($path), 403);
        abort_if($path === $this->rootPath, 403);

        $disk = Storage::disk('local');

        $isDirectory = $disk->directoryExists($path);
        $isFile = $disk->exists($path) && !$isDirectory;

        abort_unless($isDirectory || $isFile, 404);

        $this->transferSourcePath = $path;
        $this->transferSourceType = $isDirectory ? 'folder' : 'file';
        $this->transferOperation = $operation;
        $this->transferDestination = $this->currentDirectory;

        $this->resetValidation();

        $this->dispatch('open-transfer-modal');
    }

    public function navigateTransferTo(string $path): void
    {
        $path = str_replace('\\', '/', $path);

        abort_if(in_array('..', explode('/', $path), true) || in_array('.', explode('/', $path), true), 403);

        $path = trim($path, '/');

        abort_unless($this->isInsideRoot($path), 403);

        $disk = Storage::disk('local');

        abort_unless($disk->directoryExists($path), 404);

        $this->transferDestination = $path;
    }

    public function transferGoUp(): void
    {
        if ($this->transferDestination === $this->rootPath) {
            return;
        }

        $parent = dirname($this->transferDestination);

        if (!$this->isInsideRoot($parent)) {
            $parent = $this->rootPath;
        }

        $this->transferDestination = $parent;
    }

    public function confirmTransfer(): void
    {
        abort_unless(in_array($this->transferOperation, ['copy', 'move'], true), 400);

        abort_unless(in_array($this->transferSourceType, ['file', 'folder'], true), 400);

        $source = $this->transferSourcePath;
        $destination = $this->transferDestination;

        abort_if(!$source || !$this->isInsideRoot($source), 403);

        abort_if($source === $this->rootPath, 403);

        abort_unless($this->isInsideRoot($destination), 403);

        $disk = Storage::disk('local');

        abort_unless($disk->directoryExists($destination), 404);

        // Pastikan sumber masih tersedia dan tipenya belum berubah.
        if ($this->transferSourceType === 'folder') {
            abort_unless($disk->directoryExists($source), 404);
        } else {
            abort_unless($disk->exists($source) && !$disk->directoryExists($source), 404);
        }

        // Folder tidak boleh dipindahkan/disalin ke dirinya sendiri
        // atau ke salah satu subfolder di dalamnya.
        if ($this->transferSourceType === 'folder') {
            abort_if($destination === $source || str_starts_with($destination, $source . '/'), 422, 'Folder tidak dapat disalin atau dipindahkan ke dalam dirinya sendiri.');
        }

        $filename = basename($source);
        $target = $destination . '/' . $filename;

        // Jangan menimpa item yang sudah ada.
        if ($disk->exists($target) || $disk->directoryExists($target)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'transferDestination' => 'Item dengan nama yang sama sudah ada di folder tujuan.',
            ]);
        }

        $operation = $this->transferOperation;

        if ($this->transferSourceType === 'file') {
            $success = $operation === 'copy' ? $disk->copy($source, $target) : $disk->move($source, $target);

            abort_unless($success, 500, 'Gagal memproses file.');
        } else {
            // Copy seluruh isi folder beserta subfolder-nya.
            $success = $disk->copyDirectory($source, $target);

            abort_unless($success, 500, 'Gagal menyalin folder.');

            if ($operation === 'move') {
                // Setelah berhasil disalin, hapus folder sumber.
                if (!$disk->deleteDirectory($source)) {
                    // Coba batalkan hasil copy jika penghapusan sumber gagal.
                    $disk->deleteDirectory($target);

                    abort(500, 'Gagal memindahkan folder.');
                }
            }
        }

        $this->successMessage = $operation === 'copy' ? 'Item berhasil disalin.' : 'Item berhasil dipindahkan.';

        $this->resetTransfer();

        $this->dispatch('transfer-completed');
    }

    private function resetTransfer(): void
    {
        $this->transferSourcePath = null;
        $this->transferSourceType = null;
        $this->transferOperation = null;
        $this->transferDestination = $this->currentDirectory;

        $this->resetValidation();
    }

    public function startRename(string $path): void
    {
        $path = str_replace('\\', '/', $path);

        abort_if(in_array('..', explode('/', $path), true) || in_array('.', explode('/', $path), true), 403);

        $path = trim($path, '/');

        abort_unless($this->isInsideRoot($path), 403);
        abort_if($path === $this->rootPath, 403);

        $disk = Storage::disk('local');

        $isDirectory = $disk->directoryExists($path);
        $isFile = $disk->exists($path) && !$isDirectory;

        abort_unless($isDirectory || $isFile, 404);

        $this->renamePath = $path;
        $this->renameName = basename($path);

        $this->resetValidation();

        $this->dispatch('open-rename-modal');
    }

    public function renameItem(): void
    {
        $this->renameName = trim($this->renameName);

        $this->validate(
            [
                'renameName' => ['required', 'string', 'max:255', 'not_regex:/[\/\\\\]/', 'not_in:.,..', 'regex:/^[^<>:"|?*\x00-\x1F]+$/u'],
            ],
            [
                'renameName.required' => 'Nama baru wajib diisi.',
                'renameName.max' => 'Nama maksimal 255 karakter.',
                'renameName.not_regex' => 'Nama tidak boleh mengandung garis miring.',
                'renameName.not_in' => 'Nama tersebut tidak diperbolehkan.',
                'renameName.regex' => 'Nama mengandung karakter yang tidak diperbolehkan.',
            ],
        );

        $source = $this->renamePath;

        abort_unless($source, 403);

        $source = str_replace('\\', '/', $source);

        abort_if(in_array('..', explode('/', $source), true) || in_array('.', explode('/', $source), true), 403);

        $source = trim($source, '/');

        abort_unless($this->isInsideRoot($source), 403);
        abort_if($source === $this->rootPath, 403);

        $disk = Storage::disk('local');

        $isDirectory = $disk->directoryExists($source);
        $isFile = $disk->exists($source) && !$isDirectory;

        abort_unless($isDirectory || $isFile, 404);

        $oldName = basename($source);
        $newName = $this->renameName;

        // Tidak perlu melakukan rename jika nama sama.
        if ($oldName === $newName) {
            $this->resetRename();
            $this->dispatch('rename-completed');

            return;
        }

        $parent = dirname($source);
        $target = $parent . '/' . $newName;

        // Jangan menimpa item yang sudah ada.
        if ($disk->exists($target) || $disk->directoryExists($target)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'renameName' => 'Nama tersebut sudah digunakan.',
            ]);
        }

        $success = $isDirectory ? $disk->move($source, $target) : $disk->move($source, $target);

        abort_unless($success, 500, 'Gagal mengubah nama item.');

        // Jika folder yang diubah nama merupakan direktori aktif
        // atau induk dari direktori aktif, perbarui path-nya.
        if ($this->currentDirectory === $source || str_starts_with($this->currentDirectory, $source . '/')) {
            $this->currentDirectory = $target . substr($this->currentDirectory, strlen($source));
        }

        // Jika ada direktori tujuan transfer yang berada di dalam folder
        // yang diubah nama, perbarui juga path tersebut.
        if ($this->transferDestination === $source || str_starts_with($this->transferDestination, $source . '/')) {
            $this->transferDestination = $target . substr($this->transferDestination, strlen($source));
        }

        $this->successMessage = 'Nama item berhasil diubah.';

        $this->resetRename();

        $this->dispatch('rename-completed');
    }

    private function resetRename(): void
    {
        $this->renamePath = null;
        $this->renameName = '';

        $this->resetValidation();
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

            $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'Folder successfully deleted.');

            return;
        }

        if ($type === 'file') {
            abort_unless($disk->exists($path) && !$disk->directoryExists($path), 404);

            $disk->delete($path);

            $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'file(s) successfully deleted.');

            return;
        }

        abort(400);
    }

    public function extractArchive(string $path): void
    {
        $path = str_replace('\\', '/', $path);

        abort_if(in_array('..', explode('/', $path), true) || in_array('.', explode('/', $path), true), 403);

        $path = trim($path, '/');

        // Pastikan arsip berada di dalam project.
        abort_unless($this->isInsideRoot($path), 403);
        abort_if($path === $this->rootPath, 403);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path) && !$disk->directoryExists($path), 404);

        // Hanya mendukung ZIP.
        abort_unless(strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'zip', 422, 'Format arsip tidak didukung.');

        // Pastikan PHP ZipArchive tersedia.
        abort_unless(class_exists(\ZipArchive::class), 500, 'Ekstensi PHP ZIP belum tersedia.');

        $archivePath = $disk->path($path);

        $zip = new \ZipArchive();

        $opened = $zip->open($archivePath);

        abort_if($opened !== true, 422, 'File ZIP tidak valid atau tidak dapat dibuka.');

        $targetDirectory = dirname($path) . '/' . pathinfo($path, PATHINFO_FILENAME);

        // Cegah penimpaan hasil ekstraksi sebelumnya.
        if ($disk->exists($targetDirectory) || $disk->directoryExists($targetDirectory)) {
            $zip->close();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'archive' => 'The extraction folder is ready.',
            ]);
        }

        $maxEntries = 1000;
        $maxUncompressedSize = 100 * 1024 * 1024; // 100 MB

        $entries = [];
        $totalSize = 0;

        try {
            // Tahap pertama: validasi seluruh isi arsip.
            if ($zip->numFiles > $maxEntries) {
                throw new \RuntimeException('The archive contains too many files.');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);

                if ($stat === false) {
                    throw new \RuntimeException("Can't read ZIP file information.");
                }

                $entryName = $stat['name'];

                // ZIP menggunakan slash sebagai pemisah direktori.
                // Tolak backslash agar tidak menjadi separator alternatif.
                if ($entryName === '' || str_contains($entryName, '\\') || str_contains($entryName, "\0") || str_starts_with($entryName, '/') || preg_match('/^[A-Za-z]:/', $entryName)) {
                    throw new \RuntimeException('The archive contains an unsafe file name.');
                }

                $isDirectory = str_ends_with($entryName, '/');

                $relativePath = $isDirectory ? rtrim($entryName, '/') : $entryName;

                $segments = explode('/', $relativePath);

                foreach ($segments as $segment) {
                    if ($segment === '' || $segment === '.' || $segment === '..') {
                        throw new \RuntimeException('The archive contains an unsafe path.');
                    }
                }

                // Tolak symbolic link.
                $opsys = 0;
                $attributes = 0;

                if ($zip->getExternalAttributesIndex($i, $opsys, $attributes)) {
                    if ($opsys === \ZipArchive::OPSYS_UNIX) {
                        $fileType = ($attributes >> 16) & 0170000;

                        if ($fileType === 0120000) {
                            throw new \RuntimeException('The archive contains symbolic links that are not allowed.');
                        }
                    }
                }

                $size = (int) ($stat['size'] ?? 0);

                if ($size < 0) {
                    throw new \RuntimeException('The file size inside the archive is invalid.');
                }

                $totalSize += $size;

                if ($totalSize > $maxUncompressedSize) {
                    throw new \RuntimeException('The total extraction size exceeds the 100 MB limit.');
                }

                $entries[] = [
                    'name' => $entryName,
                    'path' => $relativePath,
                    'directory' => $isDirectory,
                ];
            }

            // Tolak nama entry duplikat.
            $entryNames = [];

            foreach ($entries as $entry) {
                $key = strtolower($entry['path']);

                if (isset($entryNames[$key])) {
                    throw new \RuntimeException('The archive contains duplicate file names.');
                }

                $entryNames[$key] = true;
            }

            // Buat folder tujuan.
            $disk->makeDirectory($targetDirectory);

            // Tahap kedua: ekstraksi satu per satu.
            foreach ($entries as $entry) {
                $destination = $targetDirectory . '/' . $entry['path'];

                if ($entry['directory']) {
                    $disk->makeDirectory($destination);
                    continue;
                }

                $parentDirectory = dirname($destination);

                if (!$disk->directoryExists($parentDirectory)) {
                    $disk->makeDirectory($parentDirectory);
                }

                $stream = $zip->getStream($entry['name']);

                if ($stream === false) {
                    throw new \RuntimeException('Failed to read the file inside the archive.');
                }

                try {
                    $success = $disk->writeStream($destination, $stream);
                } finally {
                    fclose($stream);
                }

                if (!$success) {
                    throw new \RuntimeException('Gagal mengekstrak file: ' . $entry['name']);
                }
            }

            $zip->close();
        } catch (\Throwable $exception) {
            $zip->close();

            // Hapus hasil ekstraksi parsial jika terjadi kegagalan.
            if ($disk->directoryExists($targetDirectory)) {
                $disk->deleteDirectory($targetDirectory);
            }

            report($exception);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'archive' => 'Extraction failed: ' . $exception->getMessage(),
            ]);
        }

        $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'The archive was successfully extracted to the folder.' . basename($targetDirectory) . '.');

        $this->dispatch('archive-extracted');
    }

    public function clearArchiveError(): void
    {
        $this->resetValidation('archive');
    }

    public function openFileEditor(string $path): void
    {
        $path = str_replace('\\', '/', $path);

        abort_if(in_array('..', explode('/', $path), true) || in_array('.', explode('/', $path), true), 403);

        $path = trim($path, '/');

        abort_unless($this->isInsideRoot($path), 403);
        abort_if($path === $this->rootPath, 403);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path) && !$disk->directoryExists($path), 404);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $editableExtensions = ['html', 'htm', 'css', 'scss', 'sass', 'less', 'js', 'mjs', 'cjs', 'ts', 'tsx', 'jsx', 'vue', 'php', 'phtml', 'py', 'json', 'xml', 'svg', 'md', 'txt', 'yml', 'yaml', 'toml', 'ini', 'sql', 'sh', 'bash', 'java', 'c', 'h', 'cpp', 'cs', 'go', 'rs', 'rb', 'dart', 'kt', 'swift'];

        abort_unless(in_array($extension, $editableExtensions, true), 422, 'Format file tidak didukung oleh text editor.');

        // Batasi ukuran file yang dibuka.
        abort_if($disk->size($path) > 2 * 1024 * 1024, 413, 'Ukuran file terlalu besar untuk dibuka.');

        $this->dispatch('open-project-file-editor', path: $path);
    }
};
?>

<div
    {{ $attributes->class([
        'w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-x-auto relative',
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

        {{-- Operation Section --}}
        <div class="flex justify-end gap-2">
            <button type="button" @click="$dispatch('open-create-modal')"
                class="inline-flex items-center gap-2 rounded bg-blue-600 px-2 py-1.5
               text-sm font-medium text-white text-xs transition-colors
               hover:bg-blue-700 focus:outline-none focus:ring-4
               focus:ring-blue-300 dark:focus:ring-blue-800 cursor-pointer">

                <x-lucide-plus class="w-4 h-4" />
                Create New
            </button>

            <button type="button" @click="$dispatch('open-upload-modal')"
                class="inline-flex items-center gap-2 rounded bg-blue-600 px-2 py-1.5
               text-sm font-medium text-white text-xs transition-colors
               hover:bg-blue-700 focus:outline-none focus:ring-4
               focus:ring-blue-300 dark:focus:ring-blue-800 cursor-pointer">

                <x-lucide-upload class="w-4 h-4" />
                Upload Files
            </button>
        </div>
    </div>

    @error('archive')
        <div class="mb-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
            role="alert">
            <svg class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v4m0 4h.01M10.3 3.86l-8 14A2 2 0 004 21h16a2 2 0 001.7-3.14l-8-14a2 2 0 00-3.4 0z" />
            </svg>

            <div class="flex-1">
                {{ $message }}
            </div>

            <button type="button" wire:click="clearArchiveError" class="text-red-500 hover:text-red-700">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @enderror

    @island(name: 'filemanager', lazy: true)

        @placeholder
            <div class="flex flex-col items-center justify-center py-12">

                <svg class="w-20 h-20 text-brand" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">

                    {{-- File --}}
                    <path d="M28 15H55L72 32V85H28V15Z" stroke="currentColor" stroke-width="4" fill="none" />

                    {{-- Fold --}}
                    <path d="M55 15V32H72" stroke="currentColor" stroke-width="4" />

                    {{-- Loading Bar --}}
                    <rect x="35" y="55" width="30" height="6" rx="3" fill="currentColor">

                        <animate attributeName="width" values="0;30;0" dur="1.5s" repeatCount="indefinite" />
                    </rect>

                </svg>

                <p class="mt-3 text-sm text-body animate-pulse">
                    Loading file...
                </p>

            </div>
        @endplaceholder

        {{-- navigasi --}}
        <div class="flex flex-wrap items-center gap-3 border-b border-gray-200 px-3 py-2 dark:border-gray-700">

            {{-- Back Button --}}
            <button type="button" wire:click="goUp" @disabled($currentDirectory === $rootPath)
                class="inline-flex shrink-0 items-center gap-2 rounded border border-gray-100 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-80 dark:border-gray-600 dark:text-gray-100 dark:hover:bg-gray-700">

                <x-lucide-arrow-left class="w-4 h-4" />
            </button>

            {{-- Breadcrumb --}}
            <nav class="flex min-w-0 items-center gap-1 overflow-x-auto text-sm">

                @foreach ($breadcrumbs as $index => $breadcrumb)
                    @if ($index > 0)
                        <svg class="size-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
                        </svg>
                    @endif

                    @if ($loop->last)
                        <span class="truncate font-semibold text-gray-900 dark:text-white">
                            {{ $breadcrumb['name'] }}
                        </span>
                    @else
                        <button type="button" wire:click="navigateTo(@js($breadcrumb['path']))"
                            class="shrink-0 text-gray-500 transition-colors hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                            {{ $breadcrumb['name'] }}
                        </button>
                    @endif
                @endforeach

            </nav>

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
                                Choose the file you want to add to the project.
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

                    <div
                        class="mt-3 rounded mb-3 border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-700/50">
                        <div class="flex items-center gap-2">
                            <svg class="size-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                            </svg>

                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Upload destination
                            </span>
                        </div>

                        <p class="mt-1 break-all text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ $currentDirectory }}
                        </p>
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
                                The maximum size of each file is 20 MB.
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

        {{-- modal create item --}}
        <div x-data="{ open: false }" x-on:open-create-modal.window="open = true" x-on:item-created.window="open = false"
            x-on:keydown.escape.window="open = false">
            <div x-show="open" x-cloak x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4"
                @click.self="open = false">
                <div x-show="open" x-transition
                    class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                    {{-- Header --}}
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Create New
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Create a new file or folder.
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

                    <form wire:submit="createItem">

                        {{-- Item Type --}}
                        <div class="mb-5">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Item Type
                            </label>

                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="newItemType" value="file"
                                        class="peer sr-only">

                                    <div
                                        class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 p-3 text-sm font-medium text-gray-600 transition-colors peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-600 dark:border-gray-600 dark:text-gray-300 dark:peer-checked:border-blue-500 dark:peer-checked:bg-blue-500/10 dark:peer-checked:text-blue-400">
                                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z" />
                                        </svg>
                                        New File
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="newItemType" value="folder"
                                        class="peer sr-only">

                                    <div
                                        class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 p-3 text-sm font-medium text-gray-600 transition-colors peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-600 dark:border-gray-600 dark:text-gray-300 dark:peer-checked:border-blue-500 dark:peer-checked:bg-blue-500/10 dark:peer-checked:text-blue-400">
                                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                        </svg>
                                        New Folder
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Item Name --}}
                        <div class="mb-5">
                            <label for="new-item-name"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Name
                            </label>

                            <input id="new-item-name" type="text" wire:model="newItemName"
                                placeholder="Enter file or folder name" autocomplete="off"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">

                            @error('newItemName')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror

                            @error('newItemType')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Destination --}}
                        <div
                            class="mb-5 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-700/50">
                            <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Location
                            </p>

                            <p class="break-all text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $currentDirectory }}
                            </p>
                        </div>

                        {{-- Footer --}}
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="open = false"
                                class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Cancel
                            </button>

                            <button type="submit" wire:loading.attr="disabled" wire:target="createItem"
                                class="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                                <span wire:loading.remove wire:target="createItem">
                                    Create
                                </span>

                                <span wire:loading wire:target="createItem">
                                    Creating...
                                </span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- modal move / copy --}}
        <div x-data="{ open: false }" x-on:open-transfer-modal.window="open = true"
            x-on:transfer-completed.window="open = false" x-on:keydown.escape.window="open = false">
            <div x-show="open" x-cloak x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4"
                @click.self="open = false">
                <div x-show="open" x-transition
                    class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                    {{-- Header --}}
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $transferOperation === 'copy' ? 'Copy Item' : 'Move Item' }}
                            </h3>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Choose the destination directory.
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

                    {{-- Source Information --}}
                    <div
                        class="mb-5 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-700/50">
                        <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                            Source
                        </p>

                        <div class="flex items-center gap-2">
                            @if ($transferSourceType === 'folder')
                                <svg class="size-5 shrink-0 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 4a2 2 0 012-2h4l2 2h6a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V4z" />
                                </svg>
                            @else
                                <svg class="size-5 shrink-0 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z" />
                                </svg>
                            @endif

                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ basename($transferSourcePath ?? '') }}
                                </p>

                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $transferSourcePath }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Destination Browser --}}
                    <div class="mb-5">
                        <div class="mb-3 flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Destination
                            </label>

                            <button type="button" wire:click="transferGoUp" @disabled($transferDestination === $rootPath)
                                class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-700">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m15 19-7-7 7-7" />
                                </svg>
                                Back
                            </button>
                        </div>

                        {{-- Breadcrumb --}}
                        <div
                            class="mb-3 flex flex-wrap items-center gap-1 rounded-lg border border-gray-200 p-3 text-sm dark:border-gray-700">
                            @foreach ($transferBreadcrumbs as $breadcrumb)
                                @if (!$loop->first)
                                    <svg class="size-4 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m9 18 6-6-6-6" />
                                    </svg>
                                @endif

                                @if ($loop->last)
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ $breadcrumb['name'] }}
                                    </span>
                                @else
                                    <button type="button" wire:click="navigateTransferTo(@js($breadcrumb['path']))"
                                        class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                                        {{ $breadcrumb['name'] }}
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        {{-- Folder List --}}
                        <div class="max-h-56 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">

                            @forelse ($transferDirectories as $directory)
                                <button type="button" wire:click="navigateTransferTo(@js($directory['path']))"
                                    wire:key="transfer-folder-{{ md5($directory['path']) }}"
                                    class="flex w-full items-center gap-3 border-b border-gray-100 px-3 py-3 text-left transition-colors last:border-b-0 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                                    <svg class="size-5 shrink-0 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 4a2 2 0 012-2h4l2 2h6a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V4z" />
                                    </svg>

                                    <span
                                        class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ $directory['name'] }}
                                    </span>

                                    <svg class="size-4 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            @empty
                                <div class="flex flex-col items-center justify-center py-8 text-center">
                                    <svg class="mb-2 size-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                    </svg>

                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        No subfolders found.
                                    </p>
                                </div>
                            @endforelse

                        </div>

                        @error('transferDestination')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-2 break-all text-xs text-gray-500 dark:text-gray-400">
                            Selected: {{ $transferDestination }}
                        </p>
                    </div>

                    {{-- Footer --}}
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="open = false"
                            class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>

                        <button type="button" wire:click="confirmTransfer"
                            wire:confirm="{{ $transferOperation === 'move' ? 'Yakin ingin memindahkan item ini?' : 'Yakin ingin menyalin item ini?' }}"
                            wire:loading.attr="disabled" wire:target="confirmTransfer"
                            class="inline-flex items-center gap-2 rounded px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 {{ $transferOperation === 'move' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                            <span wire:loading.remove wire:target="confirmTransfer">
                                {{ $transferOperation === 'move' ? 'Move Here' : 'Copy Here' }}
                            </span>

                            <span wire:loading wire:target="confirmTransfer">
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- modal rename --}}
        <div x-data="{ open: false }" x-on:open-rename-modal.window="open = true"
            x-on:rename-completed.window="open = false" x-on:keydown.escape.window="open = false">
            <div x-show="open" x-cloak x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4"
                @click.self="open = false">
                <div x-show="open" x-transition
                    class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                    {{-- Header --}}
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Rename Item
                            </h3>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Enter a new name for this item.
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

                    <form wire:submit="renameItem">

                        {{-- Current Name --}}
                        <div
                            class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-700/50">
                            <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Current Name
                            </p>

                            <p class="break-all text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ basename($renamePath ?? '') }}
                            </p>
                        </div>

                        {{-- New Name --}}
                        <div class="mb-5">
                            <label for="rename-item-name"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                New Name
                            </label>

                            <input id="rename-item-name" type="text" wire:model="renameName" autocomplete="off"
                                placeholder="Enter new name"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">

                            @error('renameName')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Footer --}}
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="open = false"
                                class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Cancel
                            </button>

                            <button type="submit" wire:loading.attr="disabled" wire:target="renameItem"
                                class="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                                <span wire:loading.remove wire:target="renameItem">
                                    Rename
                                </span>

                                <span wire:loading wire:target="renameItem">
                                    Renaming...
                                </span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- modal editor --}}
        <livewire:pages::project.manage.editor :root-path="$rootPath" />

        {{-- Column Header --}}
        <div
            class="grid grid-cols-[minmax(0,1fr)_70px_140px_120px] gap-3 border-b border-gray-200 bg-gray-50 px-5 py-2.5 text-xs font-medium text-gray-500 dark:border-gray-700 dark:bg-gray-700/40 dark:text-gray-400">
            <span>Name</span>
            <span class="text-right">Size</span>
            <span class="text-right">Actions</span>
            <span class="text-right">Modified</span>
        </div>

        {{-- File Tree --}}
        <div class="divide-y divide-gray-100 dark:divide-gray-700/70 min-h-[350px]">

            @forelse ($nodes as $node)
                <div wire:key="file-node-{{ md5($node['path']) }}"
                    class="grid grid-cols-[minmax(0,1fr)_70px_140px_120px] items-center gap-3 px-5 py-2.5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">

                    {{-- Name --}}
                    <div class="flex min-w-0 items-center gap-2">

                        @if ($node['type'] === 'folder')
                            <button type="button" wire:click="navigateTo(@js($node['path']))"
                                class="inline-flex min-w-0 items-center gap-2 text-left font-medium text-gray-800 hover:text-blue-600 dark:text-gray-200 dark:hover:text-blue-400">
                                <svg class="size-5 shrink-0 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 4a2 2 0 012-2h4l2 2h6a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V4z" />
                                </svg>

                                <span class="truncate">{{ $node['name'] }}</span>
                            </button>
                        @else
                            @php
                                $extension = $node['extension'];

                                $iconColor = match (true) {
                                    in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])
                                        => 'text-purple-500',
                                    $extension === 'pdf' => 'text-red-500',
                                    in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz']) => 'text-yellow-600',
                                    in_array($extension, ['php', 'js', 'ts', 'html', 'css', 'json', 'py'])
                                        => 'text-blue-500',
                                    in_array($extension, ['doc', 'docx', 'txt', 'md']) => 'text-sky-500',
                                    default => 'text-gray-400',
                                };
                            @endphp

                            {{-- <span class="w-4 shrink-0"></span> --}}

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
                    <div class="grid grid-cols-5 items-center justify-end gap-1">
                        {{-- extract --}}
                        @if ($node['type'] === 'file' && strtolower($node['extension'] ?? '') === 'zip')
                            <button type="button" wire:click="extractArchive(@js($node['path']))"
                                wire:confirm="Extract {{ $node['name'] }} to a new folder in this directory?"
                                wire:loading.attr="disabled" wire:target="extractArchive" title="Extract archive"
                                class="inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-purple-50 hover:text-purple-600 dark:text-gray-400 dark:hover:bg-purple-500/10 dark:hover:text-purple-400">

                                <x-lucide-square-arrow-up-right-exit class="w-3 h-3" />
                            </button>
                        @endif

                        {{-- edit --}}
                        @if (
                            $node['type'] === 'file' &&
                                in_array(strtolower($node['extension'] ?? ''),
                                    [
                                        'html',
                                        'htm',
                                        'css',
                                        'scss',
                                        'sass',
                                        'less',
                                        'js',
                                        'mjs',
                                        'cjs',
                                        'ts',
                                        'tsx',
                                        'jsx',
                                        'vue',
                                        'php',
                                        'phtml',
                                        'py',
                                        'json',
                                        'xml',
                                        'svg',
                                        'md',
                                        'txt',
                                        'yml',
                                        'yaml',
                                        'toml',
                                        'ini',
                                        'sql',
                                        'sh',
                                        'bash',
                                        'java',
                                        'c',
                                        'h',
                                        'cpp',
                                        'cs',
                                        'go',
                                        'rs',
                                        'rb',
                                        'dart',
                                        'kt',
                                        'swift',
                                    ],
                                    true))
                            <button type="button" wire:click="openFileEditor(@js($node['path']))"
                                wire:loading.attr="disabled" wire:target="openFileEditor"
                                title="Edit {{ $node['name'] }}"
                                class="inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-blue-500/10 dark:hover:text-blue-400">

                                <x-lucide-square-pen class="w-3 h-3" />
                            </button>
                        @endif

                        {{-- Rename --}}
                        <button type="button" wire:click="startRename(@js($node['path']))"
                            wire:loading.attr="disabled" wire:target="startRename" title="Rename {{ $node['type'] }}"
                            class="col-start-2 col-end-2 inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-emerald-50 hover:text-emerald-600 dark:text-gray-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400">

                            <x-lucide-pencil-line class="w-3 h-3" />
                        </button>

                        {{-- Copy --}}
                        <button type="button" wire:click="startTransfer(@js($node['path']), 'copy')"
                            wire:loading.attr="disabled" wire:target="startTransfer" title="Copy {{ $node['type'] }}"
                            class="col-start-3 col-end-3 inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-blue-500/10 dark:hover:text-blue-400">

                            <x-lucide-copy class="w-3 h-3" />
                        </button>

                        {{-- Move --}}
                        <button type="button" wire:click="startTransfer(@js($node['path']), 'move')"
                            wire:loading.attr="disabled" wire:target="startTransfer" title="Move {{ $node['type'] }}"
                            class="col-start-4 col-end-4 inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-amber-50 hover:text-amber-600 dark:text-gray-400 dark:hover:bg-amber-500/10 dark:hover:text-amber-400">

                            <x-lucide-scissors class="w-3 h-3" />
                        </button>

                        {{-- Delete --}}
                        <button type="button"
                            wire:click="deleteItem(
                            @js($node['path']),
                            @js($node['type'])
                        )"
                            wire:confirm="Yakin ingin menghapus {{ $node['name'] }}? {{ $node['type'] === 'folder' ? 'Seluruh isi folder juga akan dihapus.' : '' }}"
                            wire:loading.attr="disabled" wire:target="deleteItem" title="Delete {{ $node['type'] }}"
                            class="col-start-5 col-end-5 inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-red-100 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:opacity-50 dark:text-gray-400 dark:hover:bg-red-900/30 dark:hover:text-red-400">

                            <x-lucide-trash class="w-3 h-3" />
                        </button>

                    </div>

                    {{-- Modified --}}
                    <div class="truncate text-right text-xs text-gray-500 dark:text-gray-400">
                        {{ $node['type'] === 'file' ? $node['modified'] : '—' }}
                    </div>


                </div>

            @empty

                <div class="flex flex-col items-center justify-center px-5 py-15 text-center">
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

        <div
            class="border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-700/30 absolute bottom-0 w-full">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ count($nodes) }} item(s) shown
            </p>
        </div>
    @endisland

</div>
