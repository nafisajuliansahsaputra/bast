<?php

namespace App\Http\Controllers;

use App\Models\Bast;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class BastDocumentController extends Controller
{
    public function preview(
        Bast $bast,
    ): View {
        Gate::authorize(
            'previewDocument',
            $bast,
        );

        $this->loadDocumentRelations(
            $bast,
        );

        return view(
            'bast.document',
            [
                'bast' => $bast,

                'isPdf' => false,
            ],
        );
    }

    public function pdf(
        Request $request,
        Bast $bast,
    ): Response {
        Gate::authorize(
            'downloadPdf',
            $bast,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $this->loadDocumentRelations(
            $bast,
        );

        $bast->activityLogs()->create([
            'user_id' => $user->id,

            'action' => 'PDF_GENERATED',

            'description' => sprintf(
                'Menghasilkan PDF BAST "%s".',
                $bast->title,
            ),

            'ip_address' => $request->ip(),

            'user_agent' => $request
                ->userAgent(),

            'old_values' => null,

            'new_values' => [
                'document_number' => $bast
                    ->document_number,
            ],
        ]);

        $fileNumber = str_replace(
            [
                '/',
                '\\',
            ],
            '-',
            $bast->document_number
                ?? $bast->uuid,
        );

        return Pdf::loadView(
            'bast.document',
            [
                'bast' => $bast,

                'isPdf' => true,
            ],
        )
            ->setPaper(
                'a4',
                'portrait',
            )
            ->download(
                "BAST-{$fileNumber}.pdf",
            );
    }

    private function loadDocumentRelations(
        Bast $bast,
    ): void {
        $bast->load([
            'bastType:id,name',

            'department:id,name,code',

            'creator:id,name,nip,position',

            'finalizedBy:id,name,nip,position',

            'completedBy:id,name',

            'archivedBy:id,name',

            'cancelledBy:id,name',

            'parties' => fn ($query) => $query
                ->orderBy(
                    'sort_order',
                ),

            'items' => fn ($query) => $query
                ->with([
                    'itemCategory:id,name',

                    'unit:id,name,symbol',
                ])
                ->orderBy(
                    'sort_order',
                ),

            'attachments:id,bast_id,original_name,category',
        ]);
    }
}
