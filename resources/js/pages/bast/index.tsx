import { Head, Link, router } from '@inertiajs/react';
import { FilePlus2, FileText, Search, SlidersHorizontal } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type BastType = {
    id: number;
    name: string;
};

type BastSummary = {
    uuid: string;
    document_number: string | null;
    title: string;
    status: string;
    document_date: string;
    updated_at: string;
    items_count: number;
    attachments_count: number;
    bast_type: BastType | null;
    department: {
        id: number;
        name: string;
        code: string;
    } | null;
    creator: {
        id: number;
        name: string;
    } | null;
};

type Pagination<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

type Props = {
    basts: Pagination<BastSummary>;
    filters: {
        search: string;
        status: string;
        type: string;
        year: string;
    };
    bastTypes: BastType[];
    years: number[];
};

const statusStyles: Record<
    string,
    {
        label: string;
        className: string;
    }
> = {
    draft: {
        label: 'Draft',
        className: 'bg-[#F1F3F5] text-[#63717C]',
    },
    finalized: {
        label: 'Finalized',
        className: 'bg-[#EAF3FA] text-[#1D5D8F]',
    },
    completed: {
        label: 'Selesai',
        className: 'bg-[#EAF6EF] text-[#287A4B]',
    },
    archived: {
        label: 'Diarsipkan',
        className: 'bg-[#EDF0F3] text-[#53616D]',
    },
    cancelled: {
        label: 'Dibatalkan',
        className: 'bg-[#FCECEC] text-[#B64040]',
    },
};

function formatDate(value: string): string {
    const datePart = value.slice(0, 10);
    const date = new Date(`${datePart}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
}

export default function BastIndex({ basts, filters, bastTypes, years }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);
    const [type, setType] = useState(filters.type);
    const [year, setYear] = useState(filters.year);

    const applyFilters = (
        event?: FormEvent<HTMLFormElement>,
        page?: number,
    ) => {
        event?.preventDefault();

        router.get(
            '/bast',
            {
                search,
                status,
                type,
                year,
                page: page ?? 1,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const resetFilters = () => {
        setSearch('');
        setStatus('');
        setType('');
        setYear('');

        router.get(
            '/bast',
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <>
            <Head title="Berita Acara" />

            <div className="flex flex-1 flex-col px-5 py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1600px]">
                    <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
                        <div>
                            <h1 className="text-[28px] font-semibold tracking-[-0.035em] text-[#17212B]">
                                Berita Acara
                            </h1>

                            <p className="mt-1.5 text-sm leading-6 text-[#71808C]">
                                Kelola seluruh dokumen Berita Acara Serah
                                Terima.
                            </p>
                        </div>

                        <Link
                            href="/bast/create"
                            className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-[#1D5D8F] px-4 text-sm font-medium text-white transition-colors hover:bg-[#174C76]"
                        >
                            <FilePlus2 className="size-4" />
                            Buat BAST
                        </Link>
                    </div>

                    <form
                        onSubmit={applyFilters}
                        className="mt-7 rounded-[10px] border border-[#DDE3E8] bg-white p-4"
                    >
                        <div className="flex flex-col gap-3 xl:flex-row">
                            <div className="relative flex-1">
                                <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#8A96A0]" />

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
                                value={status}
                                onChange={(event) =>
                                    setStatus(event.target.value)
                                }
                                className="h-10 rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#4D5B66] outline-none focus:border-[#1D5D8F] xl:w-[170px]"
                            >
                                <option value="">Semua status</option>
                                <option value="draft">Draft</option>
                                <option value="finalized">Finalized</option>
                                <option value="completed">Selesai</option>
                                <option value="archived">Diarsipkan</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>

                            <select
                                value={type}
                                onChange={(event) =>
                                    setType(event.target.value)
                                }
                                className="h-10 rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#4D5B66] outline-none focus:border-[#1D5D8F] xl:w-[220px]"
                            >
                                <option value="">Semua jenis</option>

                                {bastTypes.map((bastType) => (
                                    <option
                                        key={bastType.id}
                                        value={bastType.id}
                                    >
                                        {bastType.name}
                                    </option>
                                ))}
                            </select>

                            <select
                                value={year}
                                onChange={(event) =>
                                    setYear(event.target.value)
                                }
                                className="h-10 rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#4D5B66] outline-none focus:border-[#1D5D8F] xl:w-[130px]"
                            >
                                <option value="">Semua tahun</option>

                                {years.map((item) => (
                                    <option key={item} value={item}>
                                        {item}
                                    </option>
                                ))}
                            </select>

                            <Button
                                type="submit"
                                className="h-10 bg-[#1D5D8F] px-4 text-white hover:bg-[#174C76]"
                            >
                                <SlidersHorizontal className="size-4" />
                                Terapkan
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={resetFilters}
                                className="h-10 border-[#D7DEE4] bg-white px-4 text-[#52616D] shadow-none hover:bg-[#F5F7F9] hover:text-[#344250]"
                            >
                                Reset
                            </Button>
                        </div>
                    </form>

                    <section className="mt-4 overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                        <div className="flex items-center justify-between gap-4 border-b border-[#E7EBEE] px-5 py-4 md:px-6">
                            <div>
                                <h2 className="text-[15px] font-semibold text-[#25313C]">
                                    Daftar BAST
                                </h2>

                                <p className="mt-1 text-xs text-[#87949F]">
                                    {basts.total} dokumen ditemukan
                                </p>
                            </div>
                        </div>

                        {basts.data.length === 0 ? (
                            <div className="flex min-h-[360px] items-center justify-center px-6 py-12">
                                <div className="max-w-sm text-center">
                                    <div className="mx-auto flex size-12 items-center justify-center rounded-[10px] bg-[#EEF4F8] text-[#1D5D8F]">
                                        <FileText
                                            className="size-6"
                                            strokeWidth={1.8}
                                        />
                                    </div>

                                    <h3 className="mt-4 text-sm font-semibold text-[#344250]">
                                        Belum ada berita acara
                                    </h3>

                                    <p className="mt-1.5 text-xs leading-5 text-[#87949F]">
                                        Buat dokumen BAST pertama untuk mulai
                                        mengelola proses serah terima.
                                    </p>

                                    <Link
                                        href="/bast/create"
                                        className="mt-5 inline-flex h-9 items-center justify-center rounded-lg bg-[#1D5D8F] px-4 text-sm font-medium text-white hover:bg-[#174C76]"
                                    >
                                        Buat BAST
                                    </Link>
                                </div>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <table className="w-full min-w-[980px]">
                                        <thead>
                                            <tr className="border-b border-[#E7EBEE] bg-[#FAFBFC]">
                                                <th className="px-6 py-3 text-left text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Dokumen
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Jenis
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Unit
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Tanggal
                                                </th>

                                                <th className="px-4 py-3 text-center text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Item
                                                </th>

                                                <th className="px-4 py-3 text-left text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Status
                                                </th>

                                                <th className="px-6 py-3 text-right text-[11px] font-medium tracking-wide text-[#82909B] uppercase">
                                                    Aksi
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {basts.data.map((bast) => {
                                                const statusStyle =
                                                    statusStyles[bast.status] ??
                                                    statusStyles.draft;

                                                return (
                                                    <tr
                                                        key={bast.uuid}
                                                        className="border-b border-[#EDF0F2] last:border-b-0 hover:bg-[#FBFCFD]"
                                                    >
                                                        <td className="px-6 py-4">
                                                            <p className="max-w-[300px] truncate text-[13px] font-medium text-[#344250]">
                                                                {bast.title}
                                                            </p>

                                                            <p className="mt-1 text-[11px] text-[#8B97A1]">
                                                                {bast.document_number ??
                                                                    'Belum bernomor'}
                                                            </p>
                                                        </td>

                                                        <td className="px-4 py-4 text-[12px] text-[#64727D]">
                                                            {bast.bast_type
                                                                ?.name ?? '—'}
                                                        </td>

                                                        <td className="px-4 py-4 text-[12px] text-[#64727D]">
                                                            {bast.department
                                                                ?.name ?? '—'}
                                                        </td>

                                                        <td className="px-4 py-4 text-[12px] text-[#64727D]">
                                                            {formatDate(
                                                                bast.document_date,
                                                            )}
                                                        </td>

                                                        <td className="px-4 py-4 text-center text-[12px] font-medium text-[#4D5B66]">
                                                            {bast.items_count}
                                                        </td>

                                                        <td className="px-4 py-4">
                                                            <span
                                                                className={`inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium ${statusStyle.className}`}
                                                            >
                                                                {
                                                                    statusStyle.label
                                                                }
                                                            </span>
                                                        </td>

                                                        <td className="px-6 py-4 text-right">
                                                            <Link
                                                                href={`/bast/${bast.uuid}`}
                                                                className="text-xs font-medium text-[#1D5D8F] hover:text-[#174C76]"
                                                            >
                                                                Lihat
                                                            </Link>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="flex flex-col justify-between gap-3 border-t border-[#E7EBEE] px-5 py-4 sm:flex-row sm:items-center md:px-6">
                                    <p className="text-xs text-[#87949F]">
                                        Menampilkan {basts.from ?? 0}–
                                        {basts.to ?? 0} dari {basts.total}
                                    </p>

                                    <div className="flex items-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            disabled={basts.current_page <= 1}
                                            onClick={() =>
                                                applyFilters(
                                                    undefined,
                                                    basts.current_page - 1,
                                                )
                                            }
                                            className="border-[#D7DEE4]"
                                        >
                                            Sebelumnya
                                        </Button>

                                        <span className="px-2 text-xs text-[#657481]">
                                            {basts.current_page} /{' '}
                                            {basts.last_page}
                                        </span>

                                        <Button
                                            variant="outline"
                                            size="sm"
                                            disabled={
                                                basts.current_page >=
                                                basts.last_page
                                            }
                                            onClick={() =>
                                                applyFilters(
                                                    undefined,
                                                    basts.current_page + 1,
                                                )
                                            }
                                            className="border-[#D7DEE4]"
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

BastIndex.layout = {
    breadcrumbs: [
        {
            title: 'Berita Acara',
            href: '/bast',
        },
    ],
};
