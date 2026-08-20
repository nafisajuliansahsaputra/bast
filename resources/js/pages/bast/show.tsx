import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    FileCheck2,
    FileText,
    Package,
    Paperclip,
    Trash2,
    UserRound,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Party = {
    id: number;
    party_type: string;
    name: string;
    nip: string | null;
    position: string | null;
    department: string | null;
    institution: string;
    address: string | null;
};

type Item = {
    id: number;
    name: string;
    code: string | null;
    inventory_number: string | null;
    serial_number: string | null;
    quantity: string;
    condition: string | null;
    value: string | null;
    description: string | null;

    item_category: {
        id: number;
        name: string;
    } | null;

    unit: {
        id: number;
        name: string;
        symbol: string | null;
    } | null;
};

type BastDetail = {
    uuid: string;
    document_number: string | null;
    title: string;
    description: string | null;
    document_date: string;
    handover_date: string;
    handover_place: string;
    status: string;
    created_at: string;
    finalized_at: string | null;

    bast_type: {
        id: number;
        name: string;
    } | null;

    department: {
        id: number;
        name: string;
        code: string;
    } | null;

    creator: {
        id: number;
        name: string;
        email: string;
    } | null;

    finalized_by: {
        id: number;
        name: string;
    } | null;

    parties: Party[];
    items: Item[];
    attachments: unknown[];
};

type Permissions = {
    update: boolean;
    delete: boolean;
    finalize: boolean;
    manageAttachments: boolean;
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
        month: 'long',
        year: 'numeric',
    }).format(date);
}

function formatCondition(value: string | null): string {
    const labels: Record<string, string> = {
        baik: 'Baik',
        rusak_ringan: 'Rusak Ringan',
        rusak_berat: 'Rusak Berat',
    };

    return value ? (labels[value] ?? value) : '—';
}

export default function BastShow({
    bast,
    permissions,
}: {
    bast: BastDetail;
    permissions: Permissions;
}) {
    const [finalizeOpen, setFinalizeOpen] = useState(false);

    const [deleteOpen, setDeleteOpen] = useState(false);

    const [processingAction, setProcessingAction] = useState(false);

    const status = statusStyles[bast.status] ?? statusStyles.draft;

    const firstParty = bast.parties.find(
        (party) => party.party_type === 'first_party',
    );

    const secondParty = bast.parties.find(
        (party) => party.party_type === 'second_party',
    );

    const finalizeBast = () => {
        setProcessingAction(true);

        router.post(
            `/bast/${bast.uuid}/finalize`,
            {},
            {
                preserveScroll: true,

                onSuccess: () => {
                    setFinalizeOpen(false);
                },

                onFinish: () => {
                    setProcessingAction(false);
                },
            },
        );
    };

    const deleteBast = () => {
        setProcessingAction(true);

        router.delete(`/bast/${bast.uuid}`, {
            onSuccess: () => {
                setDeleteOpen(false);
            },

            onFinish: () => {
                setProcessingAction(false);
            },
        });
    };

    return (
        <>
            <Head title={bast.title} />

            <div className="flex flex-1 flex-col px-5 py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1400px]">
                    <Link
                        href="/bast"
                        className="inline-flex items-center gap-2 text-sm font-medium text-[#657481] hover:text-[#1D5D8F]"
                    >
                        <ArrowLeft className="size-4" />
                        Kembali ke Berita Acara
                    </Link>

                    <div className="mt-5 flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-3">
                                <span
                                    className={`rounded-full px-2.5 py-1 text-[11px] font-medium ${status.className}`}
                                >
                                    {status.label}
                                </span>

                                <span className="font-mono text-xs text-[#87949F]">
                                    {bast.document_number ?? 'Belum bernomor'}
                                </span>
                            </div>

                            <h1 className="mt-3 text-[28px] font-semibold tracking-[-0.035em] text-[#17212B]">
                                {bast.title}
                            </h1>

                            <p className="mt-1.5 text-sm text-[#71808C]">
                                Dibuat oleh {bast.creator?.name ?? '—'}
                            </p>
                        </div>

                        {(permissions.finalize || permissions.delete) && (
                            <div className="flex flex-wrap items-center gap-2">
                                {permissions.finalize && (
                                    <Button
                                        type="button"
                                        onClick={() => setFinalizeOpen(true)}
                                        className="h-10 bg-[#1D5D8F] px-4 text-white shadow-none hover:bg-[#174C76]"
                                    >
                                        <FileCheck2 className="size-4" />
                                        Finalisasi BAST
                                    </Button>
                                )}

                                {permissions.delete && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setDeleteOpen(true)}
                                        className="h-10 border-[#E3CACA] bg-white px-4 text-[#B44949] shadow-none hover:bg-[#FFF6F6] hover:text-[#A53E3E]"
                                    >
                                        <Trash2 className="size-4" />
                                        Hapus Draft
                                    </Button>
                                )}
                            </div>
                        )}
                    </div>

                    <div className="mt-7 grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
                        <div className="space-y-4">
                            <Section icon={FileText} title="Informasi Dokumen">
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <DetailRow
                                        label="Jenis BAST"
                                        value={bast.bast_type?.name ?? '—'}
                                    />

                                    <DetailRow
                                        label="Unit / Bidang"
                                        value={bast.department?.name ?? '—'}
                                    />

                                    <DetailRow
                                        label="Tanggal Dokumen"
                                        value={formatDate(bast.document_date)}
                                    />

                                    <DetailRow
                                        label="Tanggal Serah Terima"
                                        value={formatDate(bast.handover_date)}
                                    />

                                    <div className="sm:col-span-2">
                                        <DetailRow
                                            label="Tempat Serah Terima"
                                            value={bast.handover_place}
                                        />
                                    </div>

                                    {bast.description && (
                                        <div className="sm:col-span-2">
                                            <DetailRow
                                                label="Keterangan"
                                                value={bast.description}
                                            />
                                        </div>
                                    )}
                                </div>
                            </Section>

                            <Section icon={UserRound} title="Pihak Terkait">
                                <div className="grid gap-4 lg:grid-cols-2">
                                    <PartyDetail
                                        title="Pihak Pertama"
                                        subtitle="Menyerahkan"
                                        party={firstParty}
                                    />

                                    <PartyDetail
                                        title="Pihak Kedua"
                                        subtitle="Menerima"
                                        party={secondParty}
                                    />
                                </div>
                            </Section>

                            <Section
                                icon={Package}
                                title={`Item (${bast.items.length})`}
                            >
                                <div className="overflow-x-auto">
                                    <table className="w-full min-w-[760px]">
                                        <thead>
                                            <tr className="border-b border-[#E7EBEE]">
                                                <th className="pb-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Item
                                                </th>

                                                <th className="pb-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Kategori
                                                </th>

                                                <th className="pb-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Jumlah
                                                </th>

                                                <th className="pb-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Kondisi
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {bast.items.map((item) => (
                                                <tr
                                                    key={item.id}
                                                    className="border-b border-[#EDF0F2] last:border-b-0"
                                                >
                                                    <td className="py-4 pr-4">
                                                        <p className="text-[13px] font-medium text-[#344250]">
                                                            {item.name}
                                                        </p>

                                                        <p className="mt-1 text-[11px] text-[#8B97A1]">
                                                            {item.inventory_number ??
                                                                item.serial_number ??
                                                                item.code ??
                                                                '—'}
                                                        </p>
                                                    </td>

                                                    <td className="py-4 pr-4 text-xs text-[#657481]">
                                                        {item.item_category
                                                            ?.name ?? '—'}
                                                    </td>

                                                    <td className="py-4 pr-4 text-xs text-[#657481]">
                                                        {item.quantity}{' '}
                                                        {item.unit?.symbol ??
                                                            item.unit?.name ??
                                                            ''}
                                                    </td>

                                                    <td className="py-4 text-xs text-[#657481]">
                                                        {formatCondition(
                                                            item.condition,
                                                        )}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </Section>
                        </div>

                        <div className="space-y-4">
                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5">
                                <h2 className="text-sm font-semibold text-[#344250]">
                                    Ringkasan
                                </h2>

                                <div className="mt-4 space-y-4">
                                    <DetailRow
                                        label="Status"
                                        value={status.label}
                                    />

                                    <DetailRow
                                        label="Nomor Dokumen"
                                        value={
                                            bast.document_number ??
                                            'Belum bernomor'
                                        }
                                    />

                                    <DetailRow
                                        label="Jumlah Item"
                                        value={String(bast.items.length)}
                                    />

                                    <DetailRow
                                        label="Lampiran"
                                        value={String(bast.attachments.length)}
                                    />
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5">
                                <div className="flex items-center gap-2">
                                    <Paperclip className="size-4 text-[#1D5D8F]" />

                                    <h2 className="text-sm font-semibold text-[#344250]">
                                        Lampiran
                                    </h2>
                                </div>

                                <p className="mt-3 text-xs leading-5 text-[#87949F]">
                                    {bast.attachments.length === 0
                                        ? 'Belum ada lampiran pada BAST ini.'
                                        : `${bast.attachments.length} lampiran tersedia.`}
                                </p>
                            </section>
                        </div>
                    </div>
                </div>
            </div>

            <Dialog open={finalizeOpen} onOpenChange={setFinalizeOpen}>
                <DialogContent className="bast-app border-[#DDE3E8] bg-white sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="text-[#17212B]">
                            Finalisasi BAST?
                        </DialogTitle>

                        <DialogDescription className="leading-6 text-[#71808C]">
                            Setelah difinalisasi, nomor dokumen akan dibuat dan
                            isi BAST dikunci dari perubahan biasa.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="rounded-lg border border-[#DCE8F0] bg-[#F5F9FC] px-4 py-3">
                        <p className="text-xs leading-5 text-[#526675]">
                            Pastikan informasi dokumen, pihak terkait, dan item
                            sudah benar sebelum melanjutkan.
                        </p>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={processingAction}
                            onClick={() => setFinalizeOpen(false)}
                            className="border-[#D7DEE4] bg-white text-[#52616D]"
                        >
                            Batal
                        </Button>

                        <Button
                            type="button"
                            disabled={processingAction}
                            onClick={finalizeBast}
                            className="bg-[#1D5D8F] text-white hover:bg-[#174C76]"
                        >
                            {processingAction
                                ? 'Memfinalisasi...'
                                : 'Ya, Finalisasi'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                <DialogContent className="bast-app border-[#DDE3E8] bg-white sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="text-[#17212B]">
                            Hapus draft BAST?
                        </DialogTitle>

                        <DialogDescription className="leading-6 text-[#71808C]">
                            Draft &quot;{bast.title}&quot; akan dihapus dari
                            daftar BAST. Tindakan ini hanya tersedia selama
                            dokumen masih Draft.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={processingAction}
                            onClick={() => setDeleteOpen(false)}
                            className="border-[#D7DEE4] bg-white text-[#52616D]"
                        >
                            Batal
                        </Button>

                        <Button
                            type="button"
                            disabled={processingAction}
                            onClick={deleteBast}
                            className="bg-[#B44949] text-white hover:bg-[#9E3D3D]"
                        >
                            {processingAction ? 'Menghapus...' : 'Hapus Draft'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

function Section({
    icon: Icon,
    title,
    children,
}: {
    icon: typeof FileText;
    title: string;
    children: React.ReactNode;
}) {
    return (
        <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5 md:p-6">
            <div className="flex items-center gap-2.5">
                <div className="flex size-8 items-center justify-center rounded-lg bg-[#EEF4F8] text-[#1D5D8F]">
                    <Icon className="size-4" />
                </div>

                <h2 className="text-[15px] font-semibold text-[#344250]">
                    {title}
                </h2>
            </div>

            <div className="mt-5">{children}</div>
        </section>
    );
}

function DetailRow({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-[11px] font-medium text-[#8A96A0]">{label}</p>

            <p className="mt-1 text-[13px] leading-5 text-[#46545F]">{value}</p>
        </div>
    );
}

function PartyDetail({
    title,
    subtitle,
    party,
}: {
    title: string;
    subtitle: string;
    party?: Party;
}) {
    return (
        <div className="rounded-lg border border-[#E2E6EA] p-4">
            <div>
                <p className="text-xs font-semibold text-[#344250]">{title}</p>

                <p className="mt-0.5 text-[10px] text-[#87949F]">{subtitle}</p>
            </div>

            <div className="mt-4 space-y-3">
                <DetailRow label="Nama" value={party?.name ?? '—'} />

                <DetailRow label="NIP" value={party?.nip ?? '—'} />

                <DetailRow label="Jabatan" value={party?.position ?? '—'} />

                <DetailRow
                    label="Unit / Bidang"
                    value={party?.department ?? '—'}
                />

                <DetailRow label="Instansi" value={party?.institution ?? '—'} />
            </div>
        </div>
    );
}

BastShow.layout = {
    breadcrumbs: [
        {
            title: 'Berita Acara',
            href: '/bast',
        },
        {
            title: 'Detail',
            href: '#',
        },
    ],
};
