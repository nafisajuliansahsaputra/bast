import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    Archive,
    CheckCircle2,
    ChevronRight,
    ClipboardList,
    FileClock,
    FilePlus2,
    FileText,
    Plus,
} from 'lucide-react';
import { dashboard } from '@/routes';

type DashboardStats = {
    total: number;
    draft: number;
    revision: number;
    completed: number;
    archived: number;
};

type StatusBreakdown = {
    key: string;
    label: string;
    count: number;
};

type MonthlyActivity = {
    month: string;
    count: number;
};

type RecentBast = {
    uuid: string;
    documentNumber: string | null;
    title: string;
    status: string;
    type: string | null;
    department: string | null;
    documentDate: string | null;
    updatedAt: string | null;
};

type RecentActivity = {
    id: number;
    action: string;
    description: string | null;
    userName: string | null;
    createdAt: string | null;
};

type Props = {
    stats: DashboardStats;
    statusBreakdown: StatusBreakdown[];
    monthlyActivity: MonthlyActivity[];
    recentBasts: RecentBast[];
    recentActivities: RecentActivity[];
};

const statusStyles: Record<
    string,
    {
        label: string;
        badge: string;
        bar: string;
    }
> = {
    draft: {
        label: 'Draft',
        badge: 'bg-[#F1F3F5] text-[#63717C]',
        bar: 'bg-[#87939D]',
    },

    revision: {
        label: 'Draft Revisi',
        badge: 'bg-[#FFF4DF] text-[#9A6718]',
        bar: 'bg-[#B77B20]',
    },

    finalized: {
        label: 'Finalized',
        badge: 'bg-[#EAF3FA] text-[#1D5D8F]',
        bar: 'bg-[#1D5D8F]',
    },

    completed: {
        label: 'Selesai',
        badge: 'bg-[#EAF6EF] text-[#287A4B]',
        bar: 'bg-[#3A8C5E]',
    },

    archived: {
        label: 'Diarsipkan',
        badge: 'bg-[#EDF0F3] text-[#53616D]',
        bar: 'bg-[#667784]',
    },

    cancelled: {
        label: 'Dibatalkan',
        badge: 'bg-[#FCECEC] text-[#B64040]',
        bar: 'bg-[#C65454]',
    },
};

const kpiItems = [
    {
        key: 'total' as const,
        title: 'Total BAST',
        description: 'Seluruh berita acara',
        icon: ClipboardList,
    },

    {
        key: 'draft' as const,
        title: 'Draft',
        description: 'Belum pernah difinalisasi',
        icon: FileClock,
    },

    {
        key: 'completed' as const,
        title: 'Selesai',
        description: 'Proses serah terima selesai',
        icon: CheckCircle2,
    },

    {
        key: 'archived' as const,
        title: 'Arsip',
        description: 'Dokumen telah diarsipkan',
        icon: Archive,
    },
];

function formatMonth(value: string): string {
    const [year, month] = value.split('-').map(Number);

    return new Intl.DateTimeFormat('id-ID', {
        month: 'short',
    })
        .format(new Date(year, month - 1, 1))
        .replace('.', '');
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${value}T00:00:00`));
}

function formatDateTime(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

export default function Dashboard({
    stats,
    statusBreakdown,
    monthlyActivity,
    recentBasts,
    recentActivities,
}: Props) {
    const { auth } = usePage().props;

    const firstName = auth.user?.name?.split(' ')[0] ?? 'Pengguna';

    const maxMonthlyValue = Math.max(
        ...monthlyActivity.map((item) => item.count),
        1,
    );

    const totalStatuses = Math.max(
        statusBreakdown.reduce((total, item) => total + item.count, 0),
        1,
    );

    return (
        <>
            <Head title="Dashboard" />

            <div className="flex flex-1 flex-col px-5 py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1600px]">
                    <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
                        <div>
                            <p className="text-sm font-medium text-[#1D5D8F]">
                                Selamat datang, {firstName}
                            </p>

                            <h1 className="mt-1 text-[28px] font-semibold tracking-[-0.035em] text-[#17212B]">
                                Dashboard
                            </h1>

                            <p className="mt-1.5 text-sm leading-6 text-[#71808C]">
                                Ringkasan aktivitas dan pengelolaan Berita Acara
                                Serah Terima.
                            </p>
                        </div>

                        <Link
                            href="/bast/create"
                            className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-[#1D5D8F] px-4 text-sm font-medium text-white transition-colors hover:bg-[#174C76]"
                        >
                            <Plus className="size-4" strokeWidth={2} />
                            Buat BAST
                        </Link>
                    </div>

                    <div className="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        {kpiItems.map((item) => {
                            const Icon = item.icon;

                            return (
                                <div
                                    key={item.key}
                                    className="rounded-[10px] border border-[#DDE3E8] bg-white p-5"
                                >
                                    <div className="flex items-start justify-between gap-4">
                                        <div>
                                            <p className="text-[13px] font-medium text-[#657481]">
                                                {item.title}
                                            </p>

                                            <p className="mt-3 text-[30px] leading-none font-semibold tracking-[-0.04em] text-[#17212B]">
                                                {stats[item.key]}
                                            </p>
                                        </div>

                                        <div className="flex size-10 items-center justify-center rounded-[9px] bg-[#EAF3FA] text-[#1D5D8F]">
                                            <Icon
                                                className="size-5"
                                                strokeWidth={1.8}
                                            />
                                        </div>
                                    </div>

                                    <p className="mt-4 text-xs text-[#8A96A0]">
                                        {item.description}
                                    </p>
                                </div>
                            );
                        })}
                    </div>

                    <div className="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.75fr)]">
                        <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5 md:p-6">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <h2 className="text-[15px] font-semibold text-[#25313C]">
                                        Aktivitas BAST
                                    </h2>

                                    <p className="mt-1 text-xs text-[#87949F]">
                                        Dokumen yang dibuat dalam 6 bulan
                                        terakhir.
                                    </p>
                                </div>

                                <div className="flex size-9 items-center justify-center rounded-lg bg-[#F2F6F8] text-[#657581]">
                                    <Activity
                                        className="size-[18px]"
                                        strokeWidth={1.8}
                                    />
                                </div>
                            </div>

                            <div className="mt-8 flex h-[220px] items-end gap-3 sm:gap-5">
                                {monthlyActivity.map((item) => {
                                    const percentage =
                                        (item.count / maxMonthlyValue) * 100;

                                    return (
                                        <div
                                            key={item.month}
                                            className="flex h-full flex-1 flex-col items-center justify-end"
                                        >
                                            <span className="mb-2 text-[11px] font-medium text-[#657481]">
                                                {item.count}
                                            </span>

                                            <div className="flex h-[160px] w-full max-w-[54px] items-end rounded-t-md bg-[#F0F4F7]">
                                                <div
                                                    className="w-full rounded-t-md bg-[#1D5D8F] transition-[height]"
                                                    style={{
                                                        height:
                                                            item.count === 0
                                                                ? '3px'
                                                                : `${Math.max(
                                                                      percentage,
                                                                      8,
                                                                  )}%`,
                                                    }}
                                                />
                                            </div>

                                            <span className="mt-3 text-[11px] text-[#87949F]">
                                                {formatMonth(item.month)}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </section>

                        <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5 md:p-6">
                            <div>
                                <h2 className="text-[15px] font-semibold text-[#25313C]">
                                    Status Dokumen
                                </h2>

                                <p className="mt-1 text-xs text-[#87949F]">
                                    Distribusi status BAST saat ini.
                                </p>
                            </div>

                            <div className="mt-6 space-y-4">
                                {statusBreakdown.map((item) => {
                                    const style =
                                        statusStyles[item.key] ??
                                        statusStyles.draft;

                                    const percentage =
                                        (item.count / totalStatuses) * 100;

                                    return (
                                        <div key={item.key}>
                                            <div className="mb-2 flex items-center justify-between gap-4">
                                                <span className="text-[13px] font-medium text-[#52616D]">
                                                    {item.label}
                                                </span>

                                                <span className="text-[13px] font-semibold text-[#25313C]">
                                                    {item.count}
                                                </span>
                                            </div>

                                            <div className="h-1.5 overflow-hidden rounded-full bg-[#EEF2F4]">
                                                <div
                                                    className={`h-full rounded-full ${style.bar}`}
                                                    style={{
                                                        width: `${percentage}%`,
                                                    }}
                                                />
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </section>
                    </div>

                    <div className="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_minmax(340px,0.65fr)]">
                        <section className="overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                            <div className="flex items-center justify-between gap-4 border-b border-[#E7EBEE] px-5 py-4 md:px-6">
                                <div>
                                    <h2 className="text-[15px] font-semibold text-[#25313C]">
                                        Berita Acara Terbaru
                                    </h2>

                                    <p className="mt-1 text-xs text-[#87949F]">
                                        Dokumen aktif yang terakhir diperbarui.
                                    </p>
                                </div>

                                <Link
                                    href="/bast"
                                    className="flex items-center gap-1 text-xs font-medium text-[#1D5D8F] hover:text-[#174C76]"
                                >
                                    Lihat semua
                                    <ChevronRight className="size-3.5" />
                                </Link>
                            </div>

                            {recentBasts.length === 0 ? (
                                <div className="flex min-h-[250px] items-center justify-center px-6 py-10">
                                    <div className="max-w-sm text-center">
                                        <div className="mx-auto flex size-11 items-center justify-center rounded-[10px] bg-[#F1F5F7] text-[#7D8B96]">
                                            <FileText
                                                className="size-5"
                                                strokeWidth={1.8}
                                            />
                                        </div>

                                        <p className="mt-4 text-sm font-medium text-[#3C4A56]">
                                            Belum ada berita acara
                                        </p>

                                        <p className="mt-1 text-xs leading-5 text-[#8A96A0]">
                                            Dokumen yang dibuat akan muncul di
                                            bagian ini.
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full min-w-[720px]">
                                        <thead>
                                            <tr className="border-b border-[#E7EBEE] bg-[#FAFBFC]">
                                                <th className="px-6 py-3 text-left text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Dokumen
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Jenis
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Tanggal
                                                </th>

                                                <th className="px-6 py-3 text-right text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {recentBasts.map((bast) => {
                                                const style =
                                                    statusStyles[bast.status] ??
                                                    statusStyles.draft;

                                                return (
                                                    <tr
                                                        key={bast.uuid}
                                                        className="border-b border-[#EDF0F2] last:border-b-0"
                                                    >
                                                        <td className="px-6 py-4">
                                                            <p className="max-w-[310px] truncate text-[13px] font-medium text-[#344250]">
                                                                {bast.title}
                                                            </p>

                                                            <p className="mt-1 font-mono text-[10px] text-[#8B97A1]">
                                                                {bast.documentNumber ??
                                                                    'Belum bernomor'}
                                                            </p>
                                                        </td>

                                                        <td className="px-4 py-4 text-[12px] text-[#64727D]">
                                                            {bast.type ?? '—'}
                                                        </td>

                                                        <td className="px-4 py-4 text-[12px] text-[#64727D]">
                                                            {formatDate(
                                                                bast.documentDate,
                                                            )}
                                                        </td>

                                                        <td className="px-6 py-4 text-right">
                                                            <span
                                                                className={`inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium ${style.badge}`}
                                                            >
                                                                {style.label}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </section>

                        <section className="rounded-[10px] border border-[#DDE3E8] bg-white">
                            <div className="border-b border-[#E7EBEE] px-5 py-4">
                                <h2 className="text-[15px] font-semibold text-[#25313C]">
                                    Aktivitas Terbaru
                                </h2>

                                <p className="mt-1 text-xs text-[#87949F]">
                                    Riwayat aktivitas sistem terbaru.
                                </p>
                            </div>

                            {recentActivities.length === 0 ? (
                                <div className="flex min-h-[250px] items-center justify-center px-6 py-10">
                                    <div className="max-w-xs text-center">
                                        <div className="mx-auto flex size-11 items-center justify-center rounded-[10px] bg-[#F1F5F7] text-[#7D8B96]">
                                            <FilePlus2
                                                className="size-5"
                                                strokeWidth={1.8}
                                            />
                                        </div>

                                        <p className="mt-4 text-sm font-medium text-[#3C4A56]">
                                            Belum ada aktivitas
                                        </p>

                                        <p className="mt-1 text-xs leading-5 text-[#8A96A0]">
                                            Aktivitas pengguna akan tercatat di
                                            sini.
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <div className="divide-y divide-[#EDF0F2]">
                                    {recentActivities.map((activity) => (
                                        <div
                                            key={activity.id}
                                            className="flex gap-3 px-5 py-4"
                                        >
                                            <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-[#EEF4F8] text-[#1D5D8F]">
                                                <FilePlus2
                                                    className="size-4"
                                                    strokeWidth={1.8}
                                                />
                                            </div>

                                            <div className="min-w-0 flex-1">
                                                <p className="text-[12px] leading-5 text-[#4C5A65]">
                                                    {activity.description ??
                                                        activity.action}
                                                </p>

                                                <div className="mt-1 flex flex-wrap items-center gap-x-2 text-[10px] text-[#939EA6]">
                                                    <span>
                                                        {activity.userName ??
                                                            'Sistem'}
                                                    </span>

                                                    <span>•</span>

                                                    <span>
                                                        {formatDateTime(
                                                            activity.createdAt,
                                                        )}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </section>
                    </div>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
