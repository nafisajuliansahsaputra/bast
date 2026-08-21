import { Link } from '@inertiajs/react';
import { ShieldCheck, UserRound } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const settingsNavItems: NavItem[] = [
    {
        title: 'Profil',
        href: editProfile(),
        icon: UserRound,
    },
    {
        title: 'Keamanan',
        href: editSecurity(),
        icon: ShieldCheck,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <div className="flex flex-1 flex-col px-5 py-6 md:px-8 md:py-8">
            <div className="mx-auto w-full max-w-[1200px]">
                <div>
                    <h1 className="text-[28px] font-semibold tracking-[-0.035em] text-[#17212B]">
                        Pengaturan Akun
                    </h1>

                    <p className="mt-1.5 text-sm text-[#71808C]">
                        Kelola informasi profil dan keamanan akun Anda.
                    </p>
                </div>

                <div className="mt-7 grid gap-5 lg:grid-cols-[220px_minmax(0,1fr)]">
                    <aside>
                        <nav
                            className="overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white p-2"
                            aria-label="Pengaturan akun"
                        >
                            {settingsNavItems.map((item) => {
                                const active = isCurrentOrParentUrl(item.href);

                                const Icon = item.icon;

                                return (
                                    <Link
                                        key={toUrl(item.href)}
                                        href={item.href}
                                        className={cn(
                                            'flex h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium transition-colors',
                                            active
                                                ? 'bg-[#EAF3FA] text-[#1D5D8F]'
                                                : 'text-[#657481] hover:bg-[#F5F7F9] hover:text-[#344250]',
                                        )}
                                    >
                                        {Icon && <Icon className="size-4" />}

                                        {item.title}
                                    </Link>
                                );
                            })}
                        </nav>
                    </aside>

                    <main className="min-w-0">{children}</main>
                </div>
            </div>
        </div>
    );
}
