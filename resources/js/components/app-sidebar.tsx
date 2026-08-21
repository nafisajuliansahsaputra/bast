import { Link, usePage } from '@inertiajs/react';
import {
    Archive,
    Database,
    FileText,
    LayoutDashboard,
    ScrollText,
    UsersRound,
    X,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const primaryNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutDashboard,
    },
    {
        title: 'Berita Acara',
        href: '/bast',
        icon: FileText,
    },
    {
        title: 'Arsip',
        href: '/archive',
        icon: Archive,
    },
];

export function AppSidebar() {
    const { auth } = usePage().props;

    const { isMobile, setOpenMobile } = useSidebar();

    const roleSlug = auth.user?.role?.slug;

    const canManage = roleSlug === 'super-admin' || roleSlug === 'admin';

    const managementNavItems: NavItem[] = [];

    if (canManage) {
        managementNavItems.push({
            title: 'Data Master',
            href: '/master',
            icon: Database,
        });
    }

    if (roleSlug === 'super-admin') {
        managementNavItems.push({
            title: 'Pengguna',
            href: '/users',
            icon: UsersRound,
        });
    }

    if (canManage) {
        managementNavItems.push({
            title: 'Activity Log',
            href: '/activity-logs',
            icon: ScrollText,
        });
    }

    const closeMobileSidebar = () => {
        if (isMobile) {
            setOpenMobile(false);
        }
    };

    return (
        <Sidebar
            collapsible="icon"
            variant="sidebar"
            className="border-r border-[#234C68] bg-[#123C5E] text-white [&_[data-sidebar=sidebar]]:bg-[#123C5E]"
        >
            <SidebarHeader className="relative border-b border-white/[0.07] bg-[#123C5E] p-3">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            className="h-12 rounded-lg hover:bg-white/[0.06] max-md:pr-12"
                        >
                            <Link
                                href={dashboard()}
                                prefetch
                                onClick={closeMobileSidebar}
                            >
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>

                <button
                    type="button"
                    onClick={() => setOpenMobile(false)}
                    className="absolute top-1/2 right-3 flex size-8 -translate-y-1/2 items-center justify-center rounded-lg text-white/60 transition-colors hover:bg-white/[0.08] hover:text-white md:hidden"
                    aria-label="Tutup menu"
                >
                    <X className="size-4.5" />
                </button>
            </SidebarHeader>

            <SidebarContent className="bg-[#123C5E] py-3">
                <NavMain items={primaryNavItems} label="Menu Utama" />

                {managementNavItems.length > 0 && (
                    <NavMain items={managementNavItems} label="Manajemen" />
                )}
            </SidebarContent>

            <SidebarFooter className="border-t border-white/[0.07] bg-[#123C5E] p-3">
                <div className="px-2 pt-1 pb-2 group-data-[collapsible=icon]:hidden">
                    <p className="text-[10px] font-medium tracking-wide text-white/55">
                        INDEPENDENT RECONSTRUCTION
                    </p>

                    <p className="mt-1 text-[10px] leading-4 text-white/35">
                        Designed &amp; developed by NATSX
                    </p>
                </div>

                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
