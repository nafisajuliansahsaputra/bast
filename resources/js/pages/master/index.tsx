import { Head, router, useForm } from '@inertiajs/react';
import {
    Building2,
    Database,
    FileType2,
    PencilLine,
    Plus,
    Power,
    PowerOff,
    Ruler,
    Search,
    Tags,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useMemo, useState } from 'react';
import {
    store as storeBastType,
    toggle as toggleBastType,
    update as updateBastType,
} from '@/actions/App/Http/Controllers/Master/BastTypeController';
import {
    store as storeDepartment,
    toggle as toggleDepartment,
    update as updateDepartment,
} from '@/actions/App/Http/Controllers/Master/DepartmentController';
import {
    store as storeItemCategory,
    toggle as toggleItemCategory,
    update as updateItemCategory,
} from '@/actions/App/Http/Controllers/Master/ItemCategoryController';
import {
    store as storeUnit,
    toggle as toggleUnit,
    update as updateUnit,
} from '@/actions/App/Http/Controllers/Master/UnitController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type MasterTab = 'bast-types' | 'departments' | 'item-categories' | 'units';

type BaseMaster = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
};

type BastType = BaseMaster & {
    slug: string;
    basts_count: number;
};

type Department = BaseMaster & {
    code: string;
    users_count: number;
    basts_count: number;
};

type ItemCategory = BaseMaster & {
    slug: string;
    bast_items_count: number;
};

type Unit = BaseMaster & {
    symbol: string | null;
    bast_items_count: number;
};

type Props = {
    bastTypes: BastType[];
    departments: Department[];
    itemCategories: ItemCategory[];
    units: Unit[];
};

type EditTarget = {
    id: number;
    name: string;
    description: string | null;
    code?: string;
    symbol?: string | null;
};

type ToggleTarget = {
    id: number;
    name: string;
    is_active: boolean;
    type: MasterTab;
};

type MobileDetail = {
    label: string;
    value: string;
    mono?: boolean;
};

const tabConfig: Array<{
    key: MasterTab;
    label: string;
    description: string;
    icon: typeof Database;
}> = [
    {
        key: 'bast-types',
        label: 'Jenis BAST',
        description: 'Jenis dokumen berita acara.',
        icon: FileType2,
    },
    {
        key: 'departments',
        label: 'Unit / Bidang',
        description: 'Unit organisasi dan bidang kerja.',
        icon: Building2,
    },
    {
        key: 'item-categories',
        label: 'Kategori Item',
        description: 'Klasifikasi objek serah terima.',
        icon: Tags,
    },
    {
        key: 'units',
        label: 'Satuan',
        description: 'Satuan jumlah item serah terima.',
        icon: Ruler,
    },
];

export default function MasterIndex({
    bastTypes,
    departments,
    itemCategories,
    units,
}: Props) {
    const [activeTab, setActiveTab] = useState<MasterTab>('bast-types');
    const [search, setSearch] = useState('');
    const [formOpen, setFormOpen] = useState(false);
    const [editTarget, setEditTarget] = useState<EditTarget | null>(null);
    const [toggleTarget, setToggleTarget] = useState<ToggleTarget | null>(null);
    const [toggling, setToggling] = useState(false);

    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm({
            name: '',
            code: '',
            symbol: '',
            description: '',
        });

    const currentConfig =
        tabConfig.find((item) => item.key === activeTab) ?? tabConfig[0];

    const normalizedSearch = search.trim().toLowerCase();

    const filteredBastTypes = useMemo(() => {
        return bastTypes.filter((item) =>
            matchesSearch(
                normalizedSearch,
                item.name,
                item.description,
                item.slug,
            ),
        );
    }, [bastTypes, normalizedSearch]);

    const filteredDepartments = useMemo(() => {
        return departments.filter((item) =>
            matchesSearch(
                normalizedSearch,
                item.name,
                item.description,
                item.code,
            ),
        );
    }, [departments, normalizedSearch]);

    const filteredItemCategories = useMemo(() => {
        return itemCategories.filter((item) =>
            matchesSearch(
                normalizedSearch,
                item.name,
                item.description,
                item.slug,
            ),
        );
    }, [itemCategories, normalizedSearch]);

    const filteredUnits = useMemo(() => {
        return units.filter((item) =>
            matchesSearch(
                normalizedSearch,
                item.name,
                item.description,
                item.symbol,
            ),
        );
    }, [units, normalizedSearch]);

    const openCreate = () => {
        reset();
        clearErrors();
        setEditTarget(null);
        setFormOpen(true);
    };

    const openEdit = (target: EditTarget) => {
        clearErrors();

        setData({
            name: target.name,
            code: target.code ?? '',
            symbol: target.symbol ?? '',
            description: target.description ?? '',
        });

        setEditTarget(target);
        setFormOpen(true);
    };

    const closeForm = () => {
        if (processing) {
            return;
        }

        setFormOpen(false);
        setEditTarget(null);
        reset();
        clearErrors();
    };

    const openToggle = (item: BaseMaster, type: MasterTab) => {
        setToggleTarget({
            id: item.id,
            name: item.name,
            is_active: item.is_active,
            type,
        });
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,

            onSuccess: () => {
                closeForm();
            },
        };

        if (editTarget) {
            if (activeTab === 'bast-types') {
                put(updateBastType.url(editTarget.id), options);

                return;
            }

            if (activeTab === 'departments') {
                put(updateDepartment.url(editTarget.id), options);

                return;
            }

            if (activeTab === 'item-categories') {
                put(updateItemCategory.url(editTarget.id), options);

                return;
            }

            put(updateUnit.url(editTarget.id), options);

            return;
        }

        if (activeTab === 'bast-types') {
            post(storeBastType.url(), options);

            return;
        }

        if (activeTab === 'departments') {
            post(storeDepartment.url(), options);

            return;
        }

        if (activeTab === 'item-categories') {
            post(storeItemCategory.url(), options);

            return;
        }

        post(storeUnit.url(), options);
    };

    const runToggle = () => {
        if (!toggleTarget) {
            return;
        }

        setToggling(true);

        const options = {
            preserveScroll: true,

            onSuccess: () => {
                setToggleTarget(null);
            },

            onFinish: () => {
                setToggling(false);
            },
        };

        if (toggleTarget.type === 'bast-types') {
            router.patch(toggleBastType.url(toggleTarget.id), {}, options);

            return;
        }

        if (toggleTarget.type === 'departments') {
            router.patch(toggleDepartment.url(toggleTarget.id), {}, options);

            return;
        }

        if (toggleTarget.type === 'item-categories') {
            router.patch(toggleItemCategory.url(toggleTarget.id), {}, options);

            return;
        }

        router.patch(toggleUnit.url(toggleTarget.id), {}, options);
    };

    const totalCurrent =
        activeTab === 'bast-types'
            ? filteredBastTypes.length
            : activeTab === 'departments'
              ? filteredDepartments.length
              : activeTab === 'item-categories'
                ? filteredItemCategories.length
                : filteredUnits.length;

    return (
        <>
            <Head title="Data Master" />

            <div className="flex min-w-0 flex-1 flex-col px-4 py-5 sm:px-5 sm:py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1400px] min-w-0">
                    <div className="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                        <div className="min-w-0">
                            <h1 className="text-[26px] font-semibold tracking-[-0.035em] text-[#17212B] sm:text-[28px]">
                                Data Master
                            </h1>

                            <p className="mt-1.5 text-sm leading-6 text-[#71808C]">
                                Kelola data referensi yang digunakan pada proses
                                Berita Acara Serah Terima.
                            </p>
                        </div>

                        <Button
                            type="button"
                            onClick={openCreate}
                            className="h-10 w-full bg-[#1D5D8F] px-4 text-white shadow-none hover:bg-[#174C76] lg:w-auto"
                        >
                            <Plus className="size-4" />
                            Tambah {currentConfig.label}
                        </Button>
                    </div>

                    <section className="mt-6 grid gap-3 sm:grid-cols-2 md:mt-7 lg:grid-cols-4">
                        {tabConfig.map((tab) => {
                            const Icon = tab.icon;
                            const active = activeTab === tab.key;

                            return (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => {
                                        setActiveTab(tab.key);
                                        setSearch('');
                                    }}
                                    className={`min-w-0 rounded-[10px] border p-4 text-left transition ${
                                        active
                                            ? 'border-[#9FC0D7] bg-[#EEF5FA]'
                                            : 'border-[#DDE3E8] bg-white hover:border-[#C8D3DB] hover:bg-[#FAFBFC]'
                                    }`}
                                >
                                    <div className="flex min-w-0 items-start gap-3">
                                        <div
                                            className={`flex size-9 shrink-0 items-center justify-center rounded-lg ${
                                                active
                                                    ? 'bg-[#1D5D8F] text-white'
                                                    : 'bg-[#F0F4F6] text-[#71808C]'
                                            }`}
                                        >
                                            <Icon className="size-4" />
                                        </div>

                                        <div className="min-w-0">
                                            <p
                                                className={`text-sm font-semibold break-words ${
                                                    active
                                                        ? 'text-[#1D5D8F]'
                                                        : 'text-[#344250]'
                                                }`}
                                            >
                                                {tab.label}
                                            </p>

                                            <p className="mt-1 text-[11px] leading-4 break-words text-[#87949F]">
                                                {tab.description}
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            );
                        })}
                    </section>

                    <section className="mt-4 min-w-0 overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white">
                        <div className="flex flex-col gap-4 border-b border-[#E5E9EC] p-4 sm:p-5 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 className="text-[15px] font-semibold text-[#344250]">
                                    {currentConfig.label}
                                </h2>

                                <p className="mt-1 text-xs text-[#87949F]">
                                    {totalCurrent} data ditemukan
                                </p>
                            </div>

                            <div className="relative w-full md:max-w-[340px]">
                                <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#8C98A2]" />

                                <Input
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder={`Cari ${currentConfig.label.toLowerCase()}...`}
                                    className="h-10 border-[#D7DEE4] pl-9 shadow-none"
                                />
                            </div>
                        </div>

                        {activeTab === 'bast-types' && (
                            <>
                                <MobileMasterList
                                    empty={filteredBastTypes.length === 0}
                                >
                                    {filteredBastTypes.map((item) => (
                                        <MasterMobileCard
                                            key={item.id}
                                            name={item.name}
                                            description={item.description}
                                            active={item.is_active}
                                            details={[
                                                {
                                                    label: 'Slug',
                                                    value: item.slug,
                                                    mono: true,
                                                },
                                                {
                                                    label: 'Dipakai',
                                                    value: `${item.basts_count} BAST`,
                                                },
                                            ]}
                                            onEdit={() => openEdit(item)}
                                            onToggle={() =>
                                                openToggle(item, 'bast-types')
                                            }
                                        />
                                    ))}
                                </MobileMasterList>

                                <DesktopMasterTable
                                    headers={[
                                        'Nama',
                                        'Slug',
                                        'Dipakai',
                                        'Status',
                                        'Aksi',
                                    ]}
                                    empty={filteredBastTypes.length === 0}
                                >
                                    {filteredBastTypes.map((item) => (
                                        <tr
                                            key={item.id}
                                            className="border-b border-[#EDF0F2] last:border-b-0"
                                        >
                                            <MasterNameCell
                                                name={item.name}
                                                description={item.description}
                                            />

                                            <td className="px-5 py-4 font-mono text-xs text-[#71808C]">
                                                {item.slug}
                                            </td>

                                            <td className="px-5 py-4 text-xs text-[#657481]">
                                                {item.basts_count} BAST
                                            </td>

                                            <StatusCell
                                                active={item.is_active}
                                            />

                                            <ActionCell
                                                active={item.is_active}
                                                onEdit={() => openEdit(item)}
                                                onToggle={() =>
                                                    openToggle(
                                                        item,
                                                        'bast-types',
                                                    )
                                                }
                                            />
                                        </tr>
                                    ))}
                                </DesktopMasterTable>
                            </>
                        )}

                        {activeTab === 'departments' && (
                            <>
                                <MobileMasterList
                                    empty={filteredDepartments.length === 0}
                                >
                                    {filteredDepartments.map((item) => (
                                        <MasterMobileCard
                                            key={item.id}
                                            name={item.name}
                                            description={item.description}
                                            active={item.is_active}
                                            details={[
                                                {
                                                    label: 'Kode',
                                                    value: item.code,
                                                    mono: true,
                                                },
                                                {
                                                    label: 'Pengguna',
                                                    value: `${item.users_count} pengguna`,
                                                },
                                                {
                                                    label: 'BAST',
                                                    value: `${item.basts_count} dokumen`,
                                                },
                                            ]}
                                            onEdit={() => openEdit(item)}
                                            onToggle={() =>
                                                openToggle(item, 'departments')
                                            }
                                        />
                                    ))}
                                </MobileMasterList>

                                <DesktopMasterTable
                                    headers={[
                                        'Unit / Bidang',
                                        'Kode',
                                        'Pengguna',
                                        'BAST',
                                        'Status',
                                        'Aksi',
                                    ]}
                                    empty={filteredDepartments.length === 0}
                                >
                                    {filteredDepartments.map((item) => (
                                        <tr
                                            key={item.id}
                                            className="border-b border-[#EDF0F2] last:border-b-0"
                                        >
                                            <MasterNameCell
                                                name={item.name}
                                                description={item.description}
                                            />

                                            <td className="px-5 py-4 font-mono text-xs font-medium text-[#52616D]">
                                                {item.code}
                                            </td>

                                            <td className="px-5 py-4 text-xs text-[#657481]">
                                                {item.users_count}
                                            </td>

                                            <td className="px-5 py-4 text-xs text-[#657481]">
                                                {item.basts_count}
                                            </td>

                                            <StatusCell
                                                active={item.is_active}
                                            />

                                            <ActionCell
                                                active={item.is_active}
                                                onEdit={() => openEdit(item)}
                                                onToggle={() =>
                                                    openToggle(
                                                        item,
                                                        'departments',
                                                    )
                                                }
                                            />
                                        </tr>
                                    ))}
                                </DesktopMasterTable>
                            </>
                        )}

                        {activeTab === 'item-categories' && (
                            <>
                                <MobileMasterList
                                    empty={filteredItemCategories.length === 0}
                                >
                                    {filteredItemCategories.map((item) => (
                                        <MasterMobileCard
                                            key={item.id}
                                            name={item.name}
                                            description={item.description}
                                            active={item.is_active}
                                            details={[
                                                {
                                                    label: 'Slug',
                                                    value: item.slug,
                                                    mono: true,
                                                },
                                                {
                                                    label: 'Dipakai',
                                                    value: `${item.bast_items_count} item`,
                                                },
                                            ]}
                                            onEdit={() => openEdit(item)}
                                            onToggle={() =>
                                                openToggle(
                                                    item,
                                                    'item-categories',
                                                )
                                            }
                                        />
                                    ))}
                                </MobileMasterList>

                                <DesktopMasterTable
                                    headers={[
                                        'Kategori',
                                        'Slug',
                                        'Dipakai',
                                        'Status',
                                        'Aksi',
                                    ]}
                                    empty={filteredItemCategories.length === 0}
                                >
                                    {filteredItemCategories.map((item) => (
                                        <tr
                                            key={item.id}
                                            className="border-b border-[#EDF0F2] last:border-b-0"
                                        >
                                            <MasterNameCell
                                                name={item.name}
                                                description={item.description}
                                            />

                                            <td className="px-5 py-4 font-mono text-xs text-[#71808C]">
                                                {item.slug}
                                            </td>

                                            <td className="px-5 py-4 text-xs text-[#657481]">
                                                {item.bast_items_count} item
                                            </td>

                                            <StatusCell
                                                active={item.is_active}
                                            />

                                            <ActionCell
                                                active={item.is_active}
                                                onEdit={() => openEdit(item)}
                                                onToggle={() =>
                                                    openToggle(
                                                        item,
                                                        'item-categories',
                                                    )
                                                }
                                            />
                                        </tr>
                                    ))}
                                </DesktopMasterTable>
                            </>
                        )}

                        {activeTab === 'units' && (
                            <>
                                <MobileMasterList
                                    empty={filteredUnits.length === 0}
                                >
                                    {filteredUnits.map((item) => (
                                        <MasterMobileCard
                                            key={item.id}
                                            name={item.name}
                                            description={item.description}
                                            active={item.is_active}
                                            details={[
                                                {
                                                    label: 'Simbol',
                                                    value: item.symbol ?? '—',
                                                },
                                                {
                                                    label: 'Dipakai',
                                                    value: `${item.bast_items_count} item`,
                                                },
                                            ]}
                                            onEdit={() => openEdit(item)}
                                            onToggle={() =>
                                                openToggle(item, 'units')
                                            }
                                        />
                                    ))}
                                </MobileMasterList>

                                <DesktopMasterTable
                                    headers={[
                                        'Satuan',
                                        'Simbol',
                                        'Dipakai',
                                        'Status',
                                        'Aksi',
                                    ]}
                                    empty={filteredUnits.length === 0}
                                >
                                    {filteredUnits.map((item) => (
                                        <tr
                                            key={item.id}
                                            className="border-b border-[#EDF0F2] last:border-b-0"
                                        >
                                            <MasterNameCell
                                                name={item.name}
                                                description={item.description}
                                            />

                                            <td className="px-5 py-4 text-xs font-medium text-[#52616D]">
                                                {item.symbol ?? '—'}
                                            </td>

                                            <td className="px-5 py-4 text-xs text-[#657481]">
                                                {item.bast_items_count} item
                                            </td>

                                            <StatusCell
                                                active={item.is_active}
                                            />

                                            <ActionCell
                                                active={item.is_active}
                                                onEdit={() => openEdit(item)}
                                                onToggle={() =>
                                                    openToggle(item, 'units')
                                                }
                                            />
                                        </tr>
                                    ))}
                                </DesktopMasterTable>
                            </>
                        )}
                    </section>
                </div>
            </div>

            <Dialog
                open={formOpen}
                onOpenChange={(open) => {
                    if (!open) {
                        closeForm();
                    }
                }}
            >
                <DialogContent className="bast-app w-[calc(100%-2rem)] border-[#DDE3E8] bg-white sm:max-w-[520px]">
                    <DialogHeader>
                        <DialogTitle className="text-[#17212B]">
                            {editTarget
                                ? `Edit ${currentConfig.label}`
                                : `Tambah ${currentConfig.label}`}
                        </DialogTitle>

                        <DialogDescription className="leading-6 text-[#71808C]">
                            {editTarget
                                ? 'Perbarui informasi data master yang dipilih.'
                                : 'Tambahkan data referensi baru ke dalam sistem.'}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={submit}>
                        <div className="grid gap-4 py-2">
                            <div className="grid gap-2">
                                <Label>Nama</Label>

                                <Input
                                    value={data.name}
                                    onChange={(event) =>
                                        setData('name', event.target.value)
                                    }
                                    placeholder={`Nama ${currentConfig.label.toLowerCase()}`}
                                    className="h-10 border-[#D7DEE4] shadow-none"
                                />

                                <InputError message={errors.name} />
                            </div>

                            {activeTab === 'departments' && (
                                <div className="grid gap-2">
                                    <Label>Kode</Label>

                                    <Input
                                        value={data.code}
                                        onChange={(event) =>
                                            setData('code', event.target.value)
                                        }
                                        placeholder="Contoh: TIK"
                                        className="h-10 border-[#D7DEE4] font-mono uppercase shadow-none"
                                    />

                                    <InputError message={errors.code} />

                                    <p className="text-[11px] text-[#8B97A1]">
                                        Kode akan disimpan menggunakan huruf
                                        kapital.
                                    </p>
                                </div>
                            )}

                            {activeTab === 'units' && (
                                <div className="grid gap-2">
                                    <Label>Simbol</Label>

                                    <Input
                                        value={data.symbol}
                                        onChange={(event) =>
                                            setData(
                                                'symbol',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="Contoh: kg, m, unit"
                                        className="h-10 border-[#D7DEE4] shadow-none"
                                    />

                                    <InputError message={errors.symbol} />
                                </div>
                            )}

                            <div className="grid gap-2">
                                <Label>Deskripsi</Label>

                                <textarea
                                    rows={4}
                                    value={data.description}
                                    onChange={(event) =>
                                        setData(
                                            'description',
                                            event.target.value,
                                        )
                                    }
                                    placeholder="Keterangan tambahan (opsional)"
                                    className="w-full resize-none rounded-lg border border-[#D7DEE4] bg-white px-3 py-2.5 text-sm text-[#46545F] outline-none placeholder:text-[#A0AAB2] focus:border-[#1D5D8F]"
                                />

                                <InputError message={errors.description} />
                            </div>
                        </div>

                        <DialogFooter className="mt-5">
                            <Button
                                type="button"
                                variant="outline"
                                disabled={processing}
                                onClick={closeForm}
                                className="w-full border-[#D7DEE4] bg-white text-[#52616D] sm:w-auto"
                            >
                                Batal
                            </Button>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-[#1D5D8F] text-white hover:bg-[#174C76] sm:w-auto"
                            >
                                {processing
                                    ? 'Menyimpan...'
                                    : editTarget
                                      ? 'Simpan Perubahan'
                                      : 'Tambah Data'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={toggleTarget !== null}
                onOpenChange={(open) => {
                    if (!open && !toggling) {
                        setToggleTarget(null);
                    }
                }}
            >
                <DialogContent className="bast-app w-[calc(100%-2rem)] border-[#DDE3E8] bg-white sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="text-[#17212B]">
                            {toggleTarget?.is_active
                                ? 'Nonaktifkan data?'
                                : 'Aktifkan kembali data?'}
                        </DialogTitle>

                        <DialogDescription className="leading-6 break-words text-[#71808C]">
                            &quot;
                            {toggleTarget?.name}
                            &quot; akan{' '}
                            {toggleTarget?.is_active
                                ? 'tidak lagi tersedia untuk data BAST baru.'
                                : 'tersedia kembali untuk digunakan pada data BAST baru.'}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="rounded-lg border border-[#DCE3E8] bg-[#F7F9FA] px-4 py-3">
                        <p className="text-xs leading-5 text-[#5D6B76]">
                            Data lama yang sudah menggunakan referensi ini tidak
                            akan dihapus atau berubah.
                        </p>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={toggling}
                            onClick={() => setToggleTarget(null)}
                            className="w-full border-[#D7DEE4] bg-white text-[#52616D] sm:w-auto"
                        >
                            Batal
                        </Button>

                        <Button
                            type="button"
                            disabled={toggling}
                            onClick={runToggle}
                            className={`w-full sm:w-auto ${
                                toggleTarget?.is_active
                                    ? 'bg-[#B44949] text-white hover:bg-[#9E3D3D]'
                                    : 'bg-[#287A4B] text-white hover:bg-[#21653E]'
                            }`}
                        >
                            {toggling
                                ? 'Memproses...'
                                : toggleTarget?.is_active
                                  ? 'Ya, Nonaktifkan'
                                  : 'Ya, Aktifkan'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

function MobileMasterList({
    empty,
    children,
}: {
    empty: boolean;
    children: React.ReactNode;
}) {
    if (empty) {
        return (
            <div className="lg:hidden">
                <MasterEmptyState />
            </div>
        );
    }

    return (
        <div className="divide-y divide-[#EDF0F2] lg:hidden">{children}</div>
    );
}

function MasterMobileCard({
    name,
    description,
    active,
    details,
    onEdit,
    onToggle,
}: {
    name: string;
    description: string | null;
    active: boolean;
    details: MobileDetail[];
    onEdit: () => void;
    onToggle: () => void;
}) {
    return (
        <div className="min-w-0 px-4 py-4 sm:px-5">
            <div className="flex min-w-0 items-start justify-between gap-3">
                <div className="min-w-0 flex-1">
                    <p className="text-sm font-semibold break-words text-[#344250]">
                        {name}
                    </p>

                    <p className="mt-1 text-[11px] leading-5 break-words text-[#87949F]">
                        {description ?? 'Tidak ada deskripsi'}
                    </p>
                </div>

                <StatusBadge active={active} />
            </div>

            <div className="mt-4 grid grid-cols-2 gap-x-4 gap-y-3">
                {details.map((detail) => (
                    <div key={detail.label} className="min-w-0">
                        <p className="text-[10px] font-medium text-[#929DA6] uppercase">
                            {detail.label}
                        </p>

                        <p
                            className={`mt-1 min-w-0 text-[11px] leading-4 break-words text-[#5F6D78] ${
                                detail.mono ? 'font-mono' : ''
                            }`}
                        >
                            {detail.value}
                        </p>
                    </div>
                ))}
            </div>

            <div className="mt-4 grid grid-cols-2 gap-2 border-t border-[#EDF0F2] pt-3">
                <Button
                    type="button"
                    variant="outline"
                    onClick={onEdit}
                    className="h-9 w-full border-[#D7DEE4] bg-white px-2 text-xs text-[#52616D] shadow-none"
                >
                    <PencilLine className="size-3.5" />
                    Edit
                </Button>

                <Button
                    type="button"
                    variant="outline"
                    onClick={onToggle}
                    className={`h-9 w-full bg-white px-2 text-xs shadow-none ${
                        active
                            ? 'border-[#E3CACA] text-[#B44949]'
                            : 'border-[#CFE2D7] text-[#287A4B]'
                    }`}
                >
                    {active ? (
                        <PowerOff className="size-3.5" />
                    ) : (
                        <Power className="size-3.5" />
                    )}

                    {active ? 'Nonaktifkan' : 'Aktifkan'}
                </Button>
            </div>
        </div>
    );
}

function DesktopMasterTable({
    headers,
    empty,
    children,
}: {
    headers: string[];
    empty: boolean;
    children: React.ReactNode;
}) {
    if (empty) {
        return (
            <div className="hidden lg:block">
                <MasterEmptyState />
            </div>
        );
    }

    return (
        <div className="hidden overflow-x-auto lg:block">
            <table className="w-full min-w-[820px]">
                <thead className="bg-[#FAFBFC]">
                    <tr className="border-b border-[#E6EAED]">
                        {headers.map((header, index) => (
                            <th
                                key={header}
                                className={`px-5 py-3 text-[11px] font-medium text-[#87949F] uppercase ${
                                    index === headers.length - 1
                                        ? 'text-right'
                                        : 'text-left'
                                }`}
                            >
                                {header}
                            </th>
                        ))}
                    </tr>
                </thead>

                <tbody>{children}</tbody>
            </table>
        </div>
    );
}

function MasterEmptyState() {
    return (
        <div className="flex min-h-[300px] flex-col items-center justify-center px-5 text-center">
            <div className="flex size-12 items-center justify-center rounded-xl bg-[#F0F4F6] text-[#87949F]">
                <Database className="size-5" />
            </div>

            <p className="mt-4 text-sm font-semibold text-[#46545F]">
                Data tidak ditemukan
            </p>

            <p className="mt-1 text-xs leading-5 text-[#8B97A1]">
                Coba ubah kata pencarian atau tambahkan data baru.
            </p>
        </div>
    );
}

function MasterNameCell({
    name,
    description,
}: {
    name: string;
    description: string | null;
}) {
    return (
        <td className="px-5 py-4">
            <p className="text-[13px] font-medium text-[#344250]">{name}</p>

            <p className="mt-1 max-w-[420px] truncate text-[11px] text-[#8B97A1]">
                {description ?? 'Tidak ada deskripsi'}
            </p>
        </td>
    );
}

function StatusBadge({ active }: { active: boolean }) {
    return (
        <span
            className={`inline-flex shrink-0 rounded-full px-2.5 py-1 text-[10px] font-medium ${
                active
                    ? 'bg-[#EAF6EF] text-[#287A4B]'
                    : 'bg-[#F1F3F5] text-[#71808C]'
            }`}
        >
            {active ? 'Aktif' : 'Nonaktif'}
        </span>
    );
}

function StatusCell({ active }: { active: boolean }) {
    return (
        <td className="px-5 py-4">
            <StatusBadge active={active} />
        </td>
    );
}

function ActionCell({
    active,
    onEdit,
    onToggle,
}: {
    active: boolean;
    onEdit: () => void;
    onToggle: () => void;
}) {
    return (
        <td className="px-5 py-4">
            <div className="flex justify-end gap-1">
                <Button
                    type="button"
                    variant="ghost"
                    onClick={onEdit}
                    className="h-8 px-2 text-xs text-[#52616D]"
                >
                    <PencilLine className="size-3.5" />
                    Edit
                </Button>

                <Button
                    type="button"
                    variant="ghost"
                    onClick={onToggle}
                    className={`h-8 px-2 text-xs ${
                        active ? 'text-[#B44949]' : 'text-[#287A4B]'
                    }`}
                >
                    {active ? (
                        <PowerOff className="size-3.5" />
                    ) : (
                        <Power className="size-3.5" />
                    )}

                    {active ? 'Nonaktifkan' : 'Aktifkan'}
                </Button>
            </div>
        </td>
    );
}

function matchesSearch(
    search: string,
    ...values: Array<string | null | undefined>
): boolean {
    if (search === '') {
        return true;
    }

    return values.some((value) => (value ?? '').toLowerCase().includes(search));
}

MasterIndex.layout = {
    breadcrumbs: [
        {
            title: 'Data Master',
            href: '/master',
        },
    ],
};
