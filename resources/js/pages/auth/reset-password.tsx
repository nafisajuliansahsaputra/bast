import { Form, Head } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { update } from '@/routes/password';

type Props = {
    token: string;
    email: string;
    passwordRules: string;
};

export default function ResetPassword({ token, email, passwordRules }: Props) {
    return (
        <>
            <Head title="Atur Ulang Kata Sandi" />

            <Form
                {...update.form()}
                transform={(data) => ({
                    ...data,
                    token,
                    email,
                })}
                resetOnSuccess={['password', 'password_confirmation']}
                className="flex flex-col gap-5"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label
                                htmlFor="email"
                                className="text-sm font-medium text-[#344250]"
                            >
                                Email
                            </Label>

                            <Input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="email"
                                value={email}
                                readOnly
                                className="h-11 border-[#D7DEE4] bg-[#F7F9FA] text-[#657481] shadow-none"
                            />

                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label
                                htmlFor="password"
                                className="text-sm font-medium text-[#344250]"
                            >
                                Kata Sandi Baru
                            </Label>

                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                autoFocus
                                autoComplete="new-password"
                                placeholder="Masukkan kata sandi baru"
                                passwordrules={passwordRules}
                                className="h-11 border-[#D7DEE4] bg-white shadow-none placeholder:text-[#A4AFB8] focus-visible:border-[#1D5D8F] focus-visible:ring-[#1D5D8F]/15"
                            />

                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label
                                htmlFor="password_confirmation"
                                className="text-sm font-medium text-[#344250]"
                            >
                                Konfirmasi Kata Sandi
                            </Label>

                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                autoComplete="new-password"
                                placeholder="Ulangi kata sandi baru"
                                passwordrules={passwordRules}
                                className="h-11 border-[#D7DEE4] bg-white shadow-none placeholder:text-[#A4AFB8] focus-visible:border-[#1D5D8F] focus-visible:ring-[#1D5D8F]/15"
                            />

                            <InputError
                                message={errors.password_confirmation}
                            />
                        </div>

                        <div className="rounded-lg border border-[#DCE8F0] bg-[#F5F9FC] px-4 py-3">
                            <p className="text-xs leading-5 text-[#526675]">
                                Gunakan kata sandi yang kuat dan berbeda dari
                                kata sandi yang pernah digunakan sebelumnya.
                            </p>
                        </div>

                        <Button
                            type="submit"
                            disabled={processing}
                            data-test="reset-password-button"
                            className="h-11 w-full bg-[#1D5D8F] font-medium text-white shadow-none hover:bg-[#174C76]"
                        >
                            {processing && <Spinner />}
                            Simpan Kata Sandi Baru
                        </Button>

                        <div className="flex justify-center pt-1">
                            <TextLink
                                href={login()}
                                className="inline-flex items-center gap-1.5 text-sm font-medium text-[#657481] hover:text-[#1D5D8F]"
                            >
                                <ArrowLeft className="size-3.5" />
                                Kembali ke halaman masuk
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

ResetPassword.layout = {
    title: 'Atur Ulang Kata Sandi',
    description: 'Buat kata sandi baru untuk memulihkan akses ke akun Anda.',
};
