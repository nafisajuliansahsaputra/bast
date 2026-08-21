import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    FileText,
    History,
    Mail,
    Phone,
    ShieldCheck,
    UserRound,
} from 'lucide-react';
import { show as showBast } from '@/actions/App/Http/Controllers/BastController';

type Role = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
};

type Department = {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
};

type ManagedUser = {
    id: number;
    name: string;
    nip: string | null;
    email: string;
    position: string | null;
    phone: string | null;
    status: string;
    created_at: string;

    created_basts_count: number;
    activity_logs_count: number;

    role: Role | null;
    department: Department | null;
};

type RecentBast = {
    uuid: string;
    document_number: string | null;
    title: string;
    status: string;
    updated_at: string;

    bast_type: {
        id: number;
        name: string;
    } | null;

    department: Department | null;
};

type Activity = {
    id: number;
    action: string;
    description: string | null;
    subject_type: string | null;
    subject_id: number | null;
    created_at: string;
};

type Props = {
    managedUser: ManagedUser;
    recentBasts: RecentBast[];
    recentActivities: Activity[];
};

const statusLabel: Record<string, string> = {
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
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

export default function UserShow({
    managedUser,
    recentBasts,
    recentActivities,
}: Props) {
    return (
        <>
            <Head title={managedUser.name} />

            <div className="flex flex-1 flex-col px-5 py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1400px]">
                    <Link
                        href="/users"
                        className="inline-flex items-center gap-2 text-sm font-medium text-[#657481] hover:text-[#1D5D8F]"
                    >
                        <ArrowLeft className="size-4" />
                        Kembali ke Pengguna
                    </Link>

                    <div className="mt-5 flex items-start gap-4">
                        <div className="flex size-14 shrink-0 items-center justify-center rounded-full bg-[#EAF3FA] text-lg font-semibold text-[#1D5D8F]">
                            {managedUser.name.slice(0, 2).toUpperCase()}
                        </div>

                        <div>
                            <div className="flex flex-wrap items-center gap-2">
                                <h1 className="text-[28px] font-semibold tracking-[-0.035em] text-[#17212B]">
                                    {managedUser.name}
                                </h1>

                                <span
                                    className={`rounded-full px-2.5 py-1 text-[10px] font-medium ${
                                        managedUser.status === 'active'
                                            ? 'bg-[#EAF6EF] text-[#287A4B]'
                                            : 'bg-[#F1F3F5] text-[#71808C]'
                                    }`}
                                >
                                    {managedUser.status === 'active'
                                        ? 'Aktif'
                                        : 'Nonaktif'}
                                </span>
                            </div>

                            <p className="mt-1.5 text-sm text-[#71808C]">
                                {managedUser.role?.name ?? 'Tanpa role'}
                                {' · '}
                                {managedUser.department?.name ?? 'Tanpa unit'}
                            </p>
                        </div>
                    </div>

                    <div className="mt-7 grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
                        <div className="space-y-4">
                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5 md:p-6">
                                <SectionTitle
                                    icon={UserRound}
                                    title="Informasi Pengguna"
                                />

                                <div className="mt-5 grid gap-5 sm:grid-cols-2">
                                    <Info
                                        label="Nama Lengkap"
                                        value={managedUser.name}
                                    />

                                    <Info
                                        label="NIP"
                                        value={managedUser.nip ?? '—'}
                                    />

                                    <Info
                                        label="Email"
                                        value={managedUser.email}
                                    />

                                    <Info
                                        label="Nomor Telepon"
                                        value={managedUser.phone ?? '—'}
                                    />

                                    <Info
                                        label="Jabatan"
                                        value={managedUser.position ?? '—'}
                                    />

                                    <Info
                                        label="Unit / Bidang"
                                        value={
                                            managedUser.department?.name ?? '—'
                                        }
                                    />

                                    <Info
                                        label="Role"
                                        value={managedUser.role?.name ?? '—'}
                                    />

                                    <Info
                                        label="Terdaftar Sejak"
                                        value={formatDateTime(
                                            managedUser.created_at,
                                        )}
                                    />
                                </div>
                            </section>

                            <section className="overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                                <div className="p-5 md:p-6">
                                    <SectionTitle
                                        icon={FileText}
                                        title="BAST Terbaru"
                                    />
                                </div>

                                {recentBasts.length === 0 ? (
                                    <div className="border-t border-[#E6EAED] px-5 py-10 text-center text-xs text-[#87949F]">
                                        Pengguna belum membuat BAST.
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto border-t border-[#E6EAED]">
                                        <table className="w-full min-w-[720px]">
                                            <thead className="bg-[#FAFBFC]">
                                                <tr>
                                                    <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                        Dokumen
                                                    </th>

                                                    <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                        Jenis
                                                    </th>

                                                    <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                        Status
                                                    </th>

                                                    <th className="px-5 py-3 text-right text-[11px] font-medium text-[#87949F] uppercase">
                                                        Aksi
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                {recentBasts.map((bast) => (
                                                    <tr
                                                        key={bast.uuid}
                                                        className="border-t border-[#EDF0F2]"
                                                    >
                                                        <td className="px-5 py-4">
                                                            <p className="text-[13px] font-medium text-[#344250]">
                                                                {bast.title}
                                                            </p>

                                                            <p className="mt-1 font-mono text-[10px] text-[#87949F]">
                                                                {bast.document_number ??
                                                                    'Belum bernomor'}
                                                            </p>
                                                        </td>

                                                        <td className="px-5 py-4 text-xs text-[#657481]">
                                                            {bast.bast_type
                                                                ?.name ?? '—'}
                                                        </td>

                                                        <td className="px-5 py-4 text-xs text-[#657481]">
                                                            {statusLabel[
                                                                bast.status
                                                            ] ?? bast.status}
                                                        </td>

                                                        <td className="px-5 py-4 text-right">
                                                            <Link
                                                                href={showBast(
                                                                    bast.uuid,
                                                                )}
                                                                className="text-xs font-medium text-[#1D5D8F] hover:underline"
                                                            >
                                                                Lihat
                                                            </Link>
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
                                    icon={History}
                                    title="Aktivitas Terbaru"
                                />

                                {recentActivities.length === 0 ? (
                                    <p className="mt-6 text-xs text-[#87949F]">
                                        Belum ada aktivitas.
                                    </p>
                                ) : (
                                    <div className="mt-5 divide-y divide-[#EDF0F2]">
                                        {recentActivities.map((activity) => (
                                            <div
                                                key={activity.id}
                                                className="py-3 first:pt-0 last:pb-0"
                                            >
                                                <p className="text-[13px] text-[#46545F]">
                                                    {activity.description ??
                                                        activity.action}
                                                </p>

                                                <p className="mt-1 text-[10px] text-[#929DA6]">
                                                    {formatDateTime(
                                                        activity.created_at,
                                                    )}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </section>
                        </div>

                        <div className="space-y-4">
                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5">
                                <h2 className="text-sm font-semibold text-[#344250]">
                                    Ringkasan
                                </h2>

                                <div className="mt-5 grid grid-cols-2 gap-3">
                                    <StatCard
                                        value={String(
                                            managedUser.created_basts_count,
                                        )}
                                        label="BAST Dibuat"
                                    />

                                    <StatCard
                                        value={String(
                                            managedUser.activity_logs_count,
                                        )}
                                        label="Aktivitas"
                                    />
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5">
                                <SectionTitle
                                    icon={ShieldCheck}
                                    title="Akses Sistem"
                                />

                                <div className="mt-5 space-y-4">
                                    <Info
                                        label="Role"
                                        value={managedUser.role?.name ?? '—'}
                                    />

                                    <Info
                                        label="Status Akun"
                                        value={
                                            managedUser.status === 'active'
                                                ? 'Aktif'
                                                : 'Nonaktif'
                                        }
                                    />
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-[#DDE3E8] bg-white p-5">
                                <SectionTitle icon={Mail} title="Kontak" />

                                <div className="mt-5 space-y-4">
                                    <Info
                                        label="Email"
                                        value={managedUser.email}
                                    />

                                    <div className="flex items-start gap-2">
                                        <Phone className="mt-0.5 size-3.5 text-[#87949F]" />

                                        <p className="text-xs text-[#657481]">
                                            {managedUser.phone ??
                                                'Tidak ada nomor telepon'}
                                        </p>
                                    </div>
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
    icon: typeof UserRound;
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

            <p className="mt-1 text-[13px] leading-5 text-[#46545F]">{value}</p>
        </div>
    );
}

function StatCard({ value, label }: { value: string; label: string }) {
    return (
        <div className="rounded-lg border border-[#E3E7EA] bg-[#FAFBFC] p-4">
            <p className="text-xl font-semibold text-[#17212B]">{value}</p>

            <p className="mt-1 text-[10px] text-[#87949F]">{label}</p>
        </div>
    );
}

UserShow.layout = {
    breadcrumbs: [
        {
            title: 'Pengguna',
            href: '/users',
        },
        {
            title: 'Detail',
            href: '#',
        },
    ],
};
