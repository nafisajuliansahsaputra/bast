import { Form, Head, setLayoutProps } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import { KeyRound, ShieldCheck } from 'lucide-react';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import { OTP_MAX_LENGTH } from '@/hooks/use-two-factor-auth';
import { store } from '@/routes/two-factor/login';

export default function TwoFactorChallenge() {
    const [showRecoveryInput, setShowRecoveryInput] = useState(false);
    const [code, setCode] = useState('');

    const authConfigContent = useMemo(() => {
        if (showRecoveryInput) {
            return {
                title: 'Kode Pemulihan',
                description:
                    'Masukkan salah satu kode pemulihan darurat untuk mengakses akun Anda.',
                toggleText: 'Gunakan kode autentikasi',
            };
        }

        return {
            title: 'Verifikasi Dua Faktor',
            description:
                'Masukkan kode autentikasi dari aplikasi authenticator Anda untuk melanjutkan.',
            toggleText: 'Gunakan kode pemulihan',
        };
    }, [showRecoveryInput]);

    setLayoutProps({
        title: authConfigContent.title,
        description: authConfigContent.description,
    });

    const toggleRecoveryMode = (clearErrors: () => void) => {
        setShowRecoveryInput((current) => !current);
        clearErrors();
        setCode('');
    };

    return (
        <>
            <Head title="Autentikasi Dua Faktor" />

            <Form
                {...store.form()}
                className="flex flex-col gap-5"
                resetOnError
                resetOnSuccess={!showRecoveryInput}
            >
                {({ errors, processing, clearErrors }) => (
                    <>
                        {showRecoveryInput ? (
                            <div className="grid gap-2">
                                <div className="flex items-center gap-2 text-sm font-medium text-[#344250]">
                                    <KeyRound className="size-4 text-[#1D5D8F]" />
                                    Kode Pemulihan
                                </div>

                                <Input
                                    name="recovery_code"
                                    type="text"
                                    placeholder="Masukkan kode pemulihan"
                                    autoComplete="one-time-code"
                                    autoFocus
                                    required
                                    className="h-11 border-[#D7DEE4] bg-white font-mono shadow-none placeholder:font-sans placeholder:text-[#A4AFB8] focus-visible:border-[#1D5D8F] focus-visible:ring-[#1D5D8F]/15"
                                />

                                <InputError message={errors.recovery_code} />
                            </div>
                        ) : (
                            <div className="flex flex-col items-center gap-4">
                                <div className="flex size-10 items-center justify-center rounded-lg bg-[#EAF3FA] text-[#1D5D8F]">
                                    <ShieldCheck className="size-5" />
                                </div>

                                <div className="flex w-full justify-center overflow-hidden">
                                    <InputOTP
                                        name="code"
                                        maxLength={OTP_MAX_LENGTH}
                                        value={code}
                                        onChange={setCode}
                                        disabled={processing}
                                        pattern={REGEXP_ONLY_DIGITS}
                                        autoFocus
                                    >
                                        <InputOTPGroup>
                                            {Array.from(
                                                {
                                                    length: OTP_MAX_LENGTH,
                                                },
                                                (_, index) => (
                                                    <InputOTPSlot
                                                        key={index}
                                                        index={index}
                                                        className="size-11 border-[#D7DEE4] text-base"
                                                    />
                                                ),
                                            )}
                                        </InputOTPGroup>
                                    </InputOTP>
                                </div>

                                <InputError message={errors.code} />
                            </div>
                        )}

                        <Button
                            type="submit"
                            disabled={processing}
                            className="h-11 w-full bg-[#1D5D8F] font-medium text-white shadow-none hover:bg-[#174C76]"
                        >
                            {processing && <Spinner />}

                            {processing
                                ? 'Memverifikasi...'
                                : 'Verifikasi dan Lanjutkan'}
                        </Button>

                        <div className="rounded-lg border border-[#DCE8F0] bg-[#F5F9FC] px-4 py-3 text-center">
                            <p className="text-xs leading-5 text-[#657481]">
                                {showRecoveryInput
                                    ? 'Masih memiliki akses ke aplikasi authenticator?'
                                    : 'Tidak dapat menggunakan aplikasi authenticator?'}
                            </p>

                            <button
                                type="button"
                                onClick={() => toggleRecoveryMode(clearErrors)}
                                className="mt-1 text-xs font-medium text-[#1D5D8F] underline underline-offset-4 hover:text-[#174C76]"
                            >
                                {authConfigContent.toggleText}
                            </button>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}
