import { Head, Link, router } from '@inertiajs/react';
import { Activity, Eye, Search } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import {
    index as activityLogsIndex,
    show as showActivityLog,
} from '@/actions/App/Http/Controllers/ActivityLogController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type ActivityUser = {
    id: number;
    name: string;
    email: string;
};

type Subject = {
    type: string;
    label: string;
    href: string | null;
};

type Category = {
    key: string;
    label: string;
};

type ActivityLog = {
    id: number;
    action: string;
    action_label: string;
    description: string | null;
    category: Category;
    user: ActivityUser | null;
    subject: Subject;
    ip_address: string | null;
    created_at: string;
};

type PaginatedActivityLogs = {
    data: ActivityLog[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

type Filters = {
    search: string;
    category: string;
    user: string;
    date_from: string;
    date_to: string;
};

type Props = {
    activityLogs: PaginatedActivityLogs;
    filters: Filters;
    users: ActivityUser[];
};

const categoryOptions = [
    {
        value: '',
        label: 'Semua aktivitas',
    },
    {
        value: 'bast',
        label: 'BAST',
    },
    {
        value: 'users',
        label: 'Pengguna',
    },
    {
        value: 'master',
        label: 'Data Master',
    },
    {
        value: 'attachments',
        label: 'Lampiran',
    },
    {
        value: 'documents',
        label: 'Dokumen / PDF',
    },
    {
        value: 'auth',
        label: 'Autentikasi',
    },
];

const categoryStyles: Record<string, string> = {
    bast: 'bg-[#EAF3FA] text-[#1D5D8F]',
    users: 'bg-[#F1EEFA] text-[#65519B]',
    master: 'bg-[#EEF5F2] text-[#3E7460]',
    attachments: 'bg-[#FFF5E8] text-[#926625]',
    documents: 'bg-[#EEF2F5] text-[#53616D]',
    auth: 'bg-[#EAF6EF] text-[#287A4B]',
    other: 'bg-[#F1F3F5] text-[#657481]',
};

function formatDateTime(value: string): string {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

export default function ActivityLogIndex({
    activityLogs,
    filters,
    users,
}: Props) {
    const [search, setSearch] = useState(filters.search);
    const [category, setCategory] = useState(filters.category);
    const [user, setUser] = useState(filters.user);
    const [dateFrom, setDateFrom] = useState(filters.date_from);
    const [dateTo, setDateTo] = useState(filters.date_to);

    const applyFilters = (event: FormEvent) => {
        event.preventDefault();

        router.get(
            activityLogsIndex.url(),
            {
                search,
                category,
                user,
                date_from: dateFrom,
                date_to: dateTo,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const resetFilters = () => {
        setSearch('');
        setCategory('');
        setUser('');
        setDateFrom('');
        setDateTo('');

        router.get(activityLogsIndex.url());
    };

    return (
        <>
            <Head title="Activity Log" />

            <div className="flex min-w-0 flex-1 flex-col px-5 py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1400px] min-w-0">
                    <div>
                        <h1 className="text-[28px] font-semibold tracking-[-0.035em] text-[#17212B]">
                            Activity Log
                        </h1>

                        <p className="mt-1.5 text-sm text-[#71808C]">
                            Pantau riwayat aktivitas dan perubahan penting yang
                            terjadi di dalam sistem.
                        </p>
                    </div>

                    <form
                        onSubmit={applyFilters}
                        className="mt-7 grid gap-3 rounded-[10px] border border-[#DDE3E8] bg-white p-4 xl:grid-cols-[minmax(220px,1fr)_180px_200px_155px_155px_auto_auto]"
                    >
                        <div className="relative min-w-0">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#8C98A2]" />

                            <Input
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Cari aktivitas, pengguna, IP..."
                                className="h-10 border-[#D7DEE4] pl-9 shadow-none"
                            />
                        </div>

                        <select
                            value={category}
                            onChange={(event) =>
                                setCategory(event.target.value)
                            }
                            className="h-10 min-w-0 rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#52616D]"
                        >
                            {categoryOptions.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>

                        <select
                            value={user}
                            onChange={(event) => setUser(event.target.value)}
                            className="h-10 min-w-0 rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#52616D]"
                        >
                            <option value="">Semua pengguna</option>

                            {users.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.name}
                                </option>
                            ))}
                        </select>

                        <Input
                            type="date"
                            value={dateFrom}
                            onChange={(event) =>
                                setDateFrom(event.target.value)
                            }
                            className="h-10 min-w-0 border-[#D7DEE4] shadow-none"
                            aria-label="Tanggal mulai"
                        />

                        <Input
                            type="date"
                            value={dateTo}
                            onChange={(event) => setDateTo(event.target.value)}
                            className="h-10 min-w-0 border-[#D7DEE4] shadow-none"
                            aria-label="Tanggal akhir"
                        />

                        <Button
                            type="submit"
                            className="h-10 bg-[#1D5D8F] px-4 text-white shadow-none hover:bg-[#174C76]"
                        >
                            Terapkan
                        </Button>

                        <Button
                            type="button"
                            variant="outline"
                            onClick={resetFilters}
                            className="h-10 border-[#D7DEE4] bg-white px-4 text-[#52616D] shadow-none"
                        >
                            Reset
                        </Button>
                    </form>

                    <section className="mt-4 min-w-0 overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                        <div className="border-b border-[#E5E9EC] px-5 py-4">
                            <h2 className="text-[15px] font-semibold text-[#344250]">
                                Riwayat Aktivitas
                            </h2>

                            <p className="mt-1 text-xs text-[#87949F]">
                                {activityLogs.total} aktivitas ditemukan
                            </p>
                        </div>

                        {activityLogs.data.length === 0 ? (
                            <div className="flex min-h-[320px] flex-col items-center justify-center px-5 text-center">
                                <div className="flex size-12 items-center justify-center rounded-xl bg-[#F0F4F6] text-[#87949F]">
                                    <Activity className="size-5" />
                                </div>

                                <p className="mt-4 text-sm font-semibold text-[#46545F]">
                                    Aktivitas tidak ditemukan
                                </p>

                                <p className="mt-1 text-xs text-[#8B97A1]">
                                    Coba ubah pencarian atau filter yang
                                    digunakan.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="w-full overflow-x-auto">
                                    <table className="w-full min-w-[980px] table-fixed">
                                        <colgroup>
                                            <col className="w-[13%]" />
                                            <col className="w-[28%]" />
                                            <col className="w-[18%]" />
                                            <col className="w-[10%]" />
                                            <col className="w-[14%]" />
                                            <col className="w-[9%]" />
                                            <col className="w-[8%]" />
                                        </colgroup>

                                        <thead className="bg-[#FAFBFC]">
                                            <tr className="border-b border-[#E6EAED]">
                                                <th className="px-4 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Waktu
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Aktivitas
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Pengguna
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Kategori
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Subject
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    IP
                                                </th>

                                                <th className="px-3 py-3 text-center text-[11px] font-medium text-[#87949F] uppercase">
                                                    Aksi
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {activityLogs.data.map((log) => (
                                                <tr
                                                    key={log.id}
                                                    className="border-b border-[#EDF0F2] last:border-b-0"
                                                >
                                                    <td className="px-4 py-4 align-middle">
                                                        <p className="truncate text-xs text-[#657481]">
                                                            {formatDateTime(
                                                                log.created_at,
                                                            )}
                                                        </p>
                                                    </td>

                                                    <td className="px-4 py-4 align-middle">
                                                        <div className="min-w-0">
                                                            <p className="truncate text-[13px] font-medium text-[#344250]">
                                                                {
                                                                    log.action_label
                                                                }
                                                            </p>

                                                            <p className="mt-1 truncate text-[11px] text-[#87949F]">
                                                                {log.description ??
                                                                    log.action}
                                                            </p>

                                                            <p className="mt-1 truncate font-mono text-[9px] text-[#A0A9B0]">
                                                                {log.action}
                                                            </p>
                                                        </div>
                                                    </td>

                                                    <td className="px-4 py-4 align-middle">
                                                        {log.user ? (
                                                            <div className="flex min-w-0 items-center gap-2.5">
                                                                <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#EAF3FA] text-[10px] font-semibold text-[#1D5D8F]">
                                                                    {log.user.name
                                                                        .slice(
                                                                            0,
                                                                            2,
                                                                        )
                                                                        .toUpperCase()}
                                                                </div>

                                                                <div className="min-w-0">
                                                                    <p className="truncate text-xs font-medium text-[#46545F]">
                                                                        {
                                                                            log
                                                                                .user
                                                                                .name
                                                                        }
                                                                    </p>

                                                                    <p className="mt-0.5 truncate text-[10px] text-[#929DA6]">
                                                                        {
                                                                            log
                                                                                .user
                                                                                .email
                                                                        }
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        ) : (
                                                            <span className="text-xs text-[#929DA6]">
                                                                Sistem
                                                            </span>
                                                        )}
                                                    </td>

                                                    <td className="px-4 py-4 align-middle">
                                                        <span
                                                            className={`inline-flex max-w-full rounded-full px-2.5 py-1 text-[10px] font-medium ${
                                                                categoryStyles[
                                                                    log.category
                                                                        .key
                                                                ] ??
                                                                categoryStyles.other
                                                            }`}
                                                        >
                                                            <span className="truncate">
                                                                {
                                                                    log.category
                                                                        .label
                                                                }
                                                            </span>
                                                        </span>
                                                    </td>

                                                    <td className="px-4 py-4 align-middle">
                                                        <div className="min-w-0">
                                                            <p className="truncate text-[10px] text-[#929DA6]">
                                                                {
                                                                    log.subject
                                                                        .type
                                                                }
                                                            </p>

                                                            {log.subject
                                                                .href ? (
                                                                <Link
                                                                    href={
                                                                        log
                                                                            .subject
                                                                            .href
                                                                    }
                                                                    className="mt-1 block truncate text-xs font-medium text-[#1D5D8F] hover:underline"
                                                                >
                                                                    {
                                                                        log
                                                                            .subject
                                                                            .label
                                                                    }
                                                                </Link>
                                                            ) : (
                                                                <p className="mt-1 truncate text-xs text-[#657481]">
                                                                    {
                                                                        log
                                                                            .subject
                                                                            .label
                                                                    }
                                                                </p>
                                                            )}
                                                        </div>
                                                    </td>

                                                    <td className="px-4 py-4 align-middle">
                                                        <p className="truncate font-mono text-[10px] text-[#71808C]">
                                                            {log.ip_address ??
                                                                '—'}
                                                        </p>
                                                    </td>

                                                    <td className="px-3 py-4 align-middle">
                                                        <div className="flex justify-center">
                                                            <Button
                                                                asChild
                                                                type="button"
                                                                variant="ghost"
                                                                className="h-8 w-full max-w-[76px] justify-center gap-1 px-1 text-xs text-[#1D5D8F]"
                                                            >
                                                                <Link
                                                                    href={showActivityLog(
                                                                        log.id,
                                                                    )}
                                                                >
                                                                    <Eye className="size-3.5 shrink-0" />
                                                                    <span>
                                                                        Detail
                                                                    </span>
                                                                </Link>
                                                            </Button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="flex flex-col gap-3 border-t border-[#E6EAED] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <p className="text-xs text-[#87949F]">
                                        Menampilkan {activityLogs.from ?? 0}–
                                        {activityLogs.to ?? 0} dari{' '}
                                        {activityLogs.total}
                                    </p>

                                    <div className="flex items-center gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            disabled={
                                                !activityLogs.prev_page_url
                                            }
                                            onClick={() => {
                                                if (
                                                    activityLogs.prev_page_url
                                                ) {
                                                    router.get(
                                                        activityLogs.prev_page_url,
                                                    );
                                                }
                                            }}
                                            className="h-8 border-[#D7DEE4] bg-white px-3 text-xs"
                                        >
                                            Sebelumnya
                                        </Button>

                                        <span className="text-xs text-[#657481]">
                                            {activityLogs.current_page} /{' '}
                                            {activityLogs.last_page}
                                        </span>

                                        <Button
                                            type="button"
                                            variant="outline"
                                            disabled={
                                                !activityLogs.next_page_url
                                            }
                                            onClick={() => {
                                                if (
                                                    activityLogs.next_page_url
                                                ) {
                                                    router.get(
                                                        activityLogs.next_page_url,
                                                    );
                                                }
                                            }}
                                            className="h-8 border-[#D7DEE4] bg-white px-3 text-xs"
                                        >
                                            Berikutnya
                                        </Button>
                                    </div>
                                </div>
                            </>
                        )}
                    </section>
                </div>
            </div>
        </>
    );
}

ActivityLogIndex.layout = {
    breadcrumbs: [
        {
            title: 'Activity Log',
            href: '/activity-logs',
        },
    ],
};
