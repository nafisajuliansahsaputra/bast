import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
    demoMode: boolean;
    demoReadOnly: boolean;
};

function normalizeLoginError(message?: string): string | undefined {
    if (!message) {
        return undefined;
    }

    if (message === 'auth.failed' || message === 'auth:failed') {
        return 'Email atau kata sandi tidak sesuai.';
    }

    if (message === 'auth.password' || message === 'auth:password') {
        return 'Kata sandi tidak sesuai.';
    }

    return message;
}

export default function Login({
    status,
    canResetPassword,
    demoMode,
    demoReadOnly,
}: Props) {
    return (
        <>
            <Head title="Masuk" />

            {status && (
                <div className="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-5 text-emerald-700">
                    {status}
                </div>
            )}

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
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
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="email"
                                placeholder="nama@instansi.go.id"
                                className="h-11 border-[#D7DEE4] bg-white shadow-none placeholder:text-[#A4AFB8] focus-visible:border-[#1D5D8F] focus-visible:ring-[#1D5D8F]/15"
                            />

                            <InputError
                                message={normalizeLoginError(errors.email)}
                            />
                        </div>

                        <div className="grid gap-2">
                            <div className="flex items-center justify-between gap-4">
                                <Label
                                    htmlFor="password"
                                    className="text-sm font-medium text-[#344250]"
                                >
                                    Kata sandi
                                </Label>

                                {canResetPassword && (
                                    <TextLink
                                        href={request()}
                                        className="text-xs font-medium text-[#1D5D8F] hover:text-[#174C76]"
                                        tabIndex={5}
                                    >
                                        Lupa kata sandi?
                                    </TextLink>
                                )}
                            </div>

                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                tabIndex={2}
                                autoComplete="current-password"
                                placeholder="Masukkan kata sandi"
                                className="h-11 border-[#D7DEE4] bg-white shadow-none placeholder:text-[#A4AFB8] focus-visible:border-[#1D5D8F] focus-visible:ring-[#1D5D8F]/15"
                            />

                            <InputError
                                message={normalizeLoginError(errors.password)}
                            />
                        </div>

                        <div className="flex items-center gap-2.5">
                            <Checkbox
                                id="remember"
                                name="remember"
                                tabIndex={3}
                                className="border-[#C8D1D8] data-[state=checked]:border-[#1D5D8F] data-[state=checked]:bg-[#1D5D8F]"
                            />

                            <Label
                                htmlFor="remember"
                                className="cursor-pointer text-sm font-normal text-[#596774]"
                            >
                                Ingat saya
                            </Label>
                        </div>

                        <Button
                            type="submit"
                            className="mt-1 h-11 w-full bg-[#1D5D8F] font-medium text-white shadow-none hover:bg-[#174C76]"
                            tabIndex={4}
                            disabled={processing}
                            data-test="login-button"
                        >
                            {processing && <Spinner />}
                            Masuk ke Sistem
                        </Button>
                    </>
                )}
            </Form>

            {demoMode && (
                <div className="mt-6 border-t border-[#E1E6EA] pt-5">
                    <div className="mb-4 text-center">
                        <p className="text-sm font-semibold text-[#344250]">
                            Portfolio Demo
                        </p>

                        <p className="mt-1 text-xs leading-5 text-[#6F7D89]">
                            Masuk langsung untuk melihat dashboard, dokumen
                            BAST, arsip, data master, dan activity log tanpa
                            memasukkan akun.
                        </p>
                    </div>

                    <Form action="/demo-login" method="post">
                        {({ processing }) => (
                            <Button
                                type="submit"
                                variant="outline"
                                className="h-11 w-full border-[#1D5D8F] bg-white font-medium text-[#1D5D8F] shadow-none hover:bg-[#EAF3FA] hover:text-[#174C76]"
                                disabled={processing}
                                data-test="demo-login-button"
                            >
                                {processing && <Spinner />}

                                {processing
                                    ? 'Membuka Demo...'
                                    : 'Explore Demo'}
                            </Button>
                        )}
                    </Form>

                    {demoReadOnly && (
                        <p className="mt-3 text-center text-[11px] leading-4 text-[#8A96A1]">
                            Mode demo bersifat read-only dan menggunakan data
                            sintetis untuk kebutuhan portfolio.
                        </p>
                    )}
                </div>
            )}
        </>
    );
}

Login.layout = {
    title: 'Masuk ke BAST',
    description:
        'Gunakan akun yang telah diberikan administrator untuk mengakses sistem.',
};
