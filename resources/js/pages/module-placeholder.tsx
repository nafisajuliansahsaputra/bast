import { Head, Link, setLayoutProps } from '@inertiajs/react';
import { Construction } from 'lucide-react';
import { dashboard } from '@/routes';

type Props = {
    title: string;
    description: string;
    href: string;
};

export default function ModulePlaceholder({ title, description, href }: Props) {
    setLayoutProps({
        breadcrumbs: [
            {
                title,
                href,
            },
        ],
    });

    return (
        <>
            <Head title={title} />

            <div className="flex flex-1 flex-col p-5 md:p-8">
                <div className="mx-auto flex w-full max-w-[1600px] flex-1">
                    <div className="flex min-h-[420px] w-full items-center justify-center rounded-[10px] border border-[#DDE3E8] bg-white">
                        <div className="max-w-md px-6 text-center">
                            <div className="mx-auto flex size-12 items-center justify-center rounded-[10px] bg-[#EAF3FA] text-[#1D5D8F]">
                                <Construction
                                    className="size-6"
                                    strokeWidth={1.8}
                                />
                            </div>

                            <h1 className="mt-5 text-xl font-semibold tracking-[-0.02em] text-[#17212B]">
                                {title}
                            </h1>

                            <p className="mt-2 text-sm leading-6 text-[#71808C]">
                                {description}
                            </p>

                            <Link
                                href={dashboard()}
                                className="mt-6 inline-flex h-9 items-center justify-center rounded-lg border border-[#D7DEE4] bg-white px-4 text-sm font-medium text-[#344250] transition-colors hover:bg-[#F5F7F9]"
                            >
                                Kembali ke Dashboard
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
