<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelBastRequest;
use App\Models\Bast;
use App\Models\User;
use App\Services\BastLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BastLifecycleController extends Controller
{
    public function reopen(
        Request $request,
        Bast $bast,
        BastLifecycleService $service,
    ): RedirectResponse {
        Gate::authorize(
            'reopen',
            $bast,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $bast = $service->reopen(
            $user,
            $bast,
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route(
                'bast.show',
                $bast,
            )
            ->with(
                'success',
                'BAST berhasil dibuka kembali untuk direvisi.',
            );
    }

    public function cancel(
        CancelBastRequest $request,
        Bast $bast,
        BastLifecycleService $service,
    ): RedirectResponse {
        Gate::authorize(
            'cancel',
            $bast,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $validated = $request->validated();

        $bast = $service->cancel(
            $user,
            $bast,
            (string) $validated['cancellation_reason'],
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route(
                'bast.show',
                $bast,
            )
            ->with(
                'success',
                'BAST berhasil dibatalkan.',
            );
    }

    public function complete(
        Request $request,
        Bast $bast,
        BastLifecycleService $service,
    ): RedirectResponse {
        Gate::authorize(
            'complete',
            $bast,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $bast = $service->complete(
            $user,
            $bast,
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route(
                'bast.show',
                $bast,
            )
            ->with(
                'success',
                'BAST berhasil ditandai selesai.',
            );
    }

    public function archive(
        Request $request,
        Bast $bast,
        BastLifecycleService $service,
    ): RedirectResponse {
        Gate::authorize(
            'archive',
            $bast,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $bast = $service->archive(
            $user,
            $bast,
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route(
                'bast.show',
                $bast,
            )
            ->with(
                'success',
                'BAST berhasil diarsipkan.',
            );
    }

    public function restore(
        Request $request,
        Bast $bast,
        BastLifecycleService $service,
    ): RedirectResponse {
        Gate::authorize(
            'restoreArchive',
            $bast,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $bast = $service->restoreArchive(
            $user,
            $bast,
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route(
                'bast.show',
                $bast,
            )
            ->with(
                'success',
                'BAST berhasil dipulihkan dari arsip.',
            );
    }
}
