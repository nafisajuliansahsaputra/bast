import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export function NavMain({
    items = [],
    label,
}: {
    items: NavItem[];
    label: string;
}) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="px-3 py-2">
            <SidebarGroupLabel className="mb-1 px-2 text-[10px] font-medium tracking-[0.08em] text-white/35 uppercase">
                {label}
            </SidebarGroupLabel>

            <SidebarMenu className="gap-1">
                {items.map((item) => {
                    const isActive = isCurrentOrParentUrl(item.href);

                    return (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isActive}
                                tooltip={{ children: item.title }}
                                className="h-10 rounded-lg px-3 text-[13px] font-medium text-white/65 transition-colors hover:bg-white/[0.07] hover:text-white data-[active=true]:bg-white/[0.1] data-[active=true]:text-white"
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon && (
                                        <item.icon
                                            className="size-[18px]"
                                            strokeWidth={1.8}
                                        />
                                    )}

                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
