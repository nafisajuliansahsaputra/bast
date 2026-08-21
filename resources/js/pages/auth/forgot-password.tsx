import { Form, Head } from '@inertiajs/react';
import { ArrowLeft, LoaderCircle } from 'lucide-react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { login } from '@/routes';
import { email } from '@/routes/password';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Lupa Kata Sandi" />

            {status && (
                <div className="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-5 text-emerald-700">
                    {status}
                </div>
            )}

            <Form {...email.form()} className="flex flex-col gap-5">
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
                                autoComplete="email"
                                placeholder="nama@instansi.go.id"
                                className="h-11 border-[#D7DEE4] bg-white shadow-none placeholder:text-[#A4AFB8] focus-visible:border-[#1D5D8F] focus-visible:ring-[#1D5D8F]/15"
                            />

                            <InputError message={errors.email} />
                        </div>

                        <Button
                            type="submit"
                            disabled={processing}
                            data-test="email-password-reset-link-button"
                            className="mt-1 h-11 w-full bg-[#1D5D8F] font-medium text-white shadow-none hover:bg-[#174C76]"
                        >
                            {processing && (
                                <LoaderCircle className="size-4 animate-spin" />
                            )}
                            Kirim Tautan Reset Kata Sandi
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

ForgotPassword.layout = {
    title: 'Lupa Kata Sandi',
    description:
        'Masukkan email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.',
};
