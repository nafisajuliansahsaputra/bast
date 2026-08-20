<?php

use App\Models\Bast;
use App\Models\BastAttachment;
use App\Models\BastType;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createAttachmentRole(): Role
{
    return Role::query()->create([
        'name' => 'Staff Attachment',
        'slug' => 'staff',
        'description' => null,
        'is_active' => true,
    ]);
}

function createAttachmentDepartment(): Department
{
    return Department::query()->create([
        'name' => 'Attachment Department',
        'code' => 'ATT',
        'description' => null,
        'is_active' => true,
    ]);
}

function createAttachmentType(): BastType
{
    return BastType::query()->create([
        'name' => 'Attachment Type',
        'slug' => 'attachment-type',
        'description' => null,
        'is_active' => true,
    ]);
}

function createAttachmentBast(
    User $user,
    Department $department,
    BastType $type,
): Bast {
    return Bast::query()->create([
        'document_number' => null,
        'sequence_number' => null,
        'document_code' => 'BAST',

        'document_month' => 8,
        'document_year' => 2026,

        'bast_type_id' => $type->id,
        'department_id' => $department->id,
        'created_by' => $user->id,

        'title' => 'Attachment Test BAST',
        'description' => null,

        'document_date' => '2026-08-20',
        'handover_date' => '2026-08-20',
        'handover_place' => 'Cianjur',

        'status' => Bast::STATUS_DRAFT,
    ]);
}

beforeEach(function () {
    Storage::fake('local');

    $this->attachmentRole =
        createAttachmentRole();

    $this->attachmentDepartment =
        createAttachmentDepartment();

    $this->attachmentType =
        createAttachmentType();

    $this->attachmentUser =
        User::factory()->create([
            'role_id' => $this
                ->attachmentRole
                ->id,

            'department_id' => $this
                ->attachmentDepartment
                ->id,
        ]);

    $this->attachmentBast =
        createAttachmentBast(
            $this->attachmentUser,
            $this->attachmentDepartment,
            $this->attachmentType,
        );
});

test('owner can upload attachment to draft bast', function () {
    $file = UploadedFile::fake()
        ->create(
            'surat-pendukung.pdf',
            250,
            'application/pdf',
        );

    $this->actingAs(
        $this->attachmentUser,
    )
        ->post(
            route(
                'bast.attachments.store',
                $this->attachmentBast,
            ),
            [
                'file' => $file,

                'category' => 'supporting_document',

                'description' => 'Dokumen pendukung pengujian.',
            ],
        )
        ->assertRedirect();

    $attachment = BastAttachment::query()
        ->firstOrFail();

    expect($attachment->original_name)
        ->toBe('surat-pendukung.pdf')
        ->and($attachment->uploaded_by)
        ->toBe($this->attachmentUser->id)
        ->and($attachment->category)
        ->toBe('supporting_document');

    Storage::disk('local')
        ->assertExists(
            $attachment->path,
        );

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,
            'subject_id' => $this
                ->attachmentBast
                ->id,

            'action' => 'ATTACHMENT_UPLOADED',
        ],
    );
});

test('owner can download attachment', function () {
    $path = sprintf(
        'bast/%s/test.pdf',
        $this->attachmentBast->uuid,
    );

    Storage::disk('local')->put(
        $path,
        'test attachment',
    );

    $attachment = $this
        ->attachmentBast
        ->attachments()
        ->create([
            'uploaded_by' => $this
                ->attachmentUser
                ->id,

            'category' => 'supporting_document',

            'original_name' => 'test.pdf',

            'stored_name' => 'test.pdf',

            'disk' => 'local',

            'path' => $path,

            'mime_type' => 'application/pdf',

            'file_size' => 15,
        ]);

    $this->actingAs(
        $this->attachmentUser,
    )
        ->get(
            route(
                'bast.attachments.download',
                [
                    $this->attachmentBast,
                    $attachment,
                ],
            ),
        )
        ->assertOk();
});

test('owner can delete attachment from draft bast', function () {
    $path = sprintf(
        'bast/%s/delete-me.pdf',
        $this->attachmentBast->uuid,
    );

    Storage::disk('local')->put(
        $path,
        'delete me',
    );

    $attachment = $this
        ->attachmentBast
        ->attachments()
        ->create([
            'uploaded_by' => $this
                ->attachmentUser
                ->id,

            'category' => 'other',

            'original_name' => 'delete-me.pdf',

            'stored_name' => 'delete-me.pdf',

            'disk' => 'local',

            'path' => $path,

            'mime_type' => 'application/pdf',

            'file_size' => 9,
        ]);

    $this->actingAs(
        $this->attachmentUser,
    )
        ->delete(
            route(
                'bast.attachments.destroy',
                [
                    $this->attachmentBast,
                    $attachment,
                ],
            ),
        )
        ->assertRedirect();

    $this->assertDatabaseMissing(
        'bast_attachments',
        [
            'id' => $attachment->id,
        ],
    );

    Storage::disk('local')
        ->assertMissing($path);

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,
            'subject_id' => $this
                ->attachmentBast
                ->id,

            'action' => 'ATTACHMENT_DELETED',
        ],
    );
});

test('finalized bast cannot receive new attachments', function () {
    $this->attachmentBast->update([
        'status' => Bast::STATUS_FINALIZED,

        'document_number' => '001/BAST/DISKOMINFO/VIII/2026',

        'sequence_number' => 1,

        'finalized_at' => now(),

        'finalized_by' => $this
            ->attachmentUser
            ->id,
    ]);

    $file = UploadedFile::fake()
        ->create(
            'locked.pdf',
            100,
            'application/pdf',
        );

    $this->actingAs(
        $this->attachmentUser,
    )
        ->post(
            route(
                'bast.attachments.store',
                $this->attachmentBast,
            ),
            [
                'file' => $file,
                'category' => 'other',
            ],
        )
        ->assertForbidden();

    expect(
        BastAttachment::query()->count(),
    )->toBe(0);
});

test('invalid attachment format is rejected', function () {
    $file = UploadedFile::fake()
        ->create(
            'script.exe',
            100,
            'application/octet-stream',
        );

    $this->actingAs(
        $this->attachmentUser,
    )
        ->post(
            route(
                'bast.attachments.store',
                $this->attachmentBast,
            ),
            [
                'file' => $file,
                'category' => 'other',
            ],
        )
        ->assertSessionHasErrors(
            'file',
        );

    expect(
        BastAttachment::query()->count(),
    )->toBe(0);
});
