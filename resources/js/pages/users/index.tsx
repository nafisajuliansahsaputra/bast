import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import {
    Check,
    Copy,
    Eye,
    KeyRound,
    PencilLine,
    Plus,
    Search,
    ShieldCheck,
    UserCheck,
    UserRound,
    UserX,
} from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import {
    index as usersIndex,
    resetPassword,
    show as showUser,
    store as storeUser,
    toggleStatus,
    update as updateUser,
} from '@/actions/App/Http/Controllers/UserController';
import InputError from '@/components/input-error';
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
import { Label } from '@/components/ui/label';

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
    role_id: number | null;
    department_id: number | null;
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

type PaginatedUsers = {
    data: ManagedUser[];
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
    role: string;
    status: string;
    department: string;
};

type TemporaryCredential = {
    user_id: number;
    name: string;
    email: string;
    password: string;
};

type Props = {
    users: PaginatedUsers;
    filters: Filters;
    roles: Role[];
    departments: Department[];
    temporaryCredential: TemporaryCredential | null;
};

type EditTarget = ManagedUser | null;

export default function UsersIndex({
    users,
    filters,
    roles,
    departments,
    temporaryCredential,
}: Props) {
    const { auth } = usePage().props;

    const [search, setSearch] = useState(filters.search);

    const [role, setRole] = useState(filters.role);

    const [status, setStatus] = useState(filters.status);

    const [department, setDepartment] = useState(filters.department);

    const [formOpen, setFormOpen] = useState(false);

    const [editTarget, setEditTarget] = useState<EditTarget>(null);

    const [statusTarget, setStatusTarget] = useState<ManagedUser | null>(null);

    const [resetTarget, setResetTarget] = useState<ManagedUser | null>(null);

    const [dismissedCredentialKey, setDismissedCredentialKey] = useState<
        string | null
    >(null);

    const [copied, setCopied] = useState(false);

    const [actionProcessing, setActionProcessing] = useState(false);

    const credentialKey =
        temporaryCredential !== null
            ? `${temporaryCredential.user_id}:${temporaryCredential.password}`
            : null;

    const credentialOpen =
        temporaryCredential !== null &&
        credentialKey !== dismissedCredentialKey;

    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm({
            name: '',
            nip: '',
            email: '',
            position: '',
            phone: '',
            role_id: '',
            department_id: '',
        });

    const applyFilters = (event: FormEvent) => {
        event.preventDefault();

        router.get(
            usersIndex.url(),
            {
                search,
                role,
                status,
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
        setRole('');
        setStatus('');
        setDepartment('');

        router.get(usersIndex.url());
    };

    const openCreate = () => {
        reset();
        clearErrors();
        setEditTarget(null);
        setFormOpen(true);
    };

    const openEdit = (user: ManagedUser) => {
        clearErrors();

        setData({
            name: user.name,
            nip: user.nip ?? '',
            email: user.email,
            position: user.position ?? '',
            phone: user.phone ?? '',
            role_id: user.role_id !== null ? String(user.role_id) : '',
            department_id:
                user.department_id !== null ? String(user.department_id) : '',
        });

        setEditTarget(user);
        setFormOpen(true);
    };

    const closeForm = () => {
        if (processing) {
            return;
        }

        setFormOpen(false);
        setEditTarget(null);
        reset();
        clearErrors();
    };

    const submitUser = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,

            onSuccess: () => {
                closeForm();
            },
        };

        if (editTarget) {
            put(updateUser.url(editTarget.id), options);

            return;
        }

        post(storeUser.url(), options);
    };

    const runStatusToggle = () => {
        if (!statusTarget) {
            return;
        }

        setActionProcessing(true);

        router.patch(
            toggleStatus.url(statusTarget.id),
            {},
            {
                preserveScroll: true,

                onSuccess: () => {
                    setStatusTarget(null);
                },

                onFinish: () => {
                    setActionProcessing(false);
                },
            },
        );
    };

    const runPasswordReset = () => {
        if (!resetTarget) {
            return;
        }

        setActionProcessing(true);

        router.post(
            resetPassword.url(resetTarget.id),
            {},
            {
                preserveScroll: true,

                onSuccess: () => {
                    setResetTarget(null);
                },

                onFinish: () => {
                    setActionProcessing(false);
                },
            },
        );
    };

    const dismissCredential = () => {
        if (credentialKey !== null) {
            setDismissedCredentialKey(credentialKey);
        }
    };

    const copyCredential = async () => {
        if (!temporaryCredential) {
            return;
        }

        const text = [
            `Email: ${temporaryCredential.email}`,
            `Kata sandi: ${temporaryCredential.password}`,
        ].join('\n');

        await navigator.clipboard.writeText(text);

        setCopied(true);

        window.setTimeout(() => {
            setCopied(false);
        }, 1500);
    };

    return (
        <>
            <Head title="Pengguna" />

            <div className="flex flex-1 flex-col px-5 py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1400px]">
                    <div className="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                        <div>
                            <h1 className="text-[28px] font-semibold tracking-[-0.035em] text-[#17212B]">
                                Pengguna
                            </h1>

                            <p className="mt-1.5 text-sm text-[#71808C]">
                                Kelola akun, role, unit kerja, dan status akses
                                pengguna sistem.
                            </p>
                        </div>

                        <Button
                            type="button"
                            onClick={openCreate}
                            className="h-10 bg-[#1D5D8F] px-4 text-white shadow-none hover:bg-[#174C76]"
                        >
                            <Plus className="size-4" />
                            Tambah Pengguna
                        </Button>
                    </div>

                    <form
                        onSubmit={applyFilters}
                        className="mt-7 grid gap-3 rounded-[10px] border border-[#DDE3E8] bg-white p-4 lg:grid-cols-[minmax(260px,1fr)_180px_170px_220px_auto_auto]"
                    >
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#8C98A2]" />

                            <Input
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Cari nama, email, NIP, atau jabatan..."
                                className="h-10 border-[#D7DEE4] pl-9 shadow-none"
                            />
                        </div>

                        <select
                            value={role}
                            onChange={(event) => setRole(event.target.value)}
                            className="h-10 rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#52616D]"
                        >
                            <option value="">Semua role</option>

                            {roles.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.name}
                                </option>
                            ))}
                        </select>

                        <select
                            value={status}
                            onChange={(event) => setStatus(event.target.value)}
                            className="h-10 rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#52616D]"
                        >
                            <option value="">Semua status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>

                        <select
                            value={department}
                            onChange={(event) =>
                                setDepartment(event.target.value)
                            }
                            className="h-10 rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm text-[#52616D]"
                        >
                            <option value="">Semua unit</option>

                            {departments.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.name}
                                </option>
                            ))}
                        </select>

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

                    <section className="mt-4 overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                        <div className="border-b border-[#E5E9EC] px-5 py-4">
                            <h2 className="text-[15px] font-semibold text-[#344250]">
                                Daftar Pengguna
                            </h2>

                            <p className="mt-1 text-xs text-[#87949F]">
                                {users.total} pengguna ditemukan
                            </p>
                        </div>

                        {users.data.length === 0 ? (
                            <div className="flex min-h-[320px] flex-col items-center justify-center px-5 text-center">
                                <div className="flex size-12 items-center justify-center rounded-xl bg-[#F0F4F6] text-[#87949F]">
                                    <UserRound className="size-5" />
                                </div>

                                <p className="mt-4 text-sm font-semibold text-[#46545F]">
                                    Pengguna tidak ditemukan
                                </p>

                                <p className="mt-1 text-xs text-[#8B97A1]">
                                    Coba ubah filter atau tambahkan pengguna
                                    baru.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <table className="w-full min-w-[1150px]">
                                        <thead className="bg-[#FAFBFC]">
                                            <tr className="border-b border-[#E6EAED]">
                                                <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Pengguna
                                                </th>

                                                <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Role
                                                </th>

                                                <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Unit / Bidang
                                                </th>

                                                <th className="px-5 py-3 text-left text-[11px] font-medium text-[#87949F] uppercase">
                                                    Jabatan
                                                </th>

                                                <th className="px-5 py-3 text-center text-[11px] font-medium text-[#87949F] uppercase">
                                                    BAST
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
                                            {users.data.map((user) => {
                                                const isSelf =
                                                    auth.user?.id === user.id;

                                                return (
                                                    <tr
                                                        key={user.id}
                                                        className="border-b border-[#EDF0F2] last:border-b-0"
                                                    >
                                                        <td className="px-5 py-4">
                                                            <div className="flex items-center gap-3">
                                                                <div className="flex size-9 shrink-0 items-center justify-center rounded-full bg-[#EAF3FA] text-xs font-semibold text-[#1D5D8F]">
                                                                    {user.name
                                                                        .slice(
                                                                            0,
                                                                            2,
                                                                        )
                                                                        .toUpperCase()}
                                                                </div>

                                                                <div>
                                                                    <div className="flex items-center gap-2">
                                                                        <p className="text-[13px] font-medium text-[#344250]">
                                                                            {
                                                                                user.name
                                                                            }
                                                                        </p>

                                                                        {isSelf && (
                                                                            <span className="rounded-full bg-[#EEF4F8] px-2 py-0.5 text-[9px] font-medium text-[#1D5D8F]">
                                                                                Anda
                                                                            </span>
                                                                        )}
                                                                    </div>

                                                                    <p className="mt-1 text-[11px] text-[#87949F]">
                                                                        {
                                                                            user.email
                                                                        }
                                                                    </p>

                                                                    {user.nip && (
                                                                        <p className="mt-0.5 font-mono text-[10px] text-[#9AA4AC]">
                                                                            NIP{' '}
                                                                            {
                                                                                user.nip
                                                                            }
                                                                        </p>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td className="px-5 py-4">
                                                            <span className="inline-flex rounded-full bg-[#EEF4F8] px-2.5 py-1 text-[10px] font-medium text-[#1D5D8F]">
                                                                {user.role
                                                                    ?.name ??
                                                                    '—'}
                                                            </span>
                                                        </td>

                                                        <td className="px-5 py-4 text-xs text-[#657481]">
                                                            {user.department
                                                                ?.name ?? '—'}
                                                        </td>

                                                        <td className="px-5 py-4 text-xs text-[#657481]">
                                                            {user.position ??
                                                                '—'}
                                                        </td>

                                                        <td className="px-5 py-4 text-center text-xs text-[#657481]">
                                                            {
                                                                user.created_basts_count
                                                            }
                                                        </td>

                                                        <td className="px-5 py-4">
                                                            <span
                                                                className={`inline-flex rounded-full px-2.5 py-1 text-[10px] font-medium ${
                                                                    user.status ===
                                                                    'active'
                                                                        ? 'bg-[#EAF6EF] text-[#287A4B]'
                                                                        : 'bg-[#F1F3F5] text-[#71808C]'
                                                                }`}
                                                            >
                                                                {user.status ===
                                                                'active'
                                                                    ? 'Aktif'
                                                                    : 'Nonaktif'}
                                                            </span>
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
                                                                        href={showUser(
                                                                            user.id,
                                                                        )}
                                                                    >
                                                                        <Eye className="size-3.5" />
                                                                        Lihat
                                                                    </Link>
                                                                </Button>

                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    onClick={() =>
                                                                        openEdit(
                                                                            user,
                                                                        )
                                                                    }
                                                                    className="h-8 px-2 text-xs text-[#52616D]"
                                                                >
                                                                    <PencilLine className="size-3.5" />
                                                                    Edit
                                                                </Button>

                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    disabled={
                                                                        isSelf
                                                                    }
                                                                    onClick={() =>
                                                                        setResetTarget(
                                                                            user,
                                                                        )
                                                                    }
                                                                    className="h-8 px-2 text-xs text-[#7B5A24] disabled:opacity-30"
                                                                >
                                                                    <KeyRound className="size-3.5" />
                                                                    Reset
                                                                </Button>

                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    disabled={
                                                                        isSelf
                                                                    }
                                                                    onClick={() =>
                                                                        setStatusTarget(
                                                                            user,
                                                                        )
                                                                    }
                                                                    className={`h-8 px-2 text-xs disabled:opacity-30 ${
                                                                        user.status ===
                                                                        'active'
                                                                            ? 'text-[#B44949]'
                                                                            : 'text-[#287A4B]'
                                                                    }`}
                                                                >
                                                                    {user.status ===
                                                                    'active' ? (
                                                                        <UserX className="size-3.5" />
                                                                    ) : (
                                                                        <UserCheck className="size-3.5" />
                                                                    )}

                                                                    {user.status ===
                                                                    'active'
                                                                        ? 'Nonaktifkan'
                                                                        : 'Aktifkan'}
                                                                </Button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="flex flex-col gap-3 border-t border-[#E6EAED] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <p className="text-xs text-[#87949F]">
                                        Menampilkan {users.from ?? 0}–
                                        {users.to ?? 0} dari {users.total}
                                    </p>

                                    <div className="flex items-center gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            disabled={!users.prev_page_url}
                                            onClick={() => {
                                                if (users.prev_page_url) {
                                                    router.get(
                                                        users.prev_page_url,
                                                    );
                                                }
                                            }}
                                            className="h-8 border-[#D7DEE4] bg-white px-3 text-xs"
                                        >
                                            Sebelumnya
                                        </Button>

                                        <span className="text-xs text-[#657481]">
                                            {users.current_page} /{' '}
                                            {users.last_page}
                                        </span>

                                        <Button
                                            type="button"
                                            variant="outline"
                                            disabled={!users.next_page_url}
                                            onClick={() => {
                                                if (users.next_page_url) {
                                                    router.get(
                                                        users.next_page_url,
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

            <Dialog
                open={formOpen}
                onOpenChange={(open) => {
                    if (!open) {
                        closeForm();
                    }
                }}
            >
                <DialogContent className="bast-app max-h-[90vh] overflow-y-auto border-[#DDE3E8] bg-white sm:max-w-[620px]">
                    <DialogHeader>
                        <DialogTitle className="text-[#17212B]">
                            {editTarget ? 'Edit Pengguna' : 'Tambah Pengguna'}
                        </DialogTitle>

                        <DialogDescription className="leading-6 text-[#71808C]">
                            {editTarget
                                ? 'Perbarui informasi dan hak akses pengguna.'
                                : 'Buat akun internal baru untuk mengakses sistem BAST.'}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={submitUser}>
                        <div className="grid gap-4 py-2 sm:grid-cols-2">
                            <FormField label="Nama Lengkap" error={errors.name}>
                                <Input
                                    value={data.name}
                                    onChange={(event) =>
                                        setData('name', event.target.value)
                                    }
                                    className="h-10 border-[#D7DEE4] shadow-none"
                                />
                            </FormField>

                            <FormField label="NIP" error={errors.nip}>
                                <Input
                                    value={data.nip}
                                    onChange={(event) =>
                                        setData('nip', event.target.value)
                                    }
                                    placeholder="Opsional"
                                    className="h-10 border-[#D7DEE4] shadow-none"
                                />
                            </FormField>

                            <div className="sm:col-span-2">
                                <FormField label="Email" error={errors.email}>
                                    <Input
                                        type="email"
                                        value={data.email}
                                        onChange={(event) =>
                                            setData('email', event.target.value)
                                        }
                                        className="h-10 border-[#D7DEE4] shadow-none"
                                    />
                                </FormField>
                            </div>

                            <FormField label="Jabatan" error={errors.position}>
                                <Input
                                    value={data.position}
                                    onChange={(event) =>
                                        setData('position', event.target.value)
                                    }
                                    placeholder="Contoh: Pranata Komputer"
                                    className="h-10 border-[#D7DEE4] shadow-none"
                                />
                            </FormField>

                            <FormField
                                label="Nomor Telepon"
                                error={errors.phone}
                            >
                                <Input
                                    value={data.phone}
                                    onChange={(event) =>
                                        setData('phone', event.target.value)
                                    }
                                    placeholder="Opsional"
                                    className="h-10 border-[#D7DEE4] shadow-none"
                                />
                            </FormField>

                            <FormField label="Role" error={errors.role_id}>
                                <select
                                    value={data.role_id}
                                    onChange={(event) =>
                                        setData('role_id', event.target.value)
                                    }
                                    className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm"
                                >
                                    <option value="">Pilih role</option>

                                    {roles.map((item) => (
                                        <option
                                            key={item.id}
                                            value={item.id}
                                            disabled={!item.is_active}
                                        >
                                            {item.name}
                                            {!item.is_active
                                                ? ' — Nonaktif'
                                                : ''}
                                        </option>
                                    ))}
                                </select>
                            </FormField>

                            <FormField
                                label="Unit / Bidang"
                                error={errors.department_id}
                            >
                                <select
                                    value={data.department_id}
                                    onChange={(event) =>
                                        setData(
                                            'department_id',
                                            event.target.value,
                                        )
                                    }
                                    className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm"
                                >
                                    <option value="">Pilih unit</option>

                                    {departments.map((item) => (
                                        <option
                                            key={item.id}
                                            value={item.id}
                                            disabled={!item.is_active}
                                        >
                                            {item.name}
                                            {!item.is_active
                                                ? ' — Nonaktif'
                                                : ''}
                                        </option>
                                    ))}
                                </select>
                            </FormField>
                        </div>

                        {!editTarget && (
                            <div className="mt-2 rounded-lg border border-[#DCE8F0] bg-[#F5F9FC] px-4 py-3">
                                <p className="text-xs leading-5 text-[#526675]">
                                    Kata sandi sementara akan dibuat otomatis
                                    oleh sistem dan hanya ditampilkan sekali
                                    setelah akun berhasil dibuat.
                                </p>
                            </div>
                        )}

                        <DialogFooter className="mt-5">
                            <Button
                                type="button"
                                variant="outline"
                                disabled={processing}
                                onClick={closeForm}
                                className="border-[#D7DEE4] bg-white text-[#52616D]"
                            >
                                Batal
                            </Button>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="bg-[#1D5D8F] text-white hover:bg-[#174C76]"
                            >
                                {processing
                                    ? 'Menyimpan...'
                                    : editTarget
                                      ? 'Simpan Perubahan'
                                      : 'Buat Pengguna'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={statusTarget !== null}
                onOpenChange={(open) => {
                    if (!open && !actionProcessing) {
                        setStatusTarget(null);
                    }
                }}
            >
                <DialogContent className="bast-app border-[#DDE3E8] bg-white sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="text-[#17212B]">
                            {statusTarget?.status === 'active'
                                ? 'Nonaktifkan pengguna?'
                                : 'Aktifkan pengguna?'}
                        </DialogTitle>

                        <DialogDescription className="leading-6 text-[#71808C]">
                            Akun &quot;{statusTarget?.name}&quot; akan{' '}
                            {statusTarget?.status === 'active'
                                ? 'kehilangan akses ke sistem.'
                                : 'dapat kembali mengakses sistem.'}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="rounded-lg border border-[#DCE3E8] bg-[#F7F9FA] px-4 py-3">
                        <p className="text-xs leading-5 text-[#5D6B76]">
                            Riwayat BAST dan aktivitas pengguna tidak akan
                            dihapus.
                        </p>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={actionProcessing}
                            onClick={() => setStatusTarget(null)}
                        >
                            Batal
                        </Button>

                        <Button
                            type="button"
                            disabled={actionProcessing}
                            onClick={runStatusToggle}
                            className={
                                statusTarget?.status === 'active'
                                    ? 'bg-[#B44949] text-white hover:bg-[#9E3D3D]'
                                    : 'bg-[#287A4B] text-white hover:bg-[#21653E]'
                            }
                        >
                            {actionProcessing
                                ? 'Memproses...'
                                : statusTarget?.status === 'active'
                                  ? 'Ya, Nonaktifkan'
                                  : 'Ya, Aktifkan'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog
                open={resetTarget !== null}
                onOpenChange={(open) => {
                    if (!open && !actionProcessing) {
                        setResetTarget(null);
                    }
                }}
            >
                <DialogContent className="bast-app border-[#DDE3E8] bg-white sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="text-[#17212B]">
                            Reset kata sandi?
                        </DialogTitle>

                        <DialogDescription className="leading-6 text-[#71808C]">
                            Sistem akan membuat kata sandi sementara baru untuk
                            &quot;{resetTarget?.name}&quot;.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="rounded-lg border border-[#E7DFCF] bg-[#FFFBF3] px-4 py-3">
                        <p className="text-xs leading-5 text-[#715E39]">
                            Sesi login lama dan konfigurasi autentikasi dua
                            faktor pengguna juga akan direset.
                        </p>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={actionProcessing}
                            onClick={() => setResetTarget(null)}
                        >
                            Batal
                        </Button>

                        <Button
                            type="button"
                            disabled={actionProcessing}
                            onClick={runPasswordReset}
                            className="bg-[#1D5D8F] text-white hover:bg-[#174C76]"
                        >
                            {actionProcessing
                                ? 'Mereset...'
                                : 'Ya, Reset Password'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog
                open={credentialOpen}
                onOpenChange={(open) => {
                    if (!open) {
                        dismissCredential();
                    }
                }}
            >
                <DialogContent className="bast-app border-[#DDE3E8] bg-white sm:max-w-[520px]">
                    <DialogHeader>
                        <div className="mb-2 flex size-11 items-center justify-center rounded-xl bg-[#EAF6EF] text-[#287A4B]">
                            <ShieldCheck className="size-5" />
                        </div>

                        <DialogTitle className="text-[#17212B]">
                            Kredensial Sementara
                        </DialogTitle>

                        <DialogDescription className="leading-6 text-[#71808C]">
                            Simpan atau kirim kredensial ini kepada pengguna.
                            Kata sandi tidak akan ditampilkan kembali setelah
                            dialog ini ditutup dan halaman dimuat ulang.
                        </DialogDescription>
                    </DialogHeader>

                    {temporaryCredential && (
                        <div className="mt-2 rounded-[10px] border border-[#DDE3E8] bg-[#F8FAFB] p-4">
                            <p className="text-xs font-medium text-[#87949F]">
                                Pengguna
                            </p>

                            <p className="mt-1 text-sm font-semibold text-[#344250]">
                                {temporaryCredential.name}
                            </p>

                            <div className="mt-4 grid gap-3">
                                <CredentialRow
                                    label="Email"
                                    value={temporaryCredential.email}
                                />

                                <CredentialRow
                                    label="Kata Sandi"
                                    value={temporaryCredential.password}
                                    mono
                                />
                            </div>
                        </div>
                    )}

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={dismissCredential}
                        >
                            Tutup
                        </Button>

                        <Button
                            type="button"
                            onClick={copyCredential}
                            className="bg-[#1D5D8F] text-white hover:bg-[#174C76]"
                        >
                            {copied ? (
                                <Check className="size-4" />
                            ) : (
                                <Copy className="size-4" />
                            )}

                            {copied ? 'Tersalin' : 'Salin Kredensial'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

function FormField({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>

            {children}

            <InputError message={error} />
        </div>
    );
}

function CredentialRow({
    label,
    value,
    mono = false,
}: {
    label: string;
    value: string;
    mono?: boolean;
}) {
    return (
        <div>
            <p className="text-[10px] font-medium text-[#8B97A1] uppercase">
                {label}
            </p>

            <p
                className={`mt-1 text-sm text-[#344250] ${
                    mono ? 'font-mono font-semibold tracking-wide' : ''
                }`}
            >
                {value}
            </p>
        </div>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Pengguna',
            href: '/users',
        },
    ],
};
