<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\GuestLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestLogController extends Controller
{
    /**
     * Display a listing of guest logs with search and date filters.
     */
    public function index(Request $request): View
    {
        $query = GuestLog::with('division')->latest('id');

        // Filter: Keyword search (name, phone, social_media_handle, institution, person_to_meet)
        if ($request->filled('q')) {
            $keyword = trim((string) $request->query('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('social_media_handle', 'like', "%{$keyword}%")
                    ->orWhere('institution', 'like', "%{$keyword}%")
                    ->orWhere('person_to_meet', 'like', "%{$keyword}%")
                    ->orWhere('purpose', 'like', "%{$keyword}%");
            });
        }

        // Filter: Division
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->query('division_id'));
        }

        // Filter: Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('visit_date', '>=', $request->query('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('visit_date', '<=', $request->query('end_date'));
        }

        $guestLogs = $query->paginate(10)->withQueryString();
        $divisions = Division::orderBy('name', 'asc')->get();

        return view('admin.guest-logs.index', compact('guestLogs', 'divisions'));
    }

    /**
     * Show single guest log detail (used for AJAX modals).
     */
    public function show(GuestLog $guestLog): JsonResponse
    {
        $guestLog->load('division');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $guestLog->id,
                'name' => $guestLog->name,
                'phone' => $guestLog->phone,
                'social_media_platform' => $guestLog->social_media_platform,
                'social_media_platform_label' => $guestLog->social_media_platform_label,
                'social_media_handle' => $guestLog->social_media_handle,
                'social_media_url' => $guestLog->social_media_url,
                'institution' => $guestLog->institution,
                'division_name' => $guestLog->division ? $guestLog->division->name : '-',
                'person_to_meet' => $guestLog->person_to_meet ?: '-',
                'purpose' => $guestLog->purpose,
                'visit_date' => Carbon::parse($guestLog->visit_date)->locale('id')->isoFormat('dddd, D MMMM Y'),
                'visit_time' => substr((string) $guestLog->visit_time, 0, 5) . ' WIB',
                'photo_url' => $guestLog->photo_url,
            ],
        ]);
    }

    /**
     * Update guest log information (e.g. correcting typos).
     */
    public function update(Request $request, GuestLog $guestLog): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:25'],
            'social_media_platform' => ['nullable', 'string', 'in:instagram,tiktok,youtube'],
            'social_media_handle' => ['nullable', 'string', 'max:100'],
            'institution' => ['required', 'string', 'max:150'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'person_to_meet' => ['nullable', 'string', 'max:150'],
            'purpose' => ['required', 'string', 'max:2000'],
        ], [
            'name.required' => 'Nama lengkap tamu wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'social_media_platform.in' => 'Platform sosial media harus berupa Instagram, TikTok, atau YouTube.',
            'social_media_handle.max' => 'Username sosial media maksimal 100 karakter.',
            'institution.required' => 'Asal instansi wajib diisi.',
            'division_id.exists' => 'Bagian / bidang tujuan yang dipilih tidak valid.',
            'purpose.required' => 'Keperluan kunjungan wajib diisi.',
        ]);

        $socialMedia = GuestLog::normalizeSocialMedia(
            $validated['social_media_platform'] ?? null,
            $validated['social_media_handle'] ?? null
        );

        $guestLog->update([
            'name' => strip_tags(trim($validated['name'])),
            'phone' => preg_replace('/[^0-9+\-\s]/', '', trim($validated['phone'])),
            'social_media_platform' => $socialMedia['platform'],
            'social_media_handle' => $socialMedia['handle'],
            'institution' => strip_tags(trim($validated['institution'])),
            'division_id' => !empty($validated['division_id']) ? $validated['division_id'] : null,
            'person_to_meet' => !empty($validated['person_to_meet']) ? strip_tags(trim($validated['person_to_meet'])) : null,
            'purpose' => strip_tags(trim($validated['purpose'])),
        ]);

        return back()->with('success', 'Data kunjungan tamu berhasil diperbarui.');
    }

    /**
     * Remove the specified guest log from storage.
     */
    public function destroy(GuestLog $guestLog): RedirectResponse
    {
        if (!empty($guestLog->photo_path) && Storage::disk('public')->exists($guestLog->photo_path)) {
            // Do not delete sample dummy photo if it's the default
            if ($guestLog->photo_path !== 'guests/sample_guest.png') {
                Storage::disk('public')->delete($guestLog->photo_path);
            }
        }

        $guestLog->delete();

        return back()->with('success', 'Data buku tamu berhasil dihapus.');
    }

    /**
     * Export guest logs report to PDF with Kop Surat, photo proofs, and signature block.
     */
    public function exportPdf(Request $request): Response
    {
        $query = GuestLog::with('division')->orderBy('visit_date', 'desc')->orderBy('visit_time', 'desc');

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->query('division_id'));
        }
        if ($request->filled('start_date')) {
            $query->whereDate('visit_date', '>=', $request->query('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('visit_date', '<=', $request->query('end_date'));
        }

        $guests = $query->get();

        $startDate = $request->query('start_date') 
            ? Carbon::parse($request->query('start_date'))->locale('id')->isoFormat('D MMMM Y')
            : null;
        $endDate = $request->query('end_date') 
            ? Carbon::parse($request->query('end_date'))->locale('id')->isoFormat('D MMMM Y')
            : null;

        $selectedDivision = $request->filled('division_id')
            ? Division::find($request->query('division_id'))
            : null;

        $printedAt = Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm') . ' WIB';
        $todayDateIndo = Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y');

        // Embed base64 logo for dompdf reliability
        $logoBase64 = '';
        $logoPath = public_path('images/ekraf.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('admin.guest-logs.pdf', compact(
            'guests',
            'startDate',
            'endDate',
            'selectedDivision',
            'printedAt',
            'todayDateIndo',
            'logoBase64'
        ));

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('laporan-buku-tamu-' . Carbon::now('Asia/Jakarta')->format('Ymd-His') . '.pdf');
    }

    /**
     * Export guest logs report to styled Excel (.xlsx) format with proper spacing and formatting.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $query = GuestLog::with('division')->orderBy('visit_date', 'desc')->orderBy('visit_time', 'desc');

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->query('division_id'));
        }
        if ($request->filled('start_date')) {
            $query->whereDate('visit_date', '>=', $request->query('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('visit_date', '<=', $request->query('end_date'));
        }

        $guests = $query->get();

        $startDate = $request->query('start_date')
            ? Carbon::parse($request->query('start_date'))->locale('id')->isoFormat('D MMMM Y')
            : null;
        $endDate = $request->query('end_date')
            ? Carbon::parse($request->query('end_date'))->locale('id')->isoFormat('D MMMM Y')
            : null;
        $selectedDivision = $request->filled('division_id')
            ? Division::find($request->query('division_id'))
            : null;
        $printedAt = Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y, HH:mm') . ' WIB';

        $periodLabel = match (true) {
            $startDate && $endDate => "{$startDate} s.d. {$endDate}",
            (bool) $startDate => "Mulai {$startDate}",
            (bool) $endDate => "Sampai {$endDate}",
            default => 'Seluruh Kunjungan Tercatat',
        };
        $divisionLabel = $selectedDivision ? $selectedDivision->name : 'Semua Bagian / Bidang';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Sistem Buku Tamu Digital Ekraf Majalengka')
            ->setTitle('Laporan Rekapitulasi Buku Tamu Digital');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Buku Tamu');

        // Default font & print layout
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.4)->setRight(0.4);

        // Row 1: Main Title Banner
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI BUKU TAMU DIGITAL');
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EA580C']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Row 2: Subtitle
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'Pusat Pelayanan Terpadu Ekonomi Kreatif & Pariwisata • Kabupaten Majalengka');
        $sheet->getRowDimension(2)->setRowHeight(21);
        $sheet->getStyle('A2:K2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '9A3412']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF7ED']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Row 3: Metadata Info
        $sheet->mergeCells('A3:K3');
        $sheet->setCellValue('A3', "Periode: {$periodLabel}   |   Bagian: {$divisionLabel}   |   Total: {$guests->count()} Tamu   |   Dicetak: {$printedAt}");
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getStyle('A3:K3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '475569']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF7ED']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'FDBA74']],
            ],
        ]);

        // Row 4: Spacer Row
        $sheet->getRowDimension(4)->setRowHeight(10);

        // Row 5: Table Headers
        $headers = [
            'A' => ['label' => 'No', 'width' => 6],
            'B' => ['label' => 'Tanggal', 'width' => 14],
            'C' => ['label' => 'Jam', 'width' => 12],
            'D' => ['label' => 'Nama Lengkap Tamu', 'width' => 25],
            'E' => ['label' => 'No. WhatsApp / HP', 'width' => 19],
            'F' => ['label' => 'Sosial Media', 'width' => 24],
            'G' => ['label' => 'Asal Instansi / Alamat', 'width' => 26],
            'H' => ['label' => 'Bagian / Bidang Tujuan', 'width' => 23],
            'I' => ['label' => 'Pejabat / Staf Ditemui', 'width' => 22],
            'J' => ['label' => 'Keperluan Kunjungan', 'width' => 38],
            'K' => ['label' => 'Foto Bukti', 'width' => 15],
        ];

        $headerRow = 5;
        $sheet->getRowDimension($headerRow)->setRowHeight(28);
        foreach ($headers as $col => $meta) {
            $sheet->setCellValue("{$col}{$headerRow}", $meta['label']);
            $sheet->getColumnDimension($col)->setWidth($meta['width']);
        }

        $sheet->getStyle("A{$headerRow}:K{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '334155']],
            ],
        ]);

        // Freeze header & enable AutoFilter
        $sheet->freezePane('A6');
        $sheet->setAutoFilter("A{$headerRow}:K{$headerRow}");

        // Data Rows
        $currentRow = 6;
        if ($guests->isEmpty()) {
            $sheet->mergeCells("A{$currentRow}:K{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'Tidak ada data kunjungan tamu pada periode ini.');
            $sheet->getRowDimension($currentRow)->setRowHeight(30);
            $sheet->getStyle("A{$currentRow}:K{$currentRow}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '64748B']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                ],
            ]);
            $currentRow++;
        } else {
            $no = 1;
            foreach ($guests as $g) {
                $visitDateStr = $g->visit_date ? Carbon::parse($g->visit_date)->format('d/m/Y') : '-';
                $visitTimeStr = substr((string) $g->visit_time, 0, 5) . ' WIB';
                $socialDisplay = $g->social_media_display ?: '-';
                $divisionName = $g->division ? $g->division->name : '-';
                $personToMeet = $g->person_to_meet ?: '-';

                $sheet->setCellValue("A{$currentRow}", $no);
                $sheet->setCellValue("B{$currentRow}", $visitDateStr);
                $sheet->setCellValue("C{$currentRow}", $visitTimeStr);
                $sheet->setCellValue("D{$currentRow}", $g->name);
                $sheet->setCellValueExplicit("E{$currentRow}", (string) $g->phone, DataType::TYPE_STRING);
                $sheet->setCellValue("F{$currentRow}", $socialDisplay);
                $sheet->setCellValue("G{$currentRow}", $g->institution);
                $sheet->setCellValue("H{$currentRow}", $divisionName);
                $sheet->setCellValue("I{$currentRow}", $personToMeet);
                $sheet->setCellValue("J{$currentRow}", $g->purpose);
                $sheet->setCellValue("K{$currentRow}", 'Lihat Foto');

                if ($g->social_media_url) {
                    $sheet->getCell("F{$currentRow}")->getHyperlink()->setUrl($g->social_media_url);
                    $sheet->getStyle("F{$currentRow}")->getFont()->setColor(new Color('EA580C'))->setUnderline(true);
                }

                $sheet->getCell("K{$currentRow}")->getHyperlink()->setUrl($g->photo_url);
                $sheet->getStyle("K{$currentRow}")->getFont()->setColor(new Color('2563EB'))->setUnderline(true);

                // Calculate comfortable row height for wrapped content
                $purposeLines = max(1, (int) ceil(mb_strlen((string) $g->purpose) / 34)) + substr_count((string) $g->purpose, "\n");
                $instLines = max(1, (int) ceil(mb_strlen((string) $g->institution) / 22));
                $maxLines = max($purposeLines, $instLines);
                $rowHeight = max(24, min(90, $maxLines * 15 + 10));
                $sheet->getRowDimension($currentRow)->setRowHeight($rowHeight);

                // Zebra striping + clean borders
                $rowBg = ($no % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
                $sheet->getStyle("A{$currentRow}:K{$currentRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                    ],
                ]);

                // Column-specific horizontal alignment & emphasis
                $sheet->getStyle("A{$currentRow}:C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("K{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$currentRow}:J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("D{$currentRow}")->getFont()->setBold(true);

                $no++;
                $currentRow++;
            }
        }

        // Summary Footer Row
        $sheet->mergeCells("A{$currentRow}:K{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", "Total Kunjungan Tercatat: {$guests->count()} Tamu");
        $sheet->getRowDimension($currentRow)->setRowHeight(24);
        $sheet->getStyle("A{$currentRow}:K{$currentRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '9A3412']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF7ED']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDBA74']],
            ],
        ]);

        $filename = 'rekap-buku-tamu-' . Carbon::now('Asia/Jakarta')->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
