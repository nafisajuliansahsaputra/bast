import { Link, usePage } from '@inertiajs/react';
import {
    Archive,
    Database,
    FileText,
    LayoutDashboard,
    ScrollText,
    UsersRound,
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

    return (
        <Sidebar
            collapsible="icon"
            variant="sidebar"
            className="border-r border-[#234C68] bg-[#123C5E] text-white [&_[data-sidebar=sidebar]]:bg-[#123C5E]"
        >
            <SidebarHeader className="border-b border-white/[0.07] bg-[#123C5E] p-3">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            className="h-12 rounded-lg hover:bg-white/[0.06]"
                        >
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="bg-[#123C5E] py-3">
                <NavMain items={primaryNavItems} label="Menu Utama" />

                {managementNavItems.length > 0 && (
                    <NavMain items={managementNavItems} label="Manajemen" />
                )}
            </SidebarContent>

            <SidebarFooter className="border-t border-white/[0.07] bg-[#123C5E] p-3">
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
