import { Form, Head } from '@inertiajs/react';
import { CheckCircle2, LogOut } from 'lucide-react';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <>
            <Head title="Verifikasi Email" />

            {status === 'verification-link-sent' && (
                <div className="mb-5 flex items-start gap-3 rounded-lg border border-[#CFE6D8] bg-[#F0F8F3] px-4 py-3">
                    <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-[#287A4B]" />

                    <p className="text-sm leading-5 text-[#287A4B]">
                        Tautan verifikasi baru telah dikirim ke alamat email
                        akun Anda.
                    </p>
                </div>
            )}

            <Form {...send.form()} className="flex flex-col gap-5">
                {({ processing }) => (
                    <>
                        <div className="rounded-lg border border-[#DCE8F0] bg-[#F5F9FC] px-4 py-3">
                            <p className="text-xs leading-5 text-[#526675]">
                                Periksa kotak masuk email Anda dan klik tautan
                                verifikasi sebelum melanjutkan ke sistem.
                            </p>
                        </div>

                        <Button
                            type="submit"
                            disabled={processing}
                            className="h-11 w-full bg-[#1D5D8F] font-medium text-white shadow-none hover:bg-[#174C76]"
                        >
                            {processing && <Spinner />}

                            {processing
                                ? 'Mengirim...'
                                : 'Kirim Ulang Email Verifikasi'}
                        </Button>

                        <div className="flex justify-center">
                            <TextLink
                                href={logout()}
                                className="inline-flex items-center gap-1.5 text-sm font-medium text-[#657481] hover:text-[#1D5D8F]"
                            >
                                <LogOut className="size-3.5" />
                                Keluar dari akun
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verifikasi Email',
    description:
        'Verifikasi alamat email Anda untuk memastikan akun dapat digunakan dengan aman.',
};
