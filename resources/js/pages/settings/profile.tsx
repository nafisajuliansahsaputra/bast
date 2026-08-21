import { Form, Head, usePage } from '@inertiajs/react';
import {
    BadgeCheck,
    Building2,
    IdCard,
    Mail,
    Phone,
    Save,
    UserRound,
} from 'lucide-react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Profile() {
    const { auth } = usePage<PageProps>().props;

    const user = auth.user;

    return (
        <>
            <Head title="Profil" />

            <section className="overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                <div className="border-b border-[#E5E9EC] px-5 py-5 md:px-6">
                    <div className="flex items-start gap-3">
                        <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#EAF3FA] text-[#1D5D8F]">
                            <UserRound className="size-4" />
                        </div>

                        <div>
                            <h2 className="text-[16px] font-semibold text-[#344250]">
                                Informasi Profil
                            </h2>

                            <p className="mt-1 text-xs leading-5 text-[#87949F]">
                                Perbarui informasi kontak akun. Data organisasi
                                dikelola oleh administrator.
                            </p>
                        </div>
                    </div>
                </div>

                <Form
                    {...ProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="p-5 md:p-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-5 md:grid-cols-2">
                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="name">Nama Lengkap</Label>

                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={user.name}
                                        required
                                        autoComplete="name"
                                        className="h-10 border-[#D7DEE4] shadow-none"
                                    />

                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email</Label>

                                    <div className="relative">
                                        <Mail className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#9AA5AE]" />

                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            defaultValue={user.email}
                                            required
                                            autoComplete="username"
                                            className="h-10 border-[#D7DEE4] pl-9 shadow-none"
                                        />
                                    </div>

                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="phone">Nomor Telepon</Label>

                                    <div className="relative">
                                        <Phone className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#9AA5AE]" />

                                        <Input
                                            id="phone"
                                            name="phone"
                                            defaultValue={user.phone ?? ''}
                                            placeholder="Opsional"
                                            autoComplete="tel"
                                            className="h-10 border-[#D7DEE4] pl-9 shadow-none"
                                        />
                                    </div>

                                    <InputError message={errors.phone} />
                                </div>
                            </div>

                            <div className="mt-6 border-t border-[#E9EDF0] pt-6">
                                <div>
                                    <h3 className="text-sm font-semibold text-[#46545F]">
                                        Informasi Organisasi
                                    </h3>

                                    <p className="mt-1 text-xs text-[#8A96A0]">
                                        Informasi berikut hanya dapat diubah
                                        melalui Manajemen Pengguna.
                                    </p>
                                </div>

                                <div className="mt-4 grid gap-3 sm:grid-cols-2">
                                    <ReadOnlyField
                                        icon={IdCard}
                                        label="NIP"
                                        value={user.nip ?? 'Belum diatur'}
                                    />

                                    <ReadOnlyField
                                        icon={BadgeCheck}
                                        label="Jabatan"
                                        value={user.position ?? 'Belum diatur'}
                                    />

                                    <ReadOnlyField
                                        icon={Building2}
                                        label="Unit / Bidang"
                                        value={
                                            user.department?.name ??
                                            'Belum diatur'
                                        }
                                    />

                                    <ReadOnlyField
                                        icon={UserRound}
                                        label="Role"
                                        value={
                                            user.role?.name ?? 'Belum diatur'
                                        }
                                    />
                                </div>
                            </div>

                            <div className="mt-6 flex justify-end border-t border-[#E9EDF0] pt-5">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="update-profile-button"
                                    className="h-10 bg-[#1D5D8F] px-4 text-white shadow-none hover:bg-[#174C76]"
                                >
                                    <Save className="size-4" />

                                    {processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Perubahan'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </section>
        </>
    );
}

function ReadOnlyField({
    icon: Icon,
    label,
    value,
}: {
    icon: typeof UserRound;
    label: string;
    value: string;
}) {
    return (
        <div className="rounded-lg border border-[#E2E7EA] bg-[#F8FAFB] p-4">
            <div className="flex items-center gap-2 text-[#8A96A0]">
                <Icon className="size-3.5" />

                <p className="text-[10px] font-medium tracking-wide uppercase">
                    {label}
                </p>
            </div>

            <p className="mt-2 text-[13px] font-medium text-[#46545F]">
                {value}
            </p>
        </div>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Pengaturan',
            href: '/settings/profile',
        },
        {
            title: 'Profil',
            href: edit(),
        },
    ],
};
