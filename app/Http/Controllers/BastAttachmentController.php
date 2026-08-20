<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBastAttachmentRequest;
use App\Models\Bast;
use App\Models\BastAttachment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BastAttachmentController extends Controller
{
    public function store(
        StoreBastAttachmentRequest $request,
        Bast $bast,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $file = $request->file('file');

        abort_unless(
            $file instanceof UploadedFile,
            422,
        );

        $validated = $request->validated();

        $disk = 'local';

        $extension = strtolower(
            $file->getClientOriginalExtension(),
        );

        $storedName = Str::uuid()->toString()
            .($extension !== ''
                ? ".{$extension}"
                : '');

        $directory = "bast/{$bast->uuid}";

        $path = $file->storeAs(
            $directory,
            $storedName,
            $disk,
        );

        if (! is_string($path)) {
            throw new RuntimeException(
                'Lampiran gagal disimpan.',
            );
        }

        try {
            DB::transaction(function () use (
                $request,
                $user,
                $bast,
                $file,
                $validated,
                $disk,
                $storedName,
                $path,
            ): void {
                $fileSize = $file->getSize();

                $category = isset(
                    $validated['category'],
                ) && is_string(
                    $validated['category'],
                )
                    ? $validated['category']
                    : 'other';

                $description = isset(
                    $validated['description'],
                ) && is_string(
                    $validated['description'],
                )
                    ? $validated['description']
                    : null;

                $attachment = $bast
                    ->attachments()
                    ->create([
                        'uploaded_by' => $user->id,

                        'category' => $category,

                        'original_name' => $file
                            ->getClientOriginalName(),

                        'stored_name' => $storedName,

                        'disk' => $disk,

                        'path' => $path,

                        'mime_type' => $file
                            ->getMimeType(),

                        'file_size' => is_int(
                            $fileSize,
                        )
                            ? $fileSize
                            : null,

                        'description' => $description,
                    ]);

                $bast->activityLogs()->create([
                    'user_id' => $user->id,

                    'action' => 'ATTACHMENT_UPLOADED',

                    'description' => sprintf(
                        'Mengunggah lampiran "%s" pada BAST "%s".',
                        $attachment->original_name,
                        $bast->title,
                    ),

                    'ip_address' => $request->ip(),

                    'user_agent' => $request
                        ->userAgent(),

                    'old_values' => null,

                    'new_values' => [
                        'attachment_id' => $attachment->id,
                        'original_name' => $attachment->original_name,
                        'category' => $attachment->category,
                    ],
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)
                ->delete($path);

            throw $exception;
        }

        return back()->with(
            'success',
            'Lampiran berhasil diunggah.',
        );
    }

    public function download(
        Request $request,
        Bast $bast,
        BastAttachment $attachment,
    ): StreamedResponse {
        Gate::authorize(
            'view',
            $bast,
        );

        $this->ensureAttachmentBelongsToBast(
            $bast,
            $attachment,
        );

        abort_unless(
            Storage::disk($attachment->disk)
                ->exists($attachment->path),
            404,
            'File lampiran tidak ditemukan.',
        );

        return Storage::disk(
            $attachment->disk,
        )->download(
            $attachment->path,
            $attachment->original_name,
        );
    }

    public function destroy(
        Request $request,
        Bast $bast,
        BastAttachment $attachment,
    ): RedirectResponse {
        Gate::authorize(
            'manageAttachments',
            $bast,
        );

        $this->ensureAttachmentBelongsToBast(
            $bast,
            $attachment,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $disk = $attachment->disk;
        $path = $attachment->path;
        $attachmentId = $attachment->id;
        $originalName = $attachment
            ->original_name;

        DB::transaction(function () use (
            $request,
            $user,
            $bast,
            $attachment,
            $attachmentId,
            $originalName,
        ): void {
            $bast->activityLogs()->create([
                'user_id' => $user->id,

                'action' => 'ATTACHMENT_DELETED',

                'description' => sprintf(
                    'Menghapus lampiran "%s" dari BAST "%s".',
                    $originalName,
                    $bast->title,
                ),

                'ip_address' => $request->ip(),

                'user_agent' => $request
                    ->userAgent(),

                'old_values' => [
                    'attachment_id' => $attachmentId,
                    'original_name' => $originalName,
                ],

                'new_values' => null,
            ]);

            $attachment->delete();
        });

        Storage::disk($disk)
            ->delete($path);

        return back()->with(
            'success',
            'Lampiran berhasil dihapus.',
        );
    }

    private function ensureAttachmentBelongsToBast(
        Bast $bast,
        BastAttachment $attachment,
    ): void {
        abort_unless(
            $attachment->bast_id === $bast->id,
            404,
        );
    }
}
