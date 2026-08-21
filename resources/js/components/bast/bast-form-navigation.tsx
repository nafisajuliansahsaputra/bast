import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    currentStep: number;
    totalSteps: number;
    cancelHref: string;
    processing: boolean;
    onPrevious: () => void;
    onNext: () => void;
    onSubmit: () => void;
    submitLabel: string;
    processingLabel?: string;
};

export function BastFormNavigation({
    currentStep,
    totalSteps,
    cancelHref,
    processing,
    onPrevious,
    onNext,
    onSubmit,
    submitLabel,
    processingLabel = 'Menyimpan...',
}: Props) {
    const isFirstStep = currentStep === 0;
    const isLastStep = currentStep === totalSteps - 1;

    const primaryAction = isLastStep ? (
        <Button
            type="button"
            disabled={processing}
            onClick={onSubmit}
            className="h-10 w-full bg-[#1D5D8F] text-white shadow-none hover:bg-[#174C76] md:w-auto"
        >
            {processing ? processingLabel : submitLabel}
        </Button>
    ) : (
        <Button
            type="button"
            onClick={onNext}
            className="h-10 w-full bg-[#1D5D8F] text-white shadow-none hover:bg-[#174C76] md:w-auto"
        >
            Selanjutnya
            <ChevronRight className="size-4" />
        </Button>
    );

    return (
        <div className="border-t border-[#E5E9EC] px-5 py-4 md:px-7">
            <div className="md:hidden">
                {isFirstStep ? (
                    <div>
                        {primaryAction}

                        <Link
                            href={cancelHref}
                            className="mt-2 flex h-9 w-full items-center justify-center rounded-lg text-sm font-medium text-[#657481] transition-colors hover:bg-[#F3F5F7]"
                        >
                            Batal
                        </Link>
                    </div>
                ) : (
                    <div className="grid grid-cols-2 gap-3">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onPrevious}
                            className="h-10 w-full border-[#D7DEE4] bg-white px-3 text-[#52616D] shadow-none hover:bg-[#F5F7F9] hover:text-[#344250]"
                        >
                            <ChevronLeft className="size-4" />
                            Sebelumnya
                        </Button>

                        {primaryAction}
                    </div>
                )}
            </div>

            <div className="hidden items-center justify-between gap-3 md:flex">
                <div>
                    {isFirstStep ? (
                        <Link
                            href={cancelHref}
                            className="inline-flex h-9 items-center rounded-lg px-3 text-sm font-medium text-[#657481] transition-colors hover:bg-[#F3F5F7]"
                        >
                            Batal
                        </Link>
                    ) : (
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onPrevious}
                            className="border-[#D7DEE4] bg-white text-[#52616D] shadow-none hover:bg-[#F5F7F9] hover:text-[#344250]"
                        >
                            <ChevronLeft className="size-4" />
                            Sebelumnya
                        </Button>
                    )}
                </div>

                {primaryAction}
            </div>
        </div>
    );
}
