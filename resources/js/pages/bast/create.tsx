import { Head, useForm, usePage } from '@inertiajs/react';
import {
    FileCheck2,
    FileText,
    PackagePlus,
    Paperclip,
    Plus,
    Trash2,
    UsersRound,
} from 'lucide-react';
import { useState } from 'react';
import { BastFormNavigation } from '@/components/bast/bast-form-navigation';
import { BastFormStepper } from '@/components/bast/bast-form-stepper';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type SelectOption = {
    id: number;
    name: string;
};

type Department = SelectOption & {
    code: string;
};

type Unit = SelectOption & {
    symbol: string | null;
};

type PartyForm = {
    user_id: string;
    name: string;
    nip: string;
    position: string;
    department: string;
    institution: string;
    address: string;
};

type ItemForm = {
    item_category_id: string;
    unit_id: string;
    name: string;
    code: string;
    inventory_number: string;
    serial_number: string;
    quantity: string;
    condition: string;
    value: string;
    description: string;
};

type BastForm = {
    bast_type_id: string;
    department_id: string;
    title: string;
    description: string;
    document_date: string;
    handover_date: string;
    handover_place: string;
    parties: {
        first_party: PartyForm;
        second_party: PartyForm;
    };
    items: ItemForm[];
};

type Props = {
    bastTypes: SelectOption[];
    departments: Department[];
    itemCategories: SelectOption[];
    units: Unit[];
    defaultDepartmentId: number | null;
};

const steps = [
    {
        title: 'Informasi',
        icon: FileText,
    },
    {
        title: 'Pihak Terkait',
        icon: UsersRound,
    },
    {
        title: 'Item',
        icon: PackagePlus,
    },
    {
        title: 'Lampiran',
        icon: Paperclip,
    },
    {
        title: 'Review',
        icon: FileCheck2,
    },
];

const emptyParty = (): PartyForm => ({
    user_id: '',
    name: '',
    nip: '',
    position: '',
    department: '',
    institution: '',
    address: '',
});

const emptyItem = (): ItemForm => ({
    item_category_id: '',
    unit_id: '',
    name: '',
    code: '',
    inventory_number: '',
    serial_number: '',
    quantity: '1',
    condition: 'baik',
    value: '',
    description: '',
});

export default function BastCreate({
    bastTypes,
    departments,
    itemCategories,
    units,
    defaultDepartmentId,
}: Props) {
    const { auth } = usePage().props;
    const [step, setStep] = useState(0);

    const { data, setData, post, processing, errors } = useForm<BastForm>({
        bast_type_id: '',
        department_id: defaultDepartmentId ? String(defaultDepartmentId) : '',
        title: '',
        description: '',
        document_date: new Date().toISOString().slice(0, 10),
        handover_date: new Date().toISOString().slice(0, 10),
        handover_place: '',
        parties: {
            first_party: {
                ...emptyParty(),
                name: auth.user?.name ?? '',
                nip: auth.user?.nip ?? '',
                position: auth.user?.position ?? '',
                department: auth.user?.department?.name ?? '',
            },
            second_party: emptyParty(),
        },
        items: [emptyItem()],
    });

    const validationErrors = errors as Record<string, string>;

    const updateParty = (
        party: 'first_party' | 'second_party',
        field: keyof PartyForm,
        value: string,
    ) => {
        setData('parties', {
            ...data.parties,
            [party]: {
                ...data.parties[party],
                [field]: value,
            },
        });
    };

    const updateItem = (
        index: number,
        field: keyof ItemForm,
        value: string,
    ) => {
        setData(
            'items',
            data.items.map((item, itemIndex) =>
                itemIndex === index
                    ? {
                          ...item,
                          [field]: value,
                      }
                    : item,
            ),
        );
    };

    const addItem = () => {
        setData('items', [...data.items, emptyItem()]);
    };

    const removeItem = (index: number) => {
        if (data.items.length === 1) {
            return;
        }

        setData(
            'items',
            data.items.filter((_, itemIndex) => itemIndex !== index),
        );
    };

    const submit = () => {
        post('/bast', {
            preserveScroll: true,
        });
    };

    const selectedType = bastTypes.find(
        (item) => String(item.id) === data.bast_type_id,
    );

    const selectedDepartment = departments.find(
        (item) => String(item.id) === data.department_id,
    );

    return (
        <>
            <Head title="Buat BAST" />

            <div className="flex flex-1 flex-col px-5 py-6 md:px-8 md:py-8">
                <div className="mx-auto w-full max-w-[1200px]">
                    <div>
                        <h1 className="text-[28px] font-semibold tracking-[-0.035em] text-[#17212B]">
                            Buat Berita Acara
                        </h1>

                        <p className="mt-1.5 text-sm text-[#71808C]">
                            Lengkapi informasi untuk membuat draft BAST baru.
                        </p>
                    </div>

                    <BastFormStepper
                        steps={steps}
                        currentStep={step}
                        onStepChange={setStep}
                    />

                    <div className="mt-4 rounded-[10px] border border-[#DDE3E8] bg-white">
                        {step === 0 && (
                            <div className="p-5 md:p-7">
                                <SectionHeader
                                    title="Informasi Dokumen"
                                    description="Informasi utama yang akan digunakan pada dokumen BAST."
                                />

                                <div className="mt-6 grid gap-5 md:grid-cols-2">
                                    <Field label="Jenis BAST">
                                        <select
                                            value={data.bast_type_id}
                                            onChange={(event) =>
                                                setData(
                                                    'bast_type_id',
                                                    event.target.value,
                                                )
                                            }
                                            className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm outline-none focus:border-[#1D5D8F]"
                                        >
                                            <option value="">
                                                Pilih jenis BAST
                                            </option>

                                            {bastTypes.map((item) => (
                                                <option
                                                    key={item.id}
                                                    value={item.id}
                                                >
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>

                                        <InputError
                                            message={errors.bast_type_id}
                                        />
                                    </Field>

                                    <Field label="Unit / Bidang">
                                        <select
                                            value={data.department_id}
                                            disabled={
                                                auth.user?.role?.slug ===
                                                'staff'
                                            }
                                            onChange={(event) =>
                                                setData(
                                                    'department_id',
                                                    event.target.value,
                                                )
                                            }
                                            className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm outline-none focus:border-[#1D5D8F] disabled:bg-[#F5F7F9]"
                                        >
                                            <option value="">Pilih unit</option>

                                            {departments.map((item) => (
                                                <option
                                                    key={item.id}
                                                    value={item.id}
                                                >
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>

                                        <InputError
                                            message={errors.department_id}
                                        />
                                    </Field>

                                    <div className="md:col-span-2">
                                        <Field label="Judul / Perihal">
                                            <Input
                                                value={data.title}
                                                onChange={(event) =>
                                                    setData(
                                                        'title',
                                                        event.target.value,
                                                    )
                                                }
                                                placeholder="Contoh: Serah Terima Perangkat Komputer"
                                                className="h-10 border-[#D7DEE4] shadow-none"
                                            />

                                            <InputError
                                                message={errors.title}
                                            />
                                        </Field>
                                    </div>

                                    <Field label="Tanggal Dokumen">
                                        <Input
                                            type="date"
                                            value={data.document_date}
                                            onChange={(event) =>
                                                setData(
                                                    'document_date',
                                                    event.target.value,
                                                )
                                            }
                                            className="h-10 border-[#D7DEE4] shadow-none"
                                        />

                                        <InputError
                                            message={errors.document_date}
                                        />
                                    </Field>

                                    <Field label="Tanggal Serah Terima">
                                        <Input
                                            type="date"
                                            value={data.handover_date}
                                            onChange={(event) =>
                                                setData(
                                                    'handover_date',
                                                    event.target.value,
                                                )
                                            }
                                            className="h-10 border-[#D7DEE4] shadow-none"
                                        />

                                        <InputError
                                            message={errors.handover_date}
                                        />
                                    </Field>

                                    <div className="md:col-span-2">
                                        <Field label="Tempat Serah Terima">
                                            <Input
                                                value={data.handover_place}
                                                onChange={(event) =>
                                                    setData(
                                                        'handover_place',
                                                        event.target.value,
                                                    )
                                                }
                                                placeholder="Contoh: Kantor Diskominfo Kabupaten Cianjur"
                                                className="h-10 border-[#D7DEE4] shadow-none"
                                            />

                                            <InputError
                                                message={errors.handover_place}
                                            />
                                        </Field>
                                    </div>

                                    <div className="md:col-span-2">
                                        <Field label="Deskripsi / Keterangan">
                                            <textarea
                                                value={data.description}
                                                onChange={(event) =>
                                                    setData(
                                                        'description',
                                                        event.target.value,
                                                    )
                                                }
                                                rows={4}
                                                placeholder="Keterangan tambahan mengenai proses serah terima..."
                                                className="w-full resize-none rounded-lg border border-[#D7DEE4] px-3 py-2.5 text-sm outline-none focus:border-[#1D5D8F]"
                                            />

                                            <InputError
                                                message={errors.description}
                                            />
                                        </Field>
                                    </div>
                                </div>
                            </div>
                        )}

                        {step === 1 && (
                            <div className="p-5 md:p-7">
                                <SectionHeader
                                    title="Pihak Terkait"
                                    description="Isi informasi pihak yang menyerahkan dan menerima."
                                />

                                <div className="mt-6 grid gap-5 xl:grid-cols-2">
                                    <PartyCard
                                        title="Pihak Pertama"
                                        subtitle="Pihak yang menyerahkan"
                                        party={data.parties.first_party}
                                        errorPrefix="parties.first_party"
                                        errors={validationErrors}
                                        onChange={(field, value) =>
                                            updateParty(
                                                'first_party',
                                                field,
                                                value,
                                            )
                                        }
                                    />

                                    <PartyCard
                                        title="Pihak Kedua"
                                        subtitle="Pihak yang menerima"
                                        party={data.parties.second_party}
                                        errorPrefix="parties.second_party"
                                        errors={validationErrors}
                                        onChange={(field, value) =>
                                            updateParty(
                                                'second_party',
                                                field,
                                                value,
                                            )
                                        }
                                    />
                                </div>
                            </div>
                        )}

                        {step === 2 && (
                            <div className="p-5 md:p-7">
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between sm:gap-5">
                                    <SectionHeader
                                        title="Item Serah Terima"
                                        description="Tambahkan satu atau lebih item yang menjadi objek serah terima."
                                    />

                                    <Button
                                        type="button"
                                        onClick={addItem}
                                        variant="outline"
                                        className="w-full border-[#D7DEE4] sm:w-auto sm:shrink-0"
                                    >
                                        <Plus className="size-4" />
                                        Tambah Item
                                    </Button>
                                </div>

                                <div className="mt-6 space-y-4">
                                    {data.items.map((item, index) => (
                                        <div
                                            key={index}
                                            className="rounded-[10px] border border-[#E0E5E9] p-5"
                                        >
                                            <div className="flex items-center justify-between">
                                                <p className="text-sm font-semibold text-[#344250]">
                                                    Item {index + 1}
                                                </p>

                                                <button
                                                    type="button"
                                                    disabled={
                                                        data.items.length === 1
                                                    }
                                                    onClick={() =>
                                                        removeItem(index)
                                                    }
                                                    className="flex size-8 items-center justify-center rounded-lg text-[#A35A5A] hover:bg-[#FCEEEE] disabled:cursor-not-allowed disabled:opacity-30"
                                                >
                                                    <Trash2 className="size-4" />
                                                </button>
                                            </div>

                                            <div className="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                                <Field label="Nama Item">
                                                    <Input
                                                        value={item.name}
                                                        onChange={(event) =>
                                                            updateItem(
                                                                index,
                                                                'name',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 border-[#D7DEE4] shadow-none"
                                                        placeholder="Nama item"
                                                    />

                                                    <InputError
                                                        message={
                                                            validationErrors[
                                                                `items.${index}.name`
                                                            ]
                                                        }
                                                    />
                                                </Field>

                                                <Field label="Kategori">
                                                    <select
                                                        value={
                                                            item.item_category_id
                                                        }
                                                        onChange={(event) =>
                                                            updateItem(
                                                                index,
                                                                'item_category_id',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm outline-none focus:border-[#1D5D8F]"
                                                    >
                                                        <option value="">
                                                            Pilih kategori
                                                        </option>

                                                        {itemCategories.map(
                                                            (category) => (
                                                                <option
                                                                    key={
                                                                        category.id
                                                                    }
                                                                    value={
                                                                        category.id
                                                                    }
                                                                >
                                                                    {
                                                                        category.name
                                                                    }
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>
                                                </Field>

                                                <Field label="Kode Item">
                                                    <Input
                                                        value={item.code}
                                                        onChange={(event) =>
                                                            updateItem(
                                                                index,
                                                                'code',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 border-[#D7DEE4] shadow-none"
                                                        placeholder="Opsional"
                                                    />
                                                </Field>

                                                <Field label="No. Inventaris">
                                                    <Input
                                                        value={
                                                            item.inventory_number
                                                        }
                                                        onChange={(event) =>
                                                            updateItem(
                                                                index,
                                                                'inventory_number',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 border-[#D7DEE4] shadow-none"
                                                        placeholder="Opsional"
                                                    />
                                                </Field>

                                                <Field label="Serial Number">
                                                    <Input
                                                        value={
                                                            item.serial_number
                                                        }
                                                        onChange={(event) =>
                                                            updateItem(
                                                                index,
                                                                'serial_number',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 border-[#D7DEE4] shadow-none"
                                                        placeholder="Opsional"
                                                    />
                                                </Field>

                                                <Field label="Kondisi">
                                                    <select
                                                        value={item.condition}
                                                        onChange={(event) =>
                                                            updateItem(
                                                                index,
                                                                'condition',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm outline-none focus:border-[#1D5D8F]"
                                                    >
                                                        <option value="baik">
                                                            Baik
                                                        </option>
                                                        <option value="rusak_ringan">
                                                            Rusak Ringan
                                                        </option>
                                                        <option value="rusak_berat">
                                                            Rusak Berat
                                                        </option>
                                                    </select>
                                                </Field>

                                                <Field label="Jumlah">
                                                    <Input
                                                        type="number"
                                                        min="0.01"
                                                        step="0.01"
                                                        value={item.quantity}
                                                        onChange={(event) =>
                                                            updateItem(
                                                                index,
                                                                'quantity',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 border-[#D7DEE4] shadow-none"
                                                    />

                                                    <InputError
                                                        message={
                                                            validationErrors[
                                                                `items.${index}.quantity`
                                                            ]
                                                        }
                                                    />
                                                </Field>

                                                <Field label="Satuan">
                                                    <select
                                                        value={item.unit_id}
                                                        onChange={(event) =>
                                                            updateItem(
                                                                index,
                                                                'unit_id',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 w-full rounded-lg border border-[#D7DEE4] bg-white px-3 text-sm outline-none focus:border-[#1D5D8F]"
                                                    >
                                                        <option value="">
                                                            Pilih satuan
                                                        </option>

                                                        {units.map((unit) => (
                                                            <option
                                                                key={unit.id}
                                                                value={unit.id}
                                                            >
                                                                {unit.name}
                                                                {unit.symbol
                                                                    ? ` (${unit.symbol})`
                                                                    : ''}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </Field>

                                                <Field label="Nilai (Rp)">
                                                    <Input
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        value={item.value}
                                                        onChange={(event) =>
                                                            updateItem(
                                                                index,
                                                                'value',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 border-[#D7DEE4] shadow-none"
                                                        placeholder="Opsional"
                                                    />
                                                </Field>

                                                <div className="md:col-span-2 xl:col-span-3">
                                                    <Field label="Keterangan Item">
                                                        <textarea
                                                            value={
                                                                item.description
                                                            }
                                                            onChange={(event) =>
                                                                updateItem(
                                                                    index,
                                                                    'description',
                                                                    event.target
                                                                        .value,
                                                                )
                                                            }
                                                            rows={2}
                                                            className="w-full resize-none rounded-lg border border-[#D7DEE4] px-3 py-2.5 text-sm outline-none focus:border-[#1D5D8F]"
                                                            placeholder="Opsional"
                                                        />
                                                    </Field>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {step === 3 && (
                            <div className="p-5 md:p-7">
                                <SectionHeader
                                    title="Lampiran"
                                    description="Lampiran akan ditambahkan setelah draft BAST berhasil dibuat."
                                />

                                <div className="mt-6 flex min-h-[260px] items-center justify-center rounded-[10px] border border-dashed border-[#CDD6DD] bg-[#FAFBFC]">
                                    <div className="max-w-sm px-6 text-center">
                                        <div className="mx-auto flex size-11 items-center justify-center rounded-[10px] bg-[#EAF3FA] text-[#1D5D8F]">
                                            <Paperclip className="size-5" />
                                        </div>

                                        <p className="mt-4 text-sm font-medium text-[#344250]">
                                            Lampiran tersedia setelah draft
                                            disimpan
                                        </p>

                                        <p className="mt-1.5 text-xs leading-5 text-[#87949F]">
                                            Foto, dokumen pendukung, surat
                                            tugas, atau berkas lainnya akan kita
                                            kelola pada detail BAST.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        )}

                        {step === 4 && (
                            <div className="p-5 md:p-7">
                                <SectionHeader
                                    title="Review"
                                    description="Periksa kembali data sebelum menyimpan draft."
                                />

                                <div className="mt-6 grid gap-4 lg:grid-cols-2">
                                    <ReviewCard title="Informasi Dokumen">
                                        <ReviewRow
                                            label="Jenis"
                                            value={selectedType?.name ?? '—'}
                                        />

                                        <ReviewRow
                                            label="Unit"
                                            value={
                                                selectedDepartment?.name ?? '—'
                                            }
                                        />

                                        <ReviewRow
                                            label="Judul"
                                            value={data.title || '—'}
                                        />

                                        <ReviewRow
                                            label="Tanggal"
                                            value={data.document_date || '—'}
                                        />

                                        <ReviewRow
                                            label="Serah Terima"
                                            value={data.handover_date || '—'}
                                        />

                                        <ReviewRow
                                            label="Tempat"
                                            value={data.handover_place || '—'}
                                        />
                                    </ReviewCard>

                                    <ReviewCard title="Pihak Terkait">
                                        <ReviewRow
                                            label="Pihak Pertama"
                                            value={
                                                data.parties.first_party.name ||
                                                '—'
                                            }
                                        />

                                        <ReviewRow
                                            label="Instansi"
                                            value={
                                                data.parties.first_party
                                                    .institution || '—'
                                            }
                                        />

                                        <ReviewRow
                                            label="Pihak Kedua"
                                            value={
                                                data.parties.second_party
                                                    .name || '—'
                                            }
                                        />

                                        <ReviewRow
                                            label="Instansi"
                                            value={
                                                data.parties.second_party
                                                    .institution || '—'
                                            }
                                        />
                                    </ReviewCard>

                                    <div className="lg:col-span-2">
                                        <ReviewCard title="Item">
                                            <p className="text-sm text-[#52616D]">
                                                {data.items.length} item akan
                                                disimpan pada draft BAST.
                                            </p>
                                        </ReviewCard>
                                    </div>
                                </div>

                                {Object.keys(errors).length > 0 && (
                                    <div className="mt-5 rounded-lg border border-[#F0CACA] bg-[#FFF7F7] px-4 py-3 text-sm text-[#A94747]">
                                        Masih ada data yang belum valid. Periksa
                                        kembali setiap langkah sebelum
                                        menyimpan.
                                    </div>
                                )}
                            </div>
                        )}

                        <BastFormNavigation
                            currentStep={step}
                            totalSteps={steps.length}
                            cancelHref="/bast"
                            processing={processing}
                            onPrevious={() => setStep(Math.max(step - 1, 0))}
                            onNext={() =>
                                setStep(Math.min(step + 1, steps.length - 1))
                            }
                            onSubmit={submit}
                            submitLabel="Simpan Draft"
                            processingLabel="Menyimpan..."
                        />
                    </div>
                </div>
            </div>
        </>
    );
}

function SectionHeader({
    title,
    description,
}: {
    title: string;
    description: string;
}) {
    return (
        <div>
            <h2 className="text-[16px] font-semibold text-[#25313C]">
                {title}
            </h2>

            <p className="mt-1 text-xs leading-5 text-[#87949F]">
                {description}
            </p>
        </div>
    );
}

function Field({
    label,
    children,
}: {
    label: string;
    children: React.ReactNode;
}) {
    return (
        <div className="grid gap-2">
            <Label className="text-sm font-medium text-[#46545F]">
                {label}
            </Label>

            {children}
        </div>
    );
}

function PartyCard({
    title,
    subtitle,
    party,
    errorPrefix,
    errors,
    onChange,
}: {
    title: string;
    subtitle: string;
    party: PartyForm;
    errorPrefix: string;
    errors: Record<string, string>;
    onChange: (field: keyof PartyForm, value: string) => void;
}) {
    return (
        <div className="rounded-[10px] border border-[#E0E5E9] p-5">
            <h3 className="text-sm font-semibold text-[#344250]">{title}</h3>

            <p className="mt-1 text-xs text-[#87949F]">{subtitle}</p>

            <div className="mt-5 grid gap-4">
                <Field label="Nama">
                    <Input
                        value={party.name}
                        onChange={(event) =>
                            onChange('name', event.target.value)
                        }
                        className="h-10 border-[#D7DEE4] shadow-none"
                    />

                    <InputError message={errors[`${errorPrefix}.name`]} />
                </Field>

                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="NIP">
                        <Input
                            value={party.nip}
                            onChange={(event) =>
                                onChange('nip', event.target.value)
                            }
                            className="h-10 border-[#D7DEE4] shadow-none"
                        />
                    </Field>

                    <Field label="Jabatan">
                        <Input
                            value={party.position}
                            onChange={(event) =>
                                onChange('position', event.target.value)
                            }
                            className="h-10 border-[#D7DEE4] shadow-none"
                        />
                    </Field>
                </div>

                <Field label="Unit / Bidang">
                    <Input
                        value={party.department}
                        onChange={(event) =>
                            onChange('department', event.target.value)
                        }
                        className="h-10 border-[#D7DEE4] shadow-none"
                    />
                </Field>

                <Field label="Instansi">
                    <Input
                        value={party.institution}
                        onChange={(event) =>
                            onChange('institution', event.target.value)
                        }
                        className="h-10 border-[#D7DEE4] shadow-none"
                    />

                    <InputError
                        message={errors[`${errorPrefix}.institution`]}
                    />
                </Field>

                <Field label="Alamat">
                    <textarea
                        value={party.address}
                        onChange={(event) =>
                            onChange('address', event.target.value)
                        }
                        rows={3}
                        className="w-full resize-none rounded-lg border border-[#D7DEE4] px-3 py-2.5 text-sm outline-none focus:border-[#1D5D8F]"
                    />
                </Field>
            </div>
        </div>
    );
}

function ReviewCard({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <div className="rounded-[10px] border border-[#E0E5E9] p-5">
            <h3 className="text-sm font-semibold text-[#344250]">{title}</h3>

            <div className="mt-4 space-y-3">{children}</div>
        </div>
    );
}

function ReviewRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-start justify-between gap-5">
            <span className="text-xs text-[#87949F]">{label}</span>

            <span className="max-w-[65%] text-right text-xs font-medium text-[#46545F]">
                {value}
            </span>
        </div>
    );
}

BastCreate.layout = {
    breadcrumbs: [
        {
            title: 'Berita Acara',
            href: '/bast',
        },
        {
            title: 'Buat BAST',
            href: '/bast/create',
        },
    ],
};
