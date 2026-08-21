import { Check } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

export type BastFormStep = {
    title: string;
    icon: LucideIcon;
};

type Props = {
    steps: BastFormStep[];
    currentStep: number;
    onStepChange: (step: number) => void;
};

export function BastFormStepper({ steps, currentStep, onStepChange }: Props) {
    const current = steps[currentStep];

    return (
        <div className="mt-7 rounded-[10px] border border-[#DDE3E8] bg-white p-4">
            <div className="md:hidden">
                <div className="flex items-start justify-between gap-4">
                    <div className="min-w-0">
                        <p className="text-[10px] font-medium tracking-[0.06em] text-[#8A96A0] uppercase">
                            Langkah {currentStep + 1} dari {steps.length}
                        </p>

                        <p className="mt-1 truncate text-sm font-semibold text-[#344250]">
                            {current?.title}
                        </p>
                    </div>

                    {current && (
                        <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#EAF3FA] text-[#1D5D8F]">
                            <current.icon
                                className="size-[18px]"
                                strokeWidth={1.8}
                            />
                        </div>
                    )}
                </div>

                <div className="mt-4 flex items-center">
                    {steps.map((item, index) => {
                        const Icon = item.icon;

                        const completed = index < currentStep;
                        const active = index === currentStep;

                        return (
                            <div
                                key={item.title}
                                className="flex min-w-0 flex-1 items-center last:flex-none"
                            >
                                <button
                                    type="button"
                                    onClick={() => onStepChange(index)}
                                    aria-label={`Buka langkah ${index + 1}: ${item.title}`}
                                    aria-current={active ? 'step' : undefined}
                                    className={`flex size-8 shrink-0 items-center justify-center rounded-full transition-colors ${
                                        completed
                                            ? 'bg-[#EAF6EF] text-[#287A4B]'
                                            : active
                                              ? 'bg-[#1D5D8F] text-white'
                                              : 'bg-[#F1F4F6] text-[#85919B]'
                                    }`}
                                >
                                    {completed ? (
                                        <Check
                                            className="size-4"
                                            strokeWidth={2}
                                        />
                                    ) : (
                                        <Icon
                                            className="size-4"
                                            strokeWidth={1.8}
                                        />
                                    )}
                                </button>

                                {index < steps.length - 1 && (
                                    <div
                                        className={`mx-2 h-px min-w-2 flex-1 ${
                                            index < currentStep
                                                ? 'bg-[#BFDCCB]'
                                                : 'bg-[#E1E6EA]'
                                        }`}
                                    />
                                )}
                            </div>
                        );
                    })}
                </div>
            </div>

            <div className="hidden md:block">
                <div className="flex items-center">
                    {steps.map((item, index) => {
                        const Icon = item.icon;

                        const completed = index < currentStep;
                        const active = index === currentStep;

                        return (
                            <div
                                key={item.title}
                                className="flex min-w-0 flex-1 items-center"
                            >
                                <button
                                    type="button"
                                    onClick={() => onStepChange(index)}
                                    aria-current={active ? 'step' : undefined}
                                    className="flex shrink-0 items-center gap-2"
                                >
                                    <span
                                        className={`flex size-8 shrink-0 items-center justify-center rounded-full ${
                                            completed
                                                ? 'bg-[#EAF6EF] text-[#287A4B]'
                                                : active
                                                  ? 'bg-[#1D5D8F] text-white'
                                                  : 'bg-[#F1F4F6] text-[#85919B]'
                                        }`}
                                    >
                                        {completed ? (
                                            <Check
                                                className="size-4"
                                                strokeWidth={2}
                                            />
                                        ) : (
                                            <Icon
                                                className="size-4"
                                                strokeWidth={1.8}
                                            />
                                        )}
                                    </span>

                                    <span
                                        className={`text-xs font-medium whitespace-nowrap ${
                                            active
                                                ? 'text-[#1D5D8F]'
                                                : 'text-[#71808C]'
                                        }`}
                                    >
                                        {item.title}
                                    </span>
                                </button>

                                {index < steps.length - 1 && (
                                    <div
                                        className={`mx-3 h-px min-w-3 flex-1 ${
                                            index < currentStep
                                                ? 'bg-[#BFDCCB]'
                                                : 'bg-[#E1E6EA]'
                                        }`}
                                    />
                                )}
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
