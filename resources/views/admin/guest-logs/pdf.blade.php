<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Buku Tamu Digital</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.35;
        }
        /* Kop Surat */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .kop-logo {
            width: 80px;
            vertical-align: middle;
            text-align: center;
        }
        .kop-logo img {
            width: 70px;
            height: auto;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
        }
        .kop-text h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .kop-text h2 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: 800;
            color: #ea580c;
            letter-spacing: 1px;
        }
        .kop-text p {
            margin: 1px 0;
            font-size: 8.5pt;
            color: #475569;
        }
        .kop-divider-thick {
            border-top: 2.5px solid #0f172a;
            margin-top: 5px;
            margin-bottom: 1.5px;
        }
        .kop-divider-thin {
            border-top: 1px solid #0f172a;
            margin-bottom: 15px;
        }

        /* Title Area */
        .report-header {
            text-align: center;
            margin-bottom: 15px;
        }
        .report-header h1 {
            font-size: 13pt;
            margin: 0;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-meta {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 4px;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 25px;
        }
        .data-table th {
            background-color: #ea580c;
            color: #ffffff;
            font-weight: bold;
            padding: 7px 6px;
            border: 1px solid #c2410c;
            text-align: left;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .guest-photo {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            display: block;
            margin: 0 auto;
        }

        /* Signature Section */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-col-left {
            width: 60%;
            vertical-align: top;
            font-size: 8.5pt;
            color: #64748b;
        }
        .signature-col-right {
            width: 40%;
            vertical-align: top;
            text-align: center;
            font-size: 9.5pt;
        }
        .signature-space {
            height: 65px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
        .signature-nip {
            font-size: 8.5pt;
            color: #475569;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" />
                @endif
            </td>
            <td class="kop-text">
                <h3>PEMERINTAH KABUPATEN MAJALENGKA</h3>
                <h2>PUSAT LAYANAN EKONOMI KREATIF & PARIWISATA</h2>
                <p>Jl. K.H. Abdul Halim No. 123, Kabupaten Majalengka, Jawa Barat • Kode Pos: 45411</p>
                <p>Laman: ekraf.majalengkakab.go.id • Pos-el: pelayanan@ekraf.majalengkakab.go.id</p>
            </td>
        </tr>
    </table>
    <div class="kop-divider-thick"></div>
    <div class="kop-divider-thin"></div>

    <!-- REPORT TITLE -->
    <div class="report-header">
        <h1>LAPORAN REKAPITULASI BUKU TAMU DIGITAL</h1>
        <div class="report-meta">
            @if($startDate && $endDate)
                <strong>Periode:</strong> {{ $startDate }} s.d. {{ $endDate }} &nbsp;•&nbsp;
            @elseif($startDate)
                <strong>Mulai:</strong> {{ $startDate }} &nbsp;•&nbsp;
            @elseif($endDate)
                <strong>Sampai:</strong> {{ $endDate }} &nbsp;•&nbsp;
            @else
                <strong>Periode:</strong> Seluruh Kunjungan Tercatat &nbsp;•&nbsp;
            @endif
            <strong>Bagian:</strong> {{ $selectedDivision ? $selectedDivision->name : 'Semua Bagian / Bidang' }} &nbsp;•&nbsp;
            <strong>Total:</strong> {{ count($guests) }} Tamu &nbsp;•&nbsp;
            <strong>Dicetak:</strong> {{ $printedAt }}
        </div>
    </div>

    <!-- TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 55px; text-align: center;">Foto</th>
                <th style="width: 90px;">Waktu</th>
                <th style="width: 140px;">Nama Tamu & HP</th>
                <th style="width: 130px;">Instansi / Alamat</th>
                <th style="width: 130px;">Tujuan Bagian</th>
                <th>Keperluan Kunjungan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($guests as $index => $g)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">
                        @php
                            $pBase64 = null;
                            if (!empty($g->photo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($g->photo_path)) {
                                $fullImgPath = storage_path('app/public/' . $g->photo_path);
                                if (file_exists($fullImgPath)) {
                                    $pBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($fullImgPath));
                                }
                            }
                        @endphp
                        @if($pBase64)
                            <img src="{{ $pBase64 }}" class="guest-photo" alt="Foto" />
                        @else
                            <div style="font-size: 7pt; color: #94a3b8; text-align: center;">-</div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ \Carbon\Carbon::parse($g->visit_date)->format('d/m/Y') }}</strong><br>
                        <span style="font-size: 7.5pt; color: #64748b;">{{ substr($g->visit_time, 0, 5) }} WIB</span>
                    </td>
                    <td>
                        <strong>{{ $g->name }}</strong><br>
                        <span style="font-size: 7.5pt; color: #0284c7;">{{ $g->phone }}</span>
                    </td>
                    <td>{{ $g->institution }}</td>
                    <td>
                        <strong>{{ $g->division ? $g->division->name : '-' }}</strong>
                        @if($g->person_to_meet)
                            <br><span style="font-size: 7.5pt; color: #64748b;">Bertemu: {{ $g->person_to_meet }}</span>
                        @endif
                    </td>
                    <td>{{ $g->purpose }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 25px; color: #94a3b8;">
                        Tidak ada data kunjungan tamu pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SIGNATURE BLOCK -->
    <table class="signature-table">
        <tr>
            <td class="signature-col-left">
                <p><strong>Catatan:</strong></p>
                <p>Dokumen ini merupakan rekaman data kunjungan tamu digital resmi yang dicatat melalui modul Kiosk Buku Tamu Digital Pelayanan Ekraf Majalengka.</p>
            </td>
            <td class="signature-col-right">
                <p>Majalengka, {{ $todayDateIndo }}</p>
                <p style="font-weight: bold; margin-top: 4px;">Kepala Bagian Pelayanan & Sekretariat,</p>
                <div class="signature-space"></div>
                <p class="signature-name">DR. H. ARIS MUNANDAR, M.Si.</p>
                <p class="signature-nip">NIP. 19780812 200501 1 009</p>
            </td>
        </tr>
    </table>

</body>
</html>
