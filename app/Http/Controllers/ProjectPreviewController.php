<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectPreviewController extends Controller
{
    public function show(Request $request, string $user, string $project, ?string $path = null): StreamedResponse
    {
        $owner = User::findOrFail($user);

        $project = Project::query()
            ->where('user_id', $owner->id)
            ->where('slug', $project)
            ->firstOrFail();

        $authenticatedUser = $request->user();

        $isOwner = $authenticatedUser &&
        (string) $authenticatedUser->id === (string) $owner->id;

        $hasShareToken = $project->share_token_hash
        && $request->filled('key')
        && hash_equals(
            $project->share_token_hash,
            hash('sha256', $request->query('key'))
        );

        $canAccess = match ($project->visibility) {
            'public'   => true,
            'unlisted' => $isOwner || $hasShareToken,
            'private'  => $isOwner,
            default    => false,
        };

        abort_unless($canAccess, 404);

        $path = $path ?: $project->entry_point;

        $decodedPath = rawurldecode($path);

        abort_if(
            str_contains($decodedPath, "\0") ||
            str_contains($decodedPath, '\\') ||
            str_starts_with($decodedPath, '/') ||
            collect(explode('/', $decodedPath))
                ->contains(fn($segment) =>
                    $segment === '..' || $segment === '.'
                ),
            404
        );

        $allowedExtensions = [
            'html', 'htm',
            'css', 'js', 'mjs',
            'json', 'xml', 'txt',
            'png', 'jpg', 'jpeg', 'gif',
            'svg', 'webp', 'ico',
            'woff', 'woff2', 'ttf', 'otf',
            'mp4', 'webm',
        ];

        $extension = strtolower(pathinfo($decodedPath, PATHINFO_EXTENSION));

        abort_unless(
            in_array($extension, $allowedExtensions, true),
            404
        );

        $filePath = sprintf(
            'projects/%s/%s/%s',
            $owner->id,
            $project->slug,
            $decodedPath
        );

        $disk = Storage::disk('local');

        abort_unless($disk->exists($filePath), 404);

        $stream = $disk->readStream($filePath);

        abort_if($stream === false, 404);

        $mimeType = $disk->mimeType($filePath)
            ?: 'application/octet-stream';

        return response()->stream(
            function () use ($stream) {
                try {
                    fpassthru($stream);
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            },
            200,
            [
                'Content-Type'           => $mimeType,
                'Content-Disposition'    => 'inline',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control'          => 'no-store',
            ]
        );
    }
}
