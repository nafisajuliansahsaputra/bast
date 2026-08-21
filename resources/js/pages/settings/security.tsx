import { Form, Head } from '@inertiajs/react';
import { KeyRound, Save, ShieldCheck } from 'lucide-react';
import { useRef } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/input-error';
import type { Props as ManageTwoFactorProps } from '@/components/manage-two-factor';
import ManageTwoFactor from '@/components/manage-two-factor';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';

type Props = {
    passwordRules: string;
} & ManageTwoFactorProps;

export default function Security(props: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);

    const currentPasswordInput = useRef<HTMLInputElement>(null);

    return (
        <>
            <Head title="Keamanan" />

            <div className="space-y-5">
                <section className="overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                    <div className="border-b border-[#E5E9EC] px-5 py-5 md:px-6">
                        <div className="flex items-start gap-3">
                            <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#EAF3FA] text-[#1D5D8F]">
                                <KeyRound className="size-4" />
                            </div>

                            <div>
                                <h2 className="text-[16px] font-semibold text-[#344250]">
                                    Ubah Kata Sandi
                                </h2>

                                <p className="mt-1 text-xs leading-5 text-[#87949F]">
                                    Gunakan kata sandi yang kuat dan berbeda
                                    dari kata sandi sebelumnya.
                                </p>
                            </div>
                        </div>
                    </div>

                    <Form
                        {...SecurityController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        resetOnError={[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]}
                        resetOnSuccess
                        onError={(errors) => {
                            if (errors.password) {
                                passwordInput.current?.focus();
                            }

                            if (errors.current_password) {
                                currentPasswordInput.current?.focus();
                            }
                        }}
                        className="p-5 md:p-6"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="grid gap-5">
                                    <div className="grid gap-2">
                                        <Label htmlFor="current_password">
                                            Kata Sandi Saat Ini
                                        </Label>

                                        <PasswordInput
                                            id="current_password"
                                            ref={currentPasswordInput}
                                            name="current_password"
                                            className="h-10 border-[#D7DEE4] shadow-none"
                                            autoComplete="current-password"
                                            placeholder="Masukkan kata sandi saat ini"
                                        />

                                        <InputError
                                            message={errors.current_password}
                                        />
                                    </div>

                                    <div className="grid gap-2 sm:grid-cols-2 sm:gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="password">
                                                Kata Sandi Baru
                                            </Label>

                                            <PasswordInput
                                                id="password"
                                                ref={passwordInput}
                                                name="password"
                                                className="h-10 border-[#D7DEE4] shadow-none"
                                                autoComplete="new-password"
                                                placeholder="Masukkan kata sandi baru"
                                                passwordrules={
                                                    props.passwordRules
                                                }
                                            />

                                            <InputError
                                                message={errors.password}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="password_confirmation">
                                                Konfirmasi Kata Sandi
                                            </Label>

                                            <PasswordInput
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                className="h-10 border-[#D7DEE4] shadow-none"
                                                autoComplete="new-password"
                                                placeholder="Ulangi kata sandi baru"
                                                passwordrules={
                                                    props.passwordRules
                                                }
                                            />

                                            <InputError
                                                message={
                                                    errors.password_confirmation
                                                }
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-6 flex justify-end border-t border-[#E9EDF0] pt-5">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        data-test="update-password-button"
                                        className="h-10 bg-[#1D5D8F] px-4 text-white shadow-none hover:bg-[#174C76]"
                                    >
                                        <Save className="size-4" />

                                        {processing
                                            ? 'Menyimpan...'
                                            : 'Perbarui Kata Sandi'}
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </section>

                <section className="overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                    <div className="border-b border-[#E5E9EC] px-5 py-5 md:px-6">
                        <div className="flex items-start gap-3">
                            <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#EAF3FA] text-[#1D5D8F]">
                                <ShieldCheck className="size-4" />
                            </div>

                            <div>
                                <h2 className="text-[16px] font-semibold text-[#344250]">
                                    Autentikasi Dua Faktor
                                </h2>

                                <p className="mt-1 text-xs leading-5 text-[#87949F]">
                                    Tambahkan lapisan keamanan ekstra pada akun
                                    menggunakan aplikasi authenticator.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="p-5 md:p-6">
                        <ManageTwoFactor
                            canManageTwoFactor={props.canManageTwoFactor}
                            requiresConfirmation={props.requiresConfirmation}
                            twoFactorEnabled={props.twoFactorEnabled}
                        />
                    </div>
                </section>
            </div>
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'Pengaturan',
            href: '/settings/profile',
        },
        {
            title: 'Keamanan',
            href: edit(),
        },
    ],
};
