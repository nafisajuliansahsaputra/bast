<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $bast->document_number ?? 'Preview BAST' }}
    </title>

    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 16mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            background: #eef1f4;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.55;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            border-bottom: 1px solid #dce2e7;
            background: #ffffff;
        }

        .toolbar-group {
            display: flex;
            gap: 8px;
        }

        .toolbar a,
        .toolbar button {
            display: inline-block;
            padding: 9px 14px;
            border: 1px solid #d5dce2;
            border-radius: 7px;
            color: #41505d;
            background: #ffffff;
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar .primary {
            border-color: #1d5d8f;
            color: #ffffff;
            background: #1d5d8f;
        }

        .preview-wrapper {
            padding: 28px;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 18mm 16mm;
            background: #ffffff;
            box-shadow: 0 10px 35px rgba(32, 47, 61, 0.12);
        }

        .institution {
            padding-bottom: 12px;
            border-bottom: 3px double #111827;
            text-align: center;
        }

        .institution-small {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        .institution-main {
            margin: 3px 0 0;
            font-size: 17px;
            font-weight: 700;
        }

        .institution-sub {
            margin: 2px 0 0;
            color: #4b5563;
            font-size: 10px;
        }

        .document-title {
            margin-top: 24px;
            text-align: center;
        }

        .document-title h1 {
            margin: 0;
            font-size: 15px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .document-number {
            margin-top: 3px;
            font-size: 11px;
        }

        .status-banner {
            margin-top: 16px;
            padding: 10px 12px;
            border: 1px solid;
            border-radius: 5px;
            font-size: 10px;
            line-height: 1.5;
        }

        .status-banner strong {
            display: block;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .status-banner.draft {
            border-color: #d6dde3;
            color: #53616d;
            background: #f5f7f9;
        }

        .status-banner.cancelled {
            border-color: #e2b8b8;
            color: #8f3434;
            background: #fff3f3;
        }

        .intro {
            margin-top: 24px;
            text-align: justify;
        }

        .party {
            margin-top: 15px;
        }

        .party-title {
            margin-bottom: 6px;
            font-weight: 700;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .data-table .label {
            width: 125px;
            color: #374151;
        }

        .data-table .separator {
            width: 15px;
        }

        .item-section {
            margin-top: 22px;
        }

        .section-title {
            margin-bottom: 8px;
            font-weight: 700;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
        }

        .items th,
        .items td {
            padding: 6px 7px;
            border: 1px solid #6b7280;
            vertical-align: top;
        }

        .items th {
            background: #f3f4f6;
            font-size: 10px;
            text-align: center;
        }

        .items .center {
            text-align: center;
        }

        .statement {
            margin-top: 20px;
            text-align: justify;
        }

        .signatures {
            width: 100%;
            margin-top: 42px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 50%;
            padding: 0 24px;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 70px;
        }

        .signature-name {
            display: inline-block;
            min-width: 150px;
            padding-bottom: 2px;
            border-bottom: 1px solid #111827;
            font-weight: 700;
        }

        .signature-position {
            margin-top: 3px;
            color: #4b5563;
            font-size: 10px;
        }

        .footer {
            margin-top: 32px;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 8px;
            text-align: center;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .preview-wrapper {
                padding: 0;
            }

            .sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }

        @if ($isPdf)
            body {
                background: #ffffff;
            }

            .preview-wrapper {
                padding: 0;
            }

            .sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        @endif
    </style>
</head>

<body>
@php
    $firstParty = $bast->parties
        ->firstWhere('party_type', 'first_party');

    $secondParty = $bast->parties
        ->firstWhere('party_type', 'second_party');

    $documentDate = $bast->document_date
        ->locale('id')
        ->translatedFormat('d F Y');

    $handoverDate = $bast->handover_date
        ->locale('id')
        ->translatedFormat('d F Y');

    $cancelledDate = $bast->cancelled_at
        ?->locale('id')
        ->translatedFormat('d F Y H:i');

    $formatQuantity = static function ($quantity): string {
        return rtrim(
            rtrim(
                number_format(
                    (float) $quantity,
                    2,
                    ',',
                    '.',
                ),
                '0',
            ),
            ',',
        );
    };
@endphp

@if (! $isPdf)
    <div class="toolbar">
        <a href="{{ route('bast.show', $bast) }}">
            Kembali ke Detail
        </a>

        <div class="toolbar-group">
            @if (! $bast->isDraft())
                <a
                    class="primary"
                    href="{{ route('bast.pdf', $bast) }}"
                >
                    Download PDF
                </a>
            @endif

            <button
                type="button"
                onclick="window.print()"
            >
                Cetak
            </button>
        </div>
    </div>
@endif

<div class="preview-wrapper">
    <main class="sheet">
        <header class="institution">
            <p class="institution-small">
                PEMERINTAH KABUPATEN CIANJUR
            </p>

            <p class="institution-main">
                DINAS KOMUNIKASI DAN INFORMATIKA
            </p>

            <p class="institution-sub">
                Sistem Informasi Berita Acara Serah Terima
            </p>
        </header>

        <section class="document-title">
            <h1>
                Berita Acara Serah Terima
            </h1>

            <div class="document-number">
                Nomor:
                {{ $bast->document_number ?? 'Belum bernomor' }}
            </div>
        </section>

        @if ($bast->isDraft())
            <section class="status-banner draft">
                <strong>DRAFT — BELUM FINAL</strong>

                @if ($bast->sequence_number)
                    Dokumen ini sedang dibuka kembali untuk proses revisi.
                    Nomor resmi dipertahankan, tetapi isi dokumen belum dianggap
                    final sampai proses finalisasi ulang selesai.
                @else
                    Dokumen ini masih berupa draft dan belum menjadi dokumen
                    BAST final.
                @endif
            </section>
        @endif

        @if ($bast->isCancelled())
            <section class="status-banner cancelled">
                <strong>DOKUMEN DIBATALKAN</strong>

                Dokumen ini telah dibatalkan
                @if ($cancelledDate)
                    pada {{ $cancelledDate }}
                @endif

                @if ($bast->cancelledBy)
                    oleh {{ $bast->cancelledBy->name }}
                @endif
                .

                @if ($bast->cancellation_reason)
                    <br>
                    Alasan:
                    {{ $bast->cancellation_reason }}
                @endif
            </section>
        @endif

        <section class="intro">
            Pada tanggal
            <strong>{{ $handoverDate }}</strong>,
            bertempat di
            <strong>{{ $bast->handover_place }}</strong>,
            telah dilaksanakan proses
            {{ strtolower($bast->bastType?->name ?? 'serah terima') }}
            dengan rincian sebagai berikut.
        </section>

        <section class="party">
            <div class="party-title">
                1. PIHAK PERTAMA
            </div>

            <table class="data-table">
                <tr>
                    <td class="label">Nama</td>
                    <td class="separator">:</td>
                    <td>{{ $firstParty?->name ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="label">NIP</td>
                    <td class="separator">:</td>
                    <td>{{ $firstParty?->nip ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="label">Jabatan</td>
                    <td class="separator">:</td>
                    <td>{{ $firstParty?->position ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="label">Unit / Bidang</td>
                    <td class="separator">:</td>
                    <td>{{ $firstParty?->department ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="label">Instansi</td>
                    <td class="separator">:</td>
                    <td>{{ $firstParty?->institution ?? '-' }}</td>
                </tr>
            </table>
        </section>

        <section class="party">
            <div class="party-title">
                2. PIHAK KEDUA
            </div>

            <table class="data-table">
                <tr>
                    <td class="label">Nama</td>
                    <td class="separator">:</td>
                    <td>{{ $secondParty?->name ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="label">NIP</td>
                    <td class="separator">:</td>
                    <td>{{ $secondParty?->nip ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="label">Jabatan</td>
                    <td class="separator">:</td>
                    <td>{{ $secondParty?->position ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="label">Unit / Bidang</td>
                    <td class="separator">:</td>
                    <td>{{ $secondParty?->department ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="label">Instansi</td>
                    <td class="separator">:</td>
                    <td>{{ $secondParty?->institution ?? '-' }}</td>
                </tr>
            </table>
        </section>

        <section class="item-section">
            <div class="section-title">
                Rincian Item Serah Terima
            </div>

            <table class="items">
                <thead>
                    <tr>
                        <th style="width: 30px;">No.</th>
                        <th>Nama Item</th>
                        <th style="width: 110px;">Kategori</th>
                        <th style="width: 75px;">Jumlah</th>
                        <th style="width: 95px;">Kondisi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($bast->items as $index => $item)
                        <tr>
                            <td class="center">
                                {{ $index + 1 }}
                            </td>

                            <td>
                                <strong>{{ $item->name }}</strong>

                                @if (
                                    $item->inventory_number
                                    || $item->serial_number
                                    || $item->code
                                )
                                    <br>

                                    <span style="font-size: 9px; color: #6b7280;">
                                        {{
                                            $item->inventory_number
                                            ?? $item->serial_number
                                            ?? $item->code
                                        }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $item->itemCategory?->name ?? '-' }}
                            </td>

                            <td class="center">
                                {{ $formatQuantity($item->quantity) }}

                                {{
                                    $item->unit?->symbol
                                    ?? $item->unit?->name
                                    ?? ''
                                }}
                            </td>

                            <td>
                                {{
                                    match ($item->condition) {
                                        'baik' => 'Baik',
                                        'rusak_ringan' => 'Rusak Ringan',
                                        'rusak_berat' => 'Rusak Berat',
                                        default => $item->condition ?? '-',
                                    }
                                }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="statement">
            PIHAK PERTAMA dengan ini menyerahkan item sebagaimana
            tercantum di atas kepada PIHAK KEDUA, dan PIHAK KEDUA
            menyatakan telah menerima item tersebut dalam kondisi
            sebagaimana tercantum pada dokumen ini.

            @if ($bast->description)
                <br><br>
                Keterangan:
                {{ $bast->description }}
            @endif

            <br><br>

            Berita acara ini dibuat pada
            {{ $documentDate }}
            untuk dapat dipergunakan sebagaimana mestinya.
        </section>

        <table class="signatures">
            <tr>
                <td>
                    PIHAK PERTAMA

                    <div class="signature-space"></div>

                    <div class="signature-name">
                        {{ $firstParty?->name ?? '-' }}
                    </div>

                    <div class="signature-position">
                        {{ $firstParty?->position ?? '' }}
                    </div>
                </td>

                <td>
                    PIHAK KEDUA

                    <div class="signature-space"></div>

                    <div class="signature-name">
                        {{ $secondParty?->name ?? '-' }}
                    </div>

                    <div class="signature-position">
                        {{ $secondParty?->position ?? '' }}
                    </div>
                </td>
            </tr>
        </table>

        <footer class="footer">
            Dokumen dihasilkan oleh BAST — Digital Handover Management System.
        </footer>
    </main>
</div>
</body>
</html>