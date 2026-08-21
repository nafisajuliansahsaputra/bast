import { router, useForm } from '@inertiajs/react';
import {
    Download,
    FileImage,
    FileText,
    Paperclip,
    Trash2,
    Upload,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useRef, useState } from 'react';
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

export type BastAttachmentItem = {
    id: number;
    category: string | null;
    original_name: string;
    mime_type: string | null;
    file_size: number | null;
    description: string | null;
    created_at: string;

    uploader: {
        id: number;
        name: string;
    } | null;
};

type Props = {
    bastUuid: string;
    attachments: BastAttachmentItem[];
    canManage: boolean;
};

const categoryLabels: Record<string, string> = {
    photo: 'Foto',
    supporting_document: 'Dokumen Pendukung',
    assignment_letter: 'Surat Tugas',
    official_note: 'Nota Dinas',
    signature: 'Tanda Tangan',
    other: 'Lainnya',
};

function formatFileSize(bytes: number | null): string {
    if (bytes === null || bytes <= 0) {
        return '—';
    }

    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function BastAttachmentPanel({
    bastUuid,
    attachments,
    canManage,
}: Props) {
    const fileInputRef = useRef<HTMLInputElement>(null);

    const [attachmentToDelete, setAttachmentToDelete] =
        useState<BastAttachmentItem | null>(null);

    const [deleting, setDeleting] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm<{
        file: File | null;
        category: string;
        description: string;
    }>({
        file: null,
        category: 'supporting_document',
        description: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        post(`/bast/${bastUuid}/attachments`, {
            preserveScroll: true,
            forceFormData: true,

            onSuccess: () => {
                reset();

                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
        });
    };

    const deleteAttachment = () => {
        if (!attachmentToDelete) {
            return;
        }

        setDeleting(true);

        router.delete(
            `/bast/${bastUuid}/attachments/${attachmentToDelete.id}`,
            {
                preserveScroll: true,

                onSuccess: () => {
                    setAttachmentToDelete(null);
                },

                onFinish: () => {
                    setDeleting(false);
                },
            },
        );
    };

    return (
        <>
            <section className="min-w-0 overflow-hidden rounded-[10px] border border-[#DDE3E8] bg-white p-4 sm:p-5">
                <div className="flex min-w-0 items-center gap-2">
                    <Paperclip className="size-4 shrink-0 text-[#1D5D8F]" />

                    <h2 className="min-w-0 text-sm font-semibold text-[#344250]">
                        Lampiran
                    </h2>

                    <span className="ml-auto shrink-0 rounded-full bg-[#F1F4F6] px-2 py-0.5 text-[10px] font-medium text-[#71808C]">
                        {attachments.length}
                    </span>
                </div>

                {canManage && (
                    <form
                        onSubmit={submit}
                        className="mt-4 min-w-0 rounded-lg border border-[#E1E6EA] bg-[#FAFBFC] p-4"
                    >
                        <label className="text-xs font-medium text-[#52616D]">
                            File
                        </label>

                        <input
                            ref={fileInputRef}
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx"
                            onChange={(event) =>
                                setData('file', event.target.files?.[0] ?? null)
                            }
                            className="mt-2 block max-w-full min-w-0 text-xs text-[#657481] file:mr-2 file:rounded-md file:border-0 file:bg-[#EAF3FA] file:px-3 file:py-2 file:text-xs file:font-medium file:text-[#1D5D8F] hover:file:bg-[#DCECF7]"
                        />

                        <InputError message={errors.file} />

                        <label className="mt-4 block text-xs font-medium text-[#52616D]">
                            Kategori
                        </label>

                        <select
                            value={data.category}
                            onChange={(event) =>
                                setData('category', event.target.value)
                            }
                            className="mt-2 h-9 w-full min-w-0 rounded-lg border border-[#D7DEE4] bg-white px-3 text-xs text-[#46545F] outline-none focus:border-[#1D5D8F]"
                        >
                            <option value="supporting_document">
                                Dokumen Pendukung
                            </option>

                            <option value="photo">Foto</option>

                            <option value="assignment_letter">
                                Surat Tugas
                            </option>

                            <option value="official_note">Nota Dinas</option>

                            <option value="signature">Tanda Tangan</option>

                            <option value="other">Lainnya</option>
                        </select>

                        <InputError message={errors.category} />

                        <label className="mt-4 block text-xs font-medium text-[#52616D]">
                            Keterangan
                        </label>

                        <textarea
                            rows={2}
                            value={data.description}
                            onChange={(event) =>
                                setData('description', event.target.value)
                            }
                            placeholder="Opsional"
                            className="mt-2 w-full min-w-0 resize-none rounded-lg border border-[#D7DEE4] bg-white px-3 py-2 text-xs text-[#46545F] outline-none placeholder:text-[#A0AAB2] focus:border-[#1D5D8F]"
                        />

                        <InputError message={errors.description} />

                        <Button
                            type="submit"
                            disabled={processing || !data.file}
                            className="mt-4 h-9 w-full bg-[#1D5D8F] text-xs text-white shadow-none hover:bg-[#174C76]"
                        >
                            <Upload className="size-3.5" />

                            {processing ? 'Mengunggah...' : 'Unggah Lampiran'}
                        </Button>

                        <p className="mt-3 text-[10px] leading-4 text-[#939EA6]">
                            Maksimal 10 MB. PDF, gambar, Word, atau Excel.
                        </p>
                    </form>
                )}

                {attachments.length === 0 ? (
                    <div className="w-full py-7 text-center">
                        <div className="mx-auto flex size-10 items-center justify-center rounded-lg bg-[#F1F5F7] text-[#87949F]">
                            <Paperclip className="size-4" />
                        </div>

                        <p className="mt-3 text-xs font-medium text-[#657481]">
                            Belum ada lampiran
                        </p>

                        <p className="mt-1 text-[10px] leading-4 text-[#939EA6]">
                            File yang diunggah akan muncul di sini.
                        </p>
                    </div>
                ) : (
                    <div className="mt-4 min-w-0 divide-y divide-[#EDF0F2]">
                        {attachments.map((attachment) => {
                            const isImage =
                                attachment.mime_type?.startsWith('image/') ??
                                false;

                            const Icon = isImage ? FileImage : FileText;

                            return (
                                <div
                                    key={attachment.id}
                                    className="min-w-0 py-3 first:pt-0 last:pb-0"
                                >
                                    <div className="flex min-w-0 gap-3">
                                        <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-[#EEF4F8] text-[#1D5D8F]">
                                            <Icon className="size-4" />
                                        </div>

                                        <div className="min-w-0 flex-1">
                                            <p
                                                className="truncate text-xs font-medium text-[#46545F]"
                                                title={attachment.original_name}
                                            >
                                                {attachment.original_name}
                                            </p>

                                            <p className="mt-1 text-[10px] break-words text-[#939EA6]">
                                                {categoryLabels[
                                                    attachment.category ??
                                                        'other'
                                                ] ?? 'Lainnya'}
                                                {' · '}
                                                {formatFileSize(
                                                    attachment.file_size,
                                                )}
                                            </p>

                                            {attachment.description && (
                                                <p className="mt-1 text-[10px] leading-4 break-words text-[#7D8993]">
                                                    {attachment.description}
                                                </p>
                                            )}

                                            <div className="mt-2 flex flex-wrap items-center gap-1">
                                                <a
                                                    href={`/bast/${bastUuid}/attachments/${attachment.id}/download`}
                                                    className="inline-flex h-7 items-center gap-1 rounded-md px-2 text-[10px] font-medium text-[#1D5D8F] hover:bg-[#EEF5FA]"
                                                >
                                                    <Download className="size-3" />
                                                    Download
                                                </a>

                                                {canManage && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            setAttachmentToDelete(
                                                                attachment,
                                                            )
                                                        }
                                                        className="inline-flex h-7 items-center gap-1 rounded-md px-2 text-[10px] font-medium text-[#B44949] hover:bg-[#FFF2F2]"
                                                    >
                                                        <Trash2 className="size-3" />
                                                        Hapus
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </section>

            <Dialog
                open={attachmentToDelete !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setAttachmentToDelete(null);
                    }
                }}
            >
                <DialogContent className="bast-app w-[calc(100%-2rem)] border-[#DDE3E8] bg-white sm:max-w-[460px]">
                    <DialogHeader>
                        <DialogTitle className="text-[#17212B]">
                            Hapus lampiran?
                        </DialogTitle>

                        <DialogDescription className="leading-6 break-words text-[#71808C]">
                            File &quot;
                            {attachmentToDelete?.original_name}
                            &quot; akan dihapus dari BAST ini.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={deleting}
                            onClick={() => setAttachmentToDelete(null)}
                            className="w-full border-[#D7DEE4] bg-white text-[#52616D] sm:w-auto"
                        >
                            Batal
                        </Button>

                        <Button
                            type="button"
                            disabled={deleting}
                            onClick={deleteAttachment}
                            className="w-full bg-[#B44949] text-white hover:bg-[#9E3D3D] sm:w-auto"
                        >
                            {deleting ? 'Menghapus...' : 'Hapus Lampiran'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
