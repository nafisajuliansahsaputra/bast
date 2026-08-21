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
            margin: 14mm 18mm 17mm 24mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            color: #111111;
            background: #eef1f4;
            font-family: Arial, "DejaVu Sans", sans-serif;
            font-size: 10pt;
            line-height: 1.35;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            border-bottom: 1px solid #dce2e7;
            background: #ffffff;
            font-family: Arial, sans-serif;
            font-size: 13px;
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
            position: relative;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 14mm 18mm 17mm 24mm;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 10px 35px rgba(32, 47, 61, 0.12);
        }

        .document-content {
            position: relative;
            z-index: 1;
        }

        .letterhead {
            width: 100%;
            border-collapse: collapse;
        }

        .letterhead td {
            padding: 0;
            vertical-align: middle;
        }

        .emblem-cell,
        .balance-cell {
            width: 19mm;
        }

        .emblem-cell {
            text-align: center;
        }

        .emblem {
            width: 16mm;
            height: auto;
        }

        .emblem-placeholder {
            width: 16mm;
            height: 18mm;
            border: 1px solid #111111;
            font-size: 6pt;
            line-height: 18mm;
            text-align: center;
        }

        .letterhead-content {
            text-align: center;
        }

        .government-name {
            margin: 0;
            font-size: 11.5pt;
            font-weight: 400;
            line-height: 1.05;
            text-transform: uppercase;
        }

        .agency-name {
            margin: 1px 0 0;
            font-size: 14pt;
            font-weight: 700;
            line-height: 1.02;
            text-transform: uppercase;
        }

        .agency-address {
            margin: 4px 0 0;
            font-size: 6.7pt;
            line-height: 1.2;
        }

        .letterhead-rule {
            height: 4px;
            margin-top: 6px;
            border-top: 3px solid #111111;
            border-bottom: 1px solid #111111;
        }

        .document-heading {
            margin-top: 16px;
            text-align: center;
        }

        .document-heading h1 {
            display: inline-block;
            margin: 0;
            padding-bottom: 1px;
            border-bottom: 1px solid #111111;
            font-size: 11.5pt;
            font-weight: 700;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .document-number {
            margin-top: 2px;
            font-size: 9pt;
        }

        .document-subject {
            margin: 8px auto 0;
            max-width: 88%;
            font-size: 9.5pt;
            font-weight: 700;
            line-height: 1.3;
            text-align: center;
        }

        .paragraph {
            margin: 15px 0 0;
            text-align: justify;
            text-indent: 30px;
        }

        .party-block {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .party-block > tbody > tr > td {
            vertical-align: top;
        }

        .party-number {
            width: 22px;
            padding-top: 1px;
            font-weight: 700;
        }

        .party-table {
            width: 100%;
            border-collapse: collapse;
        }

        .party-table td {
            padding: 0;
            vertical-align: top;
        }

        .party-table .label {
            width: 105px;
        }

        .party-table .separator {
            width: 14px;
        }

        .party-designation {
            margin-top: 3px;
            font-size: 9pt;
            font-style: italic;
        }

        .section-paragraph {
            margin-top: 13px;
            text-align: justify;
        }

        .items-title {
            margin-top: 12px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        .items tr {
            page-break-inside: avoid;
        }

        .items th,
        .items td {
            padding: 4px 5px;
            border: 1px solid #111111;
            vertical-align: top;
        }

        .items th {
            font-size: 7.5pt;
            font-weight: 700;
            line-height: 1.2;
            text-align: center;
        }

        .items td {
            font-size: 7.7pt;
            line-height: 1.25;
        }

        .center {
            text-align: center;
        }

        .item-meta {
            margin-top: 2px;
            font-size: 6.8pt;
            line-height: 1.2;
        }

        .closing {
            margin-top: 13px;
            text-align: justify;
            text-indent: 30px;
        }

        .cancellation-note {
            margin-top: 12px;
            font-size: 8.5pt;
            font-style: italic;
            line-height: 1.3;
            text-align: center;
        }

        .location-date {
            width: 100%;
            margin-top: 17px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .location-date td {
            width: 50%;
            padding: 0;
            vertical-align: top;
        }

        .location-date .right {
            padding-left: 28px;
        }

        .signatures {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 50%;
            padding: 0 20px;
            text-align: center;
            vertical-align: top;
        }

        .signature-role {
            min-height: 22px;
            font-weight: 700;
        }

        .signature-space {
            height: 50px;
        }

        .signature-name {
            display: inline-block;
            min-width: 135px;
            padding-bottom: 1px;
            border-bottom: 1px solid #111111;
            font-weight: 700;
        }

        .signature-detail {
            margin-top: 2px;
            font-size: 7.5pt;
            line-height: 1.25;
        }

        .demo-footer {
            margin-top: 19px;
            padding-top: 4px;
            border-top: 1px solid #d0d0d0;
            color: #777777;
            font-size: 5.5pt;
            line-height: 1.25;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .watermark {
            position: fixed;
            top: 43%;
            left: 10%;
            z-index: 0;
            width: 80%;
            color: rgba(100, 100, 100, 0.08);
            font-size: 58pt;
            font-weight: 700;
            text-align: center;
            transform: rotate(-32deg);
        }

        .watermark.cancelled {
            color: rgba(150, 20, 20, 0.09);
            font-size: 46pt;
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
                overflow: visible;
                box-shadow: none;
            }
        }

@if ($isPdf)
    @page {
        size: A4 portrait;
        margin: 0;
    }

    body {
        margin: 0;
        padding: 0;
        background: #ffffff;
        font-size: 8.7pt;
        line-height: 1.25;
    }

    .preview-wrapper {
        margin: 0;
        padding: 0;
    }

    .sheet {
        width: 210mm;
        min-height: 297mm;
        margin: 0;
        padding: 0;
        overflow: visible;
        background: #ffffff;
        box-shadow: none;
    }

    /*
     * Jangan mengandalkan @page margin di DomPDF.
     * Padding ini adalah margin fisik dokumen PDF.
     */
    .document-content {
        padding:
            14mm
            18mm
            17mm
            24mm;
    }

    .emblem-cell,
    .balance-cell {
        width: 17mm;
    }

    .emblem {
        width: 14.5mm;
    }

    .government-name {
        font-size: 10.5pt;
    }

    .agency-name {
        font-size: 12.5pt;
    }

    .agency-address {
        margin-top: 3px;
        font-size: 6pt;
        line-height: 1.15;
    }

    .letterhead-rule {
        margin-top: 4px;
    }

    .document-heading {
        margin-top: 11px;
    }

    .document-heading h1 {
        font-size: 10.5pt;
    }

    .document-number {
        margin-top: 1px;
        font-size: 8pt;
    }

    .document-subject {
        margin-top: 5px;
        font-size: 8.5pt;
        line-height: 1.2;
    }

    .paragraph {
        margin-top: 9px;
        text-indent: 25px;
    }

    .party-block {
        margin-top: 6px;
    }

    .party-table .label {
        width: 92px;
    }

    .party-table .separator {
        width: 12px;
    }

    .party-designation {
        margin-top: 1px;
        font-size: 7.8pt;
    }

    .section-paragraph {
        margin-top: 7px;
    }

    .items-title {
        margin-top: 7px;
        margin-bottom: 3px;
    }

    .items th,
    .items td {
        padding: 3px 4px;
    }

    .items th {
        font-size: 6.8pt;
        line-height: 1.15;
    }

    .items td {
        font-size: 7pt;
        line-height: 1.15;
    }

    .item-meta {
        margin-top: 1px;
        font-size: 6pt;
        line-height: 1.15;
    }

    .closing {
        margin-top: 7px;
        text-indent: 25px;
    }

    .cancellation-note {
        margin-top: 7px;
        font-size: 7.5pt;
    }

    .location-date {
        margin-top: 8px;
    }

    .location-date .right {
        padding-left: 20px;
    }

    .signatures {
        margin-top: 5px;
    }

    .signatures td {
        padding: 0 14px;
    }

    .signature-role {
        min-height: 15px;
    }

    .signature-space {
        height: 28px;
    }

    .signature-name {
        min-width: 115px;
    }

    .signature-detail {
        margin-top: 1px;
        font-size: 6.5pt;
        line-height: 1.15;
    }

    .demo-footer {
        position: fixed;
        right: 18mm;
        bottom: 7mm;
        left: 24mm;
        margin: 0;
        padding-top: 3px;
        font-size: 5pt;
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

    $handoverDay = ucfirst(
        $bast->handover_date
            ->locale('id')
            ->translatedFormat('l'),
    );

    $handoverDayNumber = $bast->handover_date
        ->format('d');

    $handoverMonth = $bast->handover_date
        ->locale('id')
        ->translatedFormat('F');

    $handoverYear = $bast->handover_date
        ->format('Y');

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

    $emblemPath = public_path(
        'branding/cianjur-emblem.png',
    );

    $emblemContent = is_file($emblemPath)
        ? file_get_contents($emblemPath)
        : false;

    $emblemData = is_string($emblemContent)
        ? 'data:image/png;base64,'.base64_encode($emblemContent)
        : null;
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
        @if ($bast->isDraft())
            <div class="watermark">
                DRAFT
            </div>
        @endif

        @if ($bast->isCancelled())
            <div class="watermark cancelled">
                DIBATALKAN
            </div>
        @endif

        <div class="document-content">
            <header>
                <table class="letterhead">
                    <tr>
                        <td class="emblem-cell">
                            @if ($emblemData)
                                <img
                                    class="emblem"
                                    src="{{ $emblemData }}"
                                    alt="Lambang Kabupaten Cianjur"
                                >
                            @else
                                <div class="emblem-placeholder">
                                    LAMBANG
                                </div>
                            @endif
                        </td>

                        <td class="letterhead-content">
                            <p class="government-name">
                                Pemerintah Kabupaten Cianjur
                            </p>

                            <p class="agency-name">
                                Dinas Komunikasi, Informatika
                                <br>
                                dan Persandian
                            </p>

                            <p class="agency-address">
                                Jalan KH. Abdullah Bin Nuh, Nagrak,
                                Kecamatan Cianjur, Kabupaten Cianjur,
                                Jawa Barat 43215
                                <br>
                                diskominfo.cianjurkab.go.id
                                &nbsp; | &nbsp;
                                diskominfo@cianjurkab.go.id
                            </p>
                        </td>

                        <td class="balance-cell"></td>
                    </tr>
                </table>

                <div class="letterhead-rule"></div>
            </header>

            <section class="document-heading">
                <h1>
                    Berita Acara Serah Terima
                </h1>

                <div class="document-number">
                    Nomor :
                    {{ $bast->document_number ?? 'Belum bernomor' }}
                </div>

                <div class="document-subject">
                    {{ $bast->title }}
                </div>
            </section>

            @if ($bast->isCancelled())
                <div class="cancellation-note">
                    Dokumen ini telah dibatalkan

                    @if ($cancelledDate)
                        pada {{ $cancelledDate }}
                    @endif

                    @if ($bast->cancellation_reason)
                        dengan alasan:
                        {{ $bast->cancellation_reason }}
                    @endif
                </div>
            @endif

            <p class="paragraph">
                Pada hari ini,
                <strong>{{ $handoverDay }}</strong>,
                tanggal
                <strong>{{ $handoverDayNumber }}</strong>
                bulan
                <strong>{{ $handoverMonth }}</strong>
                tahun
                <strong>{{ $handoverYear }}</strong>,
                bertempat di
                <strong>{{ $bast->handover_place }}</strong>,
                kami yang bertanda tangan di bawah ini:
            </p>

            <table class="party-block">
                <tr>
                    <td class="party-number">
                        1.
                    </td>

                    <td>
                        <table class="party-table">
                            <tr>
                                <td class="label">Nama</td>
                                <td class="separator">:</td>
                                <td>{{ $firstParty?->name ?? '-' }}</td>
                            </tr>

                            @if ($firstParty?->nip)
                                <tr>
                                    <td class="label">NIP</td>
                                    <td class="separator">:</td>
                                    <td>{{ $firstParty->nip }}</td>
                                </tr>
                            @endif

                            @if ($firstParty?->position)
                                <tr>
                                    <td class="label">Jabatan</td>
                                    <td class="separator">:</td>
                                    <td>{{ $firstParty->position }}</td>
                                </tr>
                            @endif

                            @if ($firstParty?->department)
                                <tr>
                                    <td class="label">Unit / Bidang</td>
                                    <td class="separator">:</td>
                                    <td>{{ $firstParty->department }}</td>
                                </tr>
                            @endif

                            @if ($firstParty?->institution)
                                <tr>
                                    <td class="label">Instansi</td>
                                    <td class="separator">:</td>
                                    <td>{{ $firstParty->institution }}</td>
                                </tr>
                            @endif

                            @if ($firstParty?->address)
                                <tr>
                                    <td class="label">Alamat</td>
                                    <td class="separator">:</td>
                                    <td>{{ $firstParty->address }}</td>
                                </tr>
                            @endif
                        </table>

                        <div class="party-designation">
                            Selanjutnya disebut sebagai
                            <strong>PIHAK PERTAMA</strong>.
                        </div>
                    </td>
                </tr>
            </table>

            <table class="party-block">
                <tr>
                    <td class="party-number">
                        2.
                    </td>

                    <td>
                        <table class="party-table">
                            <tr>
                                <td class="label">Nama</td>
                                <td class="separator">:</td>
                                <td>{{ $secondParty?->name ?? '-' }}</td>
                            </tr>

                            @if ($secondParty?->nip)
                                <tr>
                                    <td class="label">NIP</td>
                                    <td class="separator">:</td>
                                    <td>{{ $secondParty->nip }}</td>
                                </tr>
                            @endif

                            @if ($secondParty?->position)
                                <tr>
                                    <td class="label">Jabatan</td>
                                    <td class="separator">:</td>
                                    <td>{{ $secondParty->position }}</td>
                                </tr>
                            @endif

                            @if ($secondParty?->department)
                                <tr>
                                    <td class="label">Unit / Bidang</td>
                                    <td class="separator">:</td>
                                    <td>{{ $secondParty->department }}</td>
                                </tr>
                            @endif

                            @if ($secondParty?->institution)
                                <tr>
                                    <td class="label">Instansi</td>
                                    <td class="separator">:</td>
                                    <td>{{ $secondParty->institution }}</td>
                                </tr>
                            @endif

                            @if ($secondParty?->address)
                                <tr>
                                    <td class="label">Alamat</td>
                                    <td class="separator">:</td>
                                    <td>{{ $secondParty->address }}</td>
                                </tr>
                            @endif
                        </table>

                        <div class="party-designation">
                            Selanjutnya disebut sebagai
                            <strong>PIHAK KEDUA</strong>.
                        </div>
                    </td>
                </tr>
            </table>

            <p class="section-paragraph">
                PIHAK PERTAMA dan PIHAK KEDUA menerangkan bahwa
                telah dilaksanakan
                {{ strtolower(
                    $bast->bastType?->name
                    ?? 'serah terima'
                ) }}
                dengan rincian sebagai berikut:
            </p>

            <div class="items-title">
                Rincian Objek Serah Terima
            </div>

            <table class="items">
                <thead>
                    <tr>
                        <th style="width: 28px;">No.</th>
                        <th>Uraian Barang / Objek</th>
                        <th style="width: 130px;">Nomor / Identitas</th>
                        <th style="width: 68px;">Jumlah</th>
                        <th style="width: 85px;">Kondisi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($bast->items as $index => $item)
                        @php
                            $references = array_filter([
                                $item->inventory_number
                                    ? 'Inv. '.$item->inventory_number
                                    : null,

                                $item->serial_number
                                    ? 'SN '.$item->serial_number
                                    : null,

                                $item->code
                                    ? 'Kode '.$item->code
                                    : null,
                            ]);
                        @endphp

                        <tr>
                            <td class="center">
                                {{ $index + 1 }}
                            </td>

                            <td>
                                <strong>{{ $item->name }}</strong>

                                @if ($item->itemCategory)
                                    <div class="item-meta">
                                        Kategori:
                                        {{ $item->itemCategory->name }}
                                    </div>
                                @endif

                                @if ($item->description)
                                    <div class="item-meta">
                                        {{ $item->description }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if (count($references) > 0)
                                    {{ implode(' / ', $references) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="center">
                                {{ $formatQuantity($item->quantity) }}

                                {{ $item->unit?->symbol
                                    ?? $item->unit?->name
                                    ?? '' }}
                            </td>

                            <td class="center">
                                {{ $item->condition ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="closing">
                Dengan ditandatanganinya Berita Acara Serah Terima ini,
                PIHAK PERTAMA menyatakan telah menyerahkan objek
                sebagaimana tercantum di atas kepada PIHAK KEDUA dan
                PIHAK KEDUA menyatakan telah menerima objek tersebut
                dalam keadaan sebagaimana tercantum dalam dokumen ini.
            </p>

            @if ($bast->description)
                <p class="section-paragraph">
                    <strong>Keterangan:</strong>
                    {{ $bast->description }}
                </p>
            @endif

            <p class="closing">
                Demikian Berita Acara Serah Terima ini dibuat dengan
                sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
            </p>

            <table class="location-date">
                <tr>
                    <td></td>

                    <td class="right">
                        Dibuat di : Cianjur
                        <br>
                        Tanggal : {{ $handoverDate }}
                    </td>
                </tr>
            </table>

            <table class="signatures">
                <tr>
                    <td>
                        <div class="signature-role">
                            PIHAK KEDUA
                        </div>

                        <div class="signature-space"></div>

                        <div class="signature-name">
                            {{ $secondParty?->name ?? '-' }}
                        </div>

                        @if ($secondParty?->position)
                            <div class="signature-detail">
                                {{ $secondParty->position }}
                            </div>
                        @endif

                        @if ($secondParty?->nip)
                            <div class="signature-detail">
                                NIP. {{ $secondParty->nip }}
                            </div>
                        @endif
                    </td>

                    <td>
                        <div class="signature-role">
                            PIHAK PERTAMA
                        </div>

                        <div class="signature-space"></div>

                        <div class="signature-name">
                            {{ $firstParty?->name ?? '-' }}
                        </div>

                        @if ($firstParty?->position)
                            <div class="signature-detail">
                                {{ $firstParty->position }}
                            </div>
                        @endif

                        @if ($firstParty?->nip)
                            <div class="signature-detail">
                                NIP. {{ $firstParty->nip }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>

            <footer class="demo-footer">
                Independent portfolio reconstruction ·
                demo data ·
                not an official production document of
                Diskominfo Kabupaten Cianjur
            </footer>
        </div>
    </main>
</div>
</body>
</html>