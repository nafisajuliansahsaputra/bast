import { Form } from '@inertiajs/react';
import { ShieldCheck, ShieldOff } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import TwoFactorRecoveryCodes from '@/components/two-factor-recovery-codes';
import TwoFactorSetupModal from '@/components/two-factor-setup-modal';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';
import { disable, enable } from '@/routes/two-factor';

export type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

export default function ManageTwoFactor(props: Props) {
    const requiresConfirmation = props.requiresConfirmation ?? false;

    const twoFactorEnabled = props.twoFactorEnabled ?? false;

    const {
        qrCodeSvg,
        hasSetupData,
        manualSetupKey,
        clearSetupData,
        clearTwoFactorAuthData,
        fetchSetupData,
        recoveryCodesList,
        fetchRecoveryCodes,
        errors,
    } = useTwoFactorAuth();

    const [showSetupModal, setShowSetupModal] = useState(false);

    const prevTwoFactorEnabled = useRef(twoFactorEnabled);

    useEffect(() => {
        if (prevTwoFactorEnabled.current && !twoFactorEnabled) {
            clearTwoFactorAuthData();
        }

        prevTwoFactorEnabled.current = twoFactorEnabled;
    }, [twoFactorEnabled, clearTwoFactorAuthData]);

    if (!(props.canManageTwoFactor ?? false)) {
        return (
            <div className="rounded-lg border border-dashed border-[#DDE3E8] px-4 py-6 text-center">
                <p className="text-sm font-medium text-[#657481]">
                    Autentikasi dua faktor tidak tersedia
                </p>

                <p className="mt-1 text-xs text-[#929DA6]">
                    Fitur ini sedang dinonaktifkan pada konfigurasi sistem.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-5">
            {twoFactorEnabled ? (
                <>
                    <div className="flex flex-col gap-4 rounded-lg border border-[#DCE9E1] bg-[#F4FAF6] p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div className="flex items-center gap-2">
                                <ShieldCheck className="size-4 text-[#287A4B]" />

                                <p className="text-sm font-semibold text-[#344250]">
                                    2FA aktif
                                </p>
                            </div>

                            <p className="mt-2 max-w-[620px] text-xs leading-5 text-[#657481]">
                                Saat login, Anda akan diminta memasukkan kode
                                keamanan dari aplikasi authenticator.
                            </p>
                        </div>

                        <Form {...disable.form()}>
                            {({ processing }) => (
                                <Button
                                    variant="outline"
                                    type="submit"
                                    disabled={processing}
                                    className="shrink-0 border-[#E2CACA] bg-white text-[#B44949] shadow-none hover:bg-[#FFF7F7] hover:text-[#A33E3E]"
                                >
                                    <ShieldOff className="size-4" />

                                    {processing
                                        ? 'Menonaktifkan...'
                                        : 'Nonaktifkan 2FA'}
                                </Button>
                            )}
                        </Form>
                    </div>

                    <TwoFactorRecoveryCodes
                        recoveryCodesList={recoveryCodesList}
                        fetchRecoveryCodes={fetchRecoveryCodes}
                        errors={errors}
                    />
                </>
            ) : (
                <div className="flex flex-col gap-4 rounded-lg border border-[#DDE3E8] bg-[#F8FAFB] p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold text-[#344250]">
                            2FA belum aktif
                        </p>

                        <p className="mt-2 max-w-[620px] text-xs leading-5 text-[#657481]">
                            Aktifkan autentikasi dua faktor untuk membantu
                            melindungi akun jika kata sandi diketahui pihak
                            lain.
                        </p>
                    </div>

                    <div className="shrink-0">
                        {hasSetupData ? (
                            <Button
                                type="button"
                                onClick={() => setShowSetupModal(true)}
                                className="bg-[#1D5D8F] text-white shadow-none hover:bg-[#174C76]"
                            >
                                <ShieldCheck className="size-4" />
                                Lanjutkan Setup
                            </Button>
                        ) : (
                            <Form
                                {...enable.form()}
                                onSuccess={() => setShowSetupModal(true)}
                            >
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-[#1D5D8F] text-white shadow-none hover:bg-[#174C76]"
                                    >
                                        <ShieldCheck className="size-4" />

                                        {processing
                                            ? 'Mengaktifkan...'
                                            : 'Aktifkan 2FA'}
                                    </Button>
                                )}
                            </Form>
                        )}
                    </div>
                </div>
            )}

            <TwoFactorSetupModal
                isOpen={showSetupModal}
                onClose={() => setShowSetupModal(false)}
                requiresConfirmation={requiresConfirmation}
                twoFactorEnabled={twoFactorEnabled}
                qrCodeSvg={qrCodeSvg}
                manualSetupKey={manualSetupKey}
                clearSetupData={clearSetupData}
                fetchSetupData={fetchSetupData}
                errors={errors}
            />
        </div>
    );
}
