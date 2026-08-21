import { Head, Link, router } from '@inertiajs/react';
import {
    Archive,
    ChevronRight,
    Eye,
    Package,
    Paperclip,
    RotateCcw,
    Search,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { index as archiveIndex } from '@/actions/App/Http/Controllers/ArchiveController';
import { show as showBast } from '@/actions/App/Http/Controllers/BastController';
import { restore as restoreBast } from '@/actions/App/Http/Controllers/BastLifecycleController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';

type SelectOption = {
    id: number;
    name: string;
};

type Department = SelectOption & {
    code: string;
};

type BastSummary = {
    uuid: string;
    document_number: string;
    title: string;
    document_date: string;
    archived_at: string;
    items_count: number;
    attachments_count: number;

    bast_type: SelectOption | null;

    department: Department | null;

    creator: {
        id: number;
        name: string;
    } | null;

    archived_by: {
        id: number;
        name: string;
    } | null;
};

type PaginatedBasts = {
    data: BastSummary[];
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
    type: string;
    year: string;
    department: string;
};

type Props = {
    basts: PaginatedBasts;
    filters: Filters;
    bastTypes: SelectOption[];
    departments: Department[];
    years: number[];
    canRestore: boolean;
};

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
}

export default function ArchiveIndex({
    basts,
    filters,
    bastTypes,
    departments,
    years,
    canRestore,
}: Props) {
    const [search, setSearch] = useState(filters.search);
    const [type, setType] = useState(filters.type);
    const [year, setYear] = useState(filters.year);
    const [department, setDepartment] = useState(filters.department);

    const [restoreTarget, setRestoreTarget] = useState<BastSummary | null>(
        null,
    );

    const [restoring, setRestoring] = useState(false);

    const applyFilters = (event: FormEvent) => {
        event.preventDefault();

        router.get(
            archiveIndex.url(),
            {
                search,
                type,
                year,
                department,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const resetFilters = () => {
        setSearch('');
        setType('');
        setYear('');
        setDepartment('');

        router.get(archiveIndex.url());
    };

    const runRestore = () => {
        if (!restoreTarget) {
            return;
        }

        setRestoring(true);

        router.post(
            restoreBast.url(restoreTarget.uuid),
            {},
            {
                onSuccess: () => {
                    setRestoreTarget(null);
                },

                onFinish: () => {
                    setRestoring(false);
                },
            },
        );
    };

    const goToPage = (url: string | null) => {
        if (!url) {
            return;
        }

        router.get(url);
    };

    return (
        <>
            <Head title="Arsip" />

            <div className="flex flex-1 flex-col px-4 py-5 sm:px-5 sm:py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1400px]">
                    <div>
                        <h1 className="text-[26px] font-semibold tracking-[-0.035em] text-[#17212B] sm:text-[28px]">
                            Arsip
                        </h1>

                        <p className="mt-1.5 text-sm leading-6 text-[#71808C]">
                            Dokumen BAST yang telah selesai dan diarsipkan.
                        </p>
                    </div>

                    <form
                        onSubmit={applyFilters}
                        className="mt-6 grid gap-3 rounded-[10px] border border-[#DDE3E8] bg-white p-4 md:mt-7 lg:grid-cols-[minmax(260px,1fr)_190px_160px_220px_auto_auto]"
                    >
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#8C98A2]" />

                            <Input
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Cari nomor, judul, pihak, atau item..."
                                className="h-10 border-[#D7DEE4] pl-9 shadow-none"
                            />
                        </div>

                        <select
                            value={type}
                            onChange={(event) => setType(event.target.value)}
                            className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#52616D]"
                        >
                            <option value="">Semua jenis</option>

                            {bastTypes.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.name}
                                </option>
                            ))}
                        </select>

                        <select
                            value={year}
                            onChange={(event) => setYear(event.target.value)}
                            className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#52616D]"
                        >
                            <option value="">Semua tahun</option>

                            {years.map((item) => (
                                <option key={item} value={item}>
                                    {item}
                                </option>
                            ))}
                        </select>

                        <select
                            value={department}
                            onChange={(event) =>
                                setDepartment(event.target.value)
                            }
                            className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#52616D]"
                        >
                            <option value="">Semua unit</option>

                            {departments.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.name}
                                </option>
                            ))}
                        </select>

                        <div className="grid grid-cols-2 gap-3 lg:contents">
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
                                className="h-10 border-[#D7DEE4] bg-white px-4 text-[#52616D] shadow-none hover:bg-[#F5F7F9]"
                            >
                                Reset
                            </Button>
                        </div>
                    </form>

                    <section className="mt-4 overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                        <div className="border-b border-[#E5E9EC] px-4 py-4 sm:px-5">
                            <div className="flex items-center gap-3">
                                <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#EEF4F8] text-[#1D5D8F]">
                                    <Archive className="size-4" />
                                </div>

                                <div className="min-w-0">
                                    <h2 className="text-[15px] font-semibold text-[#344250]">
                                        Dokumen Diarsipkan
                                    </h2>

                                    <p className="mt-0.5 text-xs text-[#87949F]">
                                        {basts.total} dokumen ditemukan
                                    </p>
                                </div>
                            </div>
                        </div>

                        {basts.data.length === 0 ? (
                            <div className="flex min-h-[320px] flex-col items-center justify-center px-5 text-center">
                                <div className="flex size-12 items-center justify-center rounded-xl bg-[#F0F4F6] text-[#87949F]">
                                    <Archive className="size-5" />
                                </div>

                                <p className="mt-4 text-sm font-semibold text-[#46545F]">
                                    Belum ada dokumen arsip
                                </p>

                                <p className="mt-1 max-w-[400px] text-xs leading-5 text-[#8B97A1]">
                                    BAST yang telah selesai dan diarsipkan akan
                                    muncul di sini.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="divide-y divide-[#EDF0F2] lg:hidden">
                                    {basts.data.map((bast) => (
                                        <div
                                            key={bast.uuid}
                                            className="px-4 py-4 sm:px-5"
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0 flex-1">
                                                    <p className="text-sm font-semibold break-words text-[#344250]">
                                                        {bast.title}
                                                    </p>

                                                    <p className="mt-1 font-mono text-[10px] break-all text-[#87949F]">
                                                        {bast.document_number}
                                                    </p>
                                                </div>

                                                <span className="shrink-0 rounded-full bg-[#EDF0F3] px-2.5 py-1 text-[10px] font-medium text-[#53616D]">
                                                    Diarsipkan
                                                </span>
                                            </div>

                                            <div className="mt-4 grid grid-cols-2 gap-x-4 gap-y-3">
                                                <div>
                                                    <p className="text-[10px] font-medium text-[#929DA6] uppercase">
                                                        Jenis
                                                    </p>

                                                    <p className="mt-1 line-clamp-2 text-[11px] leading-4 text-[#5F6D78]">
                                                        {bast.bast_type?.name ??
                                                            '—'}
                                                    </p>
                                                </div>

                                                <div>
                                                    <p className="text-[10px] font-medium text-[#929DA6] uppercase">
                                                        Unit
                                                    </p>

                                                    <p className="mt-1 line-clamp-2 text-[11px] leading-4 text-[#5F6D78]">
                                                        {bast.department
                                                            ?.name ?? '—'}
                                                    </p>
                                                </div>

                                                <div>
                                                    <p className="text-[10px] font-medium text-[#929DA6] uppercase">
                                                        Diarsipkan
                                                    </p>

                                                    <p className="mt-1 text-[11px] leading-4 text-[#5F6D78]">
                                                        {formatDate(
                                                            bast.archived_at,
                                                        )}
                                                    </p>
                                                </div>

                                                <div>
                                                    <p className="text-[10px] font-medium text-[#929DA6] uppercase">
                                                        Oleh
                                                    </p>

                                                    <p className="mt-1 line-clamp-2 text-[11px] leading-4 text-[#5F6D78]">
                                                        {bast.archived_by
                                                            ?.name ?? '—'}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-[#EDF0F2] pt-3">
                                                <span className="flex items-center gap-1.5 text-[10px] text-[#87949F]">
                                                    <Package className="size-3.5" />
                                                    {bast.items_count} item
                                                </span>

                                                <span className="flex items-center gap-1.5 text-[10px] text-[#87949F]">
                                                    <Paperclip className="size-3.5" />
                                                    {bast.attachments_count}{' '}
                                                    lampiran
                                                </span>
                                            </div>

                                            <div className="mt-3 grid grid-cols-2 gap-2">
                                                <Button
                                                    asChild
                                                    type="button"
                                                    variant="outline"
                                                    className="h-9 border-[#D7DEE4] bg-white text-xs text-[#1D5D8F] shadow-none"
                                                >
                                                    <Link
                                                        href={showBast(
                                                            bast.uuid,
                                                        )}
                                                    >
                                                        <Eye className="size-3.5" />
                                                        Lihat Detail
                                                    </Link>
                                                </Button>

                                                {canRestore ? (
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        onClick={() =>
                                                            setRestoreTarget(
                                                                bast,
                                                            )
                                                        }
                                                        className="h-9 border-[#D7DEE4] bg-white text-xs text-[#52616D] shadow-none"
                                                    >
                                                        <RotateCcw className="size-3.5" />
                                                        Pulihkan
                                                    </Button>
                                                ) : (
                                                    <Link
                                                        href={showBast(
                                                            bast.uuid,
                                                        )}
                                                        className="flex h-9 items-center justify-center gap-1 text-xs font-medium text-[#1D5D8F]"
                                                    >
                                                        Buka
                                                        <ChevronRight className="size-3.5" />
                                                    </Link>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                <div className="hidden overflow-x-auto lg:block">
                                    <table className="w-full min-w-[1000px]">
                                        <thead className="bg-[#FAFBFC]">
                                            <tr className="border-b border-[#E6EAED]">
                                                <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Dokumen
                                                </th>

                                                <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Jenis
                                                </th>

                                                <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Unit
                                                </th>

                                                <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Diarsipkan
                                                </th>

                                                <th className="px-5 py-3 text-center text-[11px] font-medium text-[#87949F] uppercase">
                                                    Item
                                                </th>

                                                <th className="px-5 py-3 text-right text-[11px] font-medium text-[#87949F] uppercase">
                                                    Aksi
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {basts.data.map((bast) => (
                                                <tr
                                                    key={bast.uuid}
                                                    className="border-b border-[#EDF0F2] last:border-b-0"
                                                >
                                                    <td className="px-5 py-4">
                                                        <p className="text-[13px] font-medium text-[#344250]">
                                                            {bast.title}
                                                        </p>

                                                        <p className="mt-1 font-mono text-[10px] text-[#87949F]">
                                                            {
                                                                bast.document_number
                                                            }
                                                        </p>
                                                    </td>

                                                    <td className="px-5 py-4 text-xs text-[#657481]">
                                                        {bast.bast_type?.name ??
                                                            '—'}
                                                    </td>

                                                    <td className="px-5 py-4 text-xs text-[#657481]">
                                                        {bast.department
                                                            ?.name ?? '—'}
                                                    </td>

                                                    <td className="px-5 py-4 text-xs text-[#657481]">
                                                        {formatDate(
                                                            bast.archived_at,
                                                        )}
                                                    </td>

                                                    <td className="px-5 py-4 text-center text-xs text-[#657481]">
                                                        {bast.items_count}
                                                    </td>

                                                    <td className="px-5 py-4">
                                                        <div className="flex justify-end gap-1">
                                                            <Button
                                                                asChild
                                                                type="button"
                                                                variant="ghost"
                                                                className="h-8 px-2 text-xs text-[#1D5D8F]"
                                                            >
                                                                <Link
                                                                    href={showBast(
                                                                        bast.uuid,
                                                                    )}
                                                                >
                                                                    <Eye className="size-3.5" />
                                                                    Lihat
                                                                </Link>
                                                            </Button>

                                                            {canRestore && (
                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    onClick={() =>
                                                                        setRestoreTarget(
                                                                            bast,
                                                                        )
                                                                    }
                                                                    className="h-8 px-2 text-xs text-[#52616D]"
                                                                >
                                                                    <RotateCcw className="size-3.5" />
                                                                    Pulihkan
                                                                </Button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="border-t border-[#E6EAED] px-4 py-4 sm:px-5">
                                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <p className="text-center text-xs text-[#87949F] sm:text-left">
                                            Menampilkan {basts.from ?? 0}–
                                            {basts.to ?? 0} dari {basts.total}
                                        </p>

                                        <div className="grid grid-cols-[1fr_auto_1fr] items-center gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                disabled={!basts.prev_page_url}
                                                onClick={() =>
                                                    goToPage(
                                                        basts.prev_page_url,
                                                    )
                                                }
                                                className="h-8 border-[#D7DEE4] bg-white px-3 text-xs text-[#657481]"
                                            >
                                                Sebelumnya
                                            </Button>

                                            <span className="px-1 text-center text-xs whitespace-nowrap text-[#657481]">
                                                {basts.current_page} /{' '}
                                                {basts.last_page}
                                            </span>

                                            <Button
                                                type="button"
                                                variant="outline"
                                                disabled={!basts.next_page_url}
                                                onClick={() =>
                                                    goToPage(
                                                        basts.next_page_url,
                                                    )
                                                }
                                                className="h-8 border-[#D7DEE4] bg-white px-3 text-xs text-[#657481]"
                                            >
                                                Berikutnya
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </>
                        )}
                    </section>
                </div>
            </div>

            <Dialog
                open={restoreTarget !== null}
                onOpenChange={(open) => {
                    if (!open && !restoring) {
                        setRestoreTarget(null);
                    }
                }}
            >
                <DialogContent className="bast-app w-[calc(100%-2rem)] border-[#DDE3E8] bg-white sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="text-[#17212B]">
                            Pulihkan dari arsip?
                        </DialogTitle>

                        <DialogDescription className="leading-6 break-words text-[#71808C]">
                            BAST &quot;
                            {restoreTarget?.title}
                            &quot; akan dikembalikan ke status Selesai.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="rounded-lg border border-[#DCE3E8] bg-[#F7F9FA] px-4 py-3">
                        <p className="text-xs leading-5 text-[#5D6B76]">
                            Nomor dokumen dan seluruh isi BAST tetap
                            dipertahankan setelah dokumen dipulihkan.
                        </p>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={restoring}
                            onClick={() => setRestoreTarget(null)}
                            className="w-full border-[#D7DEE4] bg-white text-[#52616D] sm:w-auto"
                        >
                            Batal
                        </Button>

                        <Button
                            type="button"
                            disabled={restoring}
                            onClick={runRestore}
                            className="w-full bg-[#1D5D8F] text-white hover:bg-[#174C76] sm:w-auto"
                        >
                            {restoring ? 'Memulihkan...' : 'Pulihkan BAST'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

ArchiveIndex.layout = {
    breadcrumbs: [
        {
            title: 'Arsip',
            href: '/archive',
        },
    ],
};
