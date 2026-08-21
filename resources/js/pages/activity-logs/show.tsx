import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Clock3,
    Database,
    Globe2,
    Monitor,
    UserRound,
} from 'lucide-react';

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

type ChangeValues = Record<string, unknown> | null;

type ActivityLogDetail = {
    id: number;
    action: string;
    action_label: string;
    description: string | null;
    category: Category;
    user: ActivityUser | null;
    subject: Subject;
    ip_address: string | null;
    user_agent: string | null;
    old_values: ChangeValues;
    new_values: ChangeValues;
    created_at: string;
};

type Props = {
    activityLog: ActivityLogDetail;
};

const categoryStyles: Record<string, string> = {
    bast: 'bg-[#EAF3FA] text-[#1D5D8F]',
    users: 'bg-[#F1EEFA] text-[#65519B]',
    master: 'bg-[#EEF5F2] text-[#3E7460]',
    attachments: 'bg-[#FFF5E8] text-[#926625]',
    documents: 'bg-[#EEF2F5] text-[#53616D]',
    auth: 'bg-[#EAF6EF] text-[#287A4B]',
    other: 'bg-[#F1F3F5] text-[#657481]',
};

const fieldLabels: Record<string, string> = {
    title: 'Judul',
    status: 'Status',
    department_id: 'Unit / Bidang ID',
    bast_type_id: 'Jenis BAST ID',
    document_number: 'Nomor Dokumen',
    sequence_number: 'Nomor Urut',
    description: 'Deskripsi',
    document_date: 'Tanggal Dokumen',
    handover_date: 'Tanggal Serah Terima',
    handover_place: 'Tempat Serah Terima',
    items_count: 'Jumlah Item',
    completed_at: 'Waktu Selesai',
    completed_by: 'Diselesaikan Oleh',
    archived_at: 'Waktu Arsip',
    archived_by: 'Diarsipkan Oleh',
    cancelled_at: 'Waktu Pembatalan',
    cancelled_by: 'Dibatalkan Oleh',
    cancellation_reason: 'Alasan Pembatalan',

    name: 'Nama',
    slug: 'Slug',
    code: 'Kode',
    symbol: 'Simbol',
    is_active: 'Status Aktif',

    email: 'Email',
    nip: 'NIP',
    position: 'Jabatan',
    phone: 'Nomor Telepon',
    role_id: 'Role ID',

    attachment_id: 'Lampiran ID',
    original_name: 'Nama File',
    category: 'Kategori',

    two_factor_enabled: 'Autentikasi Dua Faktor',
};

const statusLabels: Record<string, string> = {
    active: 'Aktif',
    inactive: 'Nonaktif',

    draft: 'Draft',
    finalized: 'Finalized',
    completed: 'Selesai',
    archived: 'Diarsipkan',
    cancelled: 'Dibatalkan',
};

function formatDateTime(value: string): string {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(date);
}

function valuesAreEqual(first: unknown, second: unknown): boolean {
    if (Object.is(first, second)) {
        return true;
    }

    if (
        typeof first === 'object' &&
        first !== null &&
        typeof second === 'object' &&
        second !== null
    ) {
        try {
            return JSON.stringify(first) === JSON.stringify(second);
        } catch {
            return false;
        }
    }

    return false;
}

function humanizeValue(key: string, value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (key === 'status' && typeof value === 'string' && statusLabels[value]) {
        return statusLabels[value];
    }

    if (key === 'is_active' || key === 'two_factor_enabled') {
        if (value === true || value === 1 || value === '1') {
            return 'Ya';
        }

        if (value === false || value === 0 || value === '0') {
            return 'Tidak';
        }
    }

    if (typeof value === 'boolean') {
        return value ? 'Ya' : 'Tidak';
    }

    if (
        typeof value === 'string' ||
        typeof value === 'number' ||
        typeof value === 'bigint'
    ) {
        return String(value);
    }

    try {
        return JSON.stringify(value, null, 2);
    } catch {
        return String(value);
    }
}

function fieldLabel(key: string): string {
    if (fieldLabels[key]) {
        return fieldLabels[key];
    }

    return key
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

export default function ActivityLogShow({ activityLog }: Props) {
    const oldValues = activityLog.old_values ?? {};
    const newValues = activityLog.new_values ?? {};

    const changeKeys = Array.from(
        new Set([...Object.keys(oldValues), ...Object.keys(newValues)]),
    ).filter((key) => !valuesAreEqual(oldValues[key], newValues[key]));

    return (
        <>
            <Head title={activityLog.action_label} />

            <div className="flex flex-1 flex-col px-5 py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1200px]">
                    <Link
                        href="/activity-logs"
                        className="inline-flex items-center gap-2 text-sm font-medium text-[#657481] hover:text-[#1D5D8F]"
                    >
                        <ArrowLeft className="size-4" />
                        Kembali ke Activity Log
                    </Link>

                    <div className="mt-5">
                        <div className="flex flex-wrap items-center gap-2">
                            <span
                                className={`rounded-full px-2.5 py-1 text-[10px] font-medium ${
                                    categoryStyles[activityLog.category.key] ??
                                    categoryStyles.other
                                }`}
                            >
                                {activityLog.category.label}
                            </span>

                            <span className="font-mono text-[10px] text-[#929DA6]">
                                {activityLog.action}
                            </span>
                        </div>

                        <h1 className="mt-3 text-[28px] font-semibold tracking-[-0.035em] text-[#17212B]">
                            {activityLog.action_label}
                        </h1>

                        <p className="mt-1.5 max-w-[800px] text-sm leading-6 text-[#71808C]">
                            {activityLog.description ??
                                'Tidak ada deskripsi tambahan untuk aktivitas ini.'}
                        </p>
                    </div>

                    <div className="mt-7 grid gap-4 lg:grid-cols-[minmax(0,1fr)_340px]">
                        <div className="space-y-4">
                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5 md:p-6">
                                <SectionTitle
                                    icon={Database}
                                    title="Perubahan Data"
                                />

                                {changeKeys.length === 0 ? (
                                    <div className="mt-5 rounded-lg border border-dashed border-[#DDE3E8] px-5 py-10 text-center">
                                        <p className="text-sm font-medium text-[#52616D]">
                                            Tidak ada perubahan nilai
                                        </p>

                                        <p className="mt-1 text-xs leading-5 text-[#929DA6]">
                                            Aktivitas ini tidak memiliki
                                            perbedaan antara data sebelum dan
                                            sesudah.
                                        </p>
                                    </div>
                                ) : (
                                    <div className="mt-5 overflow-x-auto rounded-lg border border-[#E5E9EC]">
                                        <table className="w-full min-w-[620px]">
                                            <thead>
                                                <tr className="border-b border-[#E6EAED] bg-[#FAFBFC]">
                                                    <th className="w-[180px] px-4 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                        Field
                                                    </th>

                                                    <th className="px-4 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                        Sebelum
                                                    </th>

                                                    <th className="px-4 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                        Sesudah
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                {changeKeys.map((key) => (
                                                    <tr
                                                        key={key}
                                                        className="border-b border-[#EDF0F2] last:border-b-0"
                                                    >
                                                        <td className="px-4 py-4 align-top text-xs font-medium text-[#46545F]">
                                                            {fieldLabel(key)}
                                                        </td>

                                                        <td className="px-4 py-4 align-top">
                                                            <pre className="max-w-[300px] font-sans text-[12px] leading-5 break-words whitespace-pre-wrap text-[#7A8791]">
                                                                {humanizeValue(
                                                                    key,
                                                                    oldValues[
                                                                        key
                                                                    ],
                                                                )}
                                                            </pre>
                                                        </td>

                                                        <td className="px-4 py-4 align-top">
                                                            <pre className="max-w-[300px] font-sans text-[12px] leading-5 font-medium break-words whitespace-pre-wrap text-[#344250]">
                                                                {humanizeValue(
                                                                    key,
                                                                    newValues[
                                                                        key
                                                                    ],
                                                                )}
                                                            </pre>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </section>

                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5 md:p-6">
                                <SectionTitle
                                    icon={Monitor}
                                    title="Informasi Perangkat"
                                />

                                <div className="mt-5">
                                    <Info
                                        label="User Agent"
                                        value={
                                            activityLog.user_agent ??
                                            'Tidak tersedia'
                                        }
                                    />
                                </div>
                            </section>
                        </div>

                        <div className="space-y-4">
                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5">
                                <SectionTitle icon={UserRound} title="Pelaku" />

                                <div className="mt-5">
                                    {activityLog.user ? (
                                        <>
                                            <p className="text-sm font-semibold text-[#344250]">
                                                {activityLog.user.name}
                                            </p>

                                            <p className="mt-1 text-xs break-all text-[#87949F]">
                                                {activityLog.user.email}
                                            </p>
                                        </>
                                    ) : (
                                        <p className="text-sm text-[#657481]">
                                            Sistem
                                        </p>
                                    )}
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5">
                                <SectionTitle icon={Clock3} title="Waktu" />

                                <div className="mt-5">
                                    <Info
                                        label="Terjadi Pada"
                                        value={formatDateTime(
                                            activityLog.created_at,
                                        )}
                                    />
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5">
                                <SectionTitle icon={Globe2} title="Request" />

                                <div className="mt-5">
                                    <Info
                                        label="IP Address"
                                        value={
                                            activityLog.ip_address ??
                                            'Tidak tersedia'
                                        }
                                    />
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5">
                                <SectionTitle icon={Database} title="Subject" />

                                <div className="mt-5">
                                    <p className="text-[11px] font-medium text-[#8A96A0]">
                                        {activityLog.subject.type}
                                    </p>

                                    {activityLog.subject.href ? (
                                        <Link
                                            href={activityLog.subject.href}
                                            className="mt-1 block text-sm font-medium break-words text-[#1D5D8F] hover:underline"
                                        >
                                            {activityLog.subject.label}
                                        </Link>
                                    ) : (
                                        <p className="mt-1 text-sm break-words text-[#46545F]">
                                            {activityLog.subject.label}
                                        </p>
                                    )}
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

function SectionTitle({
    icon: Icon,
    title,
}: {
    icon: typeof Database;
    title: string;
}) {
    return (
        <div className="flex items-center gap-2.5">
            <div className="flex size-8 items-center justify-center rounded-lg bg-[#EEF4F8] text-[#1D5D8F]">
                <Icon className="size-4" />
            </div>

            <h2 className="text-[15px] font-semibold text-[#344250]">
                {title}
            </h2>
        </div>
    );
}

function Info({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-[11px] font-medium text-[#8A96A0]">{label}</p>

            <p className="mt-1 text-[13px] leading-5 break-words text-[#46545F]">
                {value}
            </p>
        </div>
    );
}

ActivityLogShow.layout = {
    breadcrumbs: [
        {
            title: 'Activity Log',
            href: '/activity-logs',
        },
        {
            title: 'Detail',
            href: '#',
        },
    ],
};
