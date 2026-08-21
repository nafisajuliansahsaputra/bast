import { Link } from '@inertiajs/react';
import { Fragment } from 'react';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function Breadcrumbs({
    breadcrumbs,
}: {
    breadcrumbs: BreadcrumbItemType[];
}) {
    if (breadcrumbs.length === 0) {
        return null;
    }

    const currentPage = breadcrumbs[breadcrumbs.length - 1];

    return (
        <div className="min-w-0 flex-1">
            <div className="min-w-0 sm:hidden">
                <p className="truncate text-sm font-medium text-[#344250]">
                    {currentPage.title}
                </p>
            </div>

            <div className="hidden min-w-0 sm:block">
                <Breadcrumb>
                    <BreadcrumbList className="flex-nowrap text-sm text-[#71808C]">
                        {breadcrumbs.map((item, index) => {
                            const isLast = index === breadcrumbs.length - 1;

                            return (
                                <Fragment key={index}>
                                    <BreadcrumbItem className="min-w-0">
                                        {isLast ? (
                                            <BreadcrumbPage className="max-w-[320px] truncate font-medium text-[#344250]">
                                                {item.title}
                                            </BreadcrumbPage>
                                        ) : (
                                            <BreadcrumbLink
                                                asChild
                                                className="max-w-[240px] truncate text-[#71808C] hover:text-[#1D5D8F]"
                                            >
                                                <Link href={item.href}>
                                                    {item.title}
                                                </Link>
                                            </BreadcrumbLink>
                                        )}
                                    </BreadcrumbItem>

                                    {!isLast && (
                                        <BreadcrumbSeparator className="shrink-0 text-[#A5AFB7]" />
                                    )}
                                </Fragment>
                            );
                        })}
                    </BreadcrumbList>
                </Breadcrumb>
            </div>
        </div>
    );
}
