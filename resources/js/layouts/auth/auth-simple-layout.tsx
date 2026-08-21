import { Archive, ShieldCheck, Workflow } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import type { AuthLayoutProps } from '@/types';

const benefits = [
    {
        icon: Workflow,
        title: 'Alur dokumen terstruktur',
        description:
            'Kelola proses berita acara dari draft hingga pengarsipan.',
    },
    {
        icon: ShieldCheck,
        title: 'Akses berbasis peran',
        description:
            'Hak akses Super Admin, Admin, dan Staff dikelola secara terpisah.',
    },
    {
        icon: Archive,
        title: 'Arsip terpusat',
        description:
            'Dokumen serah terima tersimpan rapi dan mudah ditelusuri kembali.',
    },
];

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <main className="min-h-svh bg-[#F5F7F9] text-[#17212B]">
            <div className="grid min-h-svh lg:grid-cols-[minmax(0,0.9fr)_minmax(560px,1.1fr)]">
                <section className="relative hidden overflow-hidden bg-[#123C5E] px-12 py-10 text-white lg:flex lg:flex-col xl:px-16 xl:py-12">
                    <div
                        className="pointer-events-none absolute inset-0 opacity-[0.06]"
                        aria-hidden="true"
                        style={{
                            backgroundImage:
                                'linear-gradient(rgba(255,255,255,0.55) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.55) 1px, transparent 1px)',
                            backgroundSize: '48px 48px',
                        }}
                    />

                    <div className="relative z-10">
                        <div className="flex items-center gap-3">
                            <div className="flex size-11 items-center justify-center rounded-[10px] bg-white/10 text-white ring-1 ring-white/15">
                                <AppLogoIcon className="size-6" />
                            </div>

                            <div>
                                <div className="text-lg font-semibold tracking-[-0.02em]">
                                    BAST
                                </div>

                                <div className="text-xs text-blue-100/70">
                                    Digital Handover Management System
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="relative z-10 my-auto max-w-xl py-16">
                        <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.07] px-3 py-1.5 text-xs font-medium text-blue-50">
                            Internal Document Management
                        </div>

                        <h1 className="max-w-lg text-4xl leading-[1.15] font-semibold tracking-[-0.035em] xl:text-[44px]">
                            Kelola berita acara serah terima dengan lebih
                            terstruktur.
                        </h1>

                        <p className="mt-5 max-w-lg text-[15px] leading-7 text-blue-100/75">
                            Sistem terpusat untuk membuat, mengelola,
                            memfinalisasi, dan mengarsipkan dokumen Berita Acara
                            Serah Terima.
                        </p>

                        <div className="mt-10 grid gap-5">
                            {benefits.map((benefit) => {
                                const Icon = benefit.icon;

                                return (
                                    <div
                                        key={benefit.title}
                                        className="flex items-start gap-4"
                                    >
                                        <div className="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/[0.08]">
                                            <Icon
                                                className="size-[18px]"
                                                strokeWidth={1.8}
                                            />
                                        </div>

                                        <div>
                                            <p className="text-sm font-medium text-white">
                                                {benefit.title}
                                            </p>

                                            <p className="mt-1 max-w-md text-[13px] leading-5 text-blue-100/65">
                                                {benefit.description}
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    <div className="relative z-10 border-t border-white/10 pt-6">
                        <p className="text-xs font-medium text-blue-50/80">
                            Independent reconstruction
                        </p>

                        <p className="mt-1 text-[11px] leading-5 text-blue-100/45">
                            Originally developed during an internship at
                            Diskominfo Kabupaten Cianjur · 2024
                        </p>

                        <p className="mt-1 text-[11px] text-blue-100/45">
                            Designed &amp; developed by NATSX
                        </p>
                    </div>
                </section>

                <section className="flex min-h-svh flex-col">
                    <div className="flex items-center px-6 py-6 lg:hidden">
                        <div className="flex items-center gap-3">
                            <div className="flex size-10 items-center justify-center rounded-[10px] bg-[#1D5D8F] text-white">
                                <AppLogoIcon className="size-[21px]" />
                            </div>

                            <div>
                                <p className="font-semibold tracking-[-0.02em] text-[#17212B]">
                                    BAST
                                </p>

                                <p className="text-[11px] text-[#71808D]">
                                    Digital Handover Management System
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-1 items-center justify-center px-6 py-10 sm:px-10 lg:px-16">
                        <div className="w-full max-w-[430px]">
                            <div className="mb-8">
                                <div className="mb-5 hidden size-12 items-center justify-center rounded-[10px] bg-[#EAF3FA] text-[#1D5D8F] lg:flex">
                                    <AppLogoIcon className="size-6" />
                                </div>

                                <h2 className="text-[28px] leading-tight font-semibold tracking-[-0.03em] text-[#17212B]">
                                    {title}
                                </h2>

                                {description && (
                                    <p className="mt-2.5 max-w-sm text-sm leading-6 text-[#6D7B87]">
                                        {description}
                                    </p>
                                )}
                            </div>

                            <div className="rounded-[12px] border border-[#DDE3E8] bg-white p-6 shadow-[0_1px_2px_rgba(23,33,43,0.03)] sm:p-7">
                                {children}
                            </div>

                            <div className="mt-6 text-center">
                                <p className="text-xs leading-5 text-[#8A96A0]">
                                    Akses sistem hanya diperuntukkan bagi
                                    pengguna yang memiliki akun terdaftar.
                                </p>

                                <p className="mt-2 text-[10px] text-[#A1AAB2]">
                                    Independent portfolio reconstruction · NATSX
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    );
}
