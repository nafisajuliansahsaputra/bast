import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-9 items-center justify-center rounded-[9px] bg-white/10 text-white ring-1 ring-white/10">
                <AppLogoIcon className="size-[21px]" />
            </div>

            <div className="ml-1 grid flex-1 text-left leading-tight">
                <span className="truncate text-sm font-semibold tracking-[-0.01em] text-white">
                    BAST
                </span>

                <span className="truncate text-[10px] text-white/50">
                    Internal Management System
                </span>
            </div>
        </>
    );
}
