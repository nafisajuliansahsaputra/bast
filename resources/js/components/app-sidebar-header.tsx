import { usePage } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { auth } = usePage().props;

    return (
        <header className="sticky top-0 z-20 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-[#E1E6EA] bg-white px-5 md:px-6">
            <div className="flex min-w-0 items-center gap-3">
                <SidebarTrigger className="-ml-1 text-[#5F6D78] hover:bg-[#F1F4F6] hover:text-[#17212B]" />

                <div className="h-5 w-px bg-[#E1E6EA]" />

                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            <div className="hidden items-center gap-3 sm:flex">
                <div className="flex items-center gap-2 rounded-full border border-[#DDE6ED] bg-[#F8FAFB] px-3 py-1.5">
                    <ShieldCheck
                        className="size-3.5 text-[#1D5D8F]"
                        strokeWidth={1.9}
                    />

                    <span className="text-[11px] font-medium text-[#657481]">
                        {auth.user?.role?.name ?? 'Pengguna'}
                    </span>
                </div>
            </div>
        </header>
    );
}
