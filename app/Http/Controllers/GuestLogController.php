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
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestLogController extends Controller
{
    /**
     * Display a listing of guest logs with search and date filters.
     */
    public function index(Request $request): View
    {
        $query = GuestLog::with('division')->latest('id');

        // Filter: Keyword search (name, phone, institution, person_to_meet)
        if ($request->filled('q')) {
            $keyword = trim((string) $request->query('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
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
            'institution' => ['required', 'string', 'max:150'],
            'division_id' => ['required', 'exists:divisions,id'],
            'person_to_meet' => ['nullable', 'string', 'max:150'],
            'purpose' => ['required', 'string', 'max:2000'],
        ], [
            'name.required' => 'Nama lengkap tamu wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'institution.required' => 'Asal instansi wajib diisi.',
            'division_id.required' => 'Silakan pilih bagian/bidang tujuan.',
            'purpose.required' => 'Keperluan kunjungan wajib diisi.',
        ]);

        $guestLog->update([
            'name' => strip_tags(trim($validated['name'])),
            'phone' => preg_replace('/[^0-9+\-\s]/', '', trim($validated['phone'])),
            'institution' => strip_tags(trim($validated['institution'])),
            'division_id' => $validated['division_id'],
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
     * Export guest logs report to Excel / CSV format with full details.
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
        $filename = 'rekap-buku-tamu-' . Carbon::now('Asia/Jakarta')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($guests) {
            $handle = fopen('php://output', 'w');
            
            // Write UTF-8 BOM for Microsoft Excel proper encoding
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header Row
            fputcsv($handle, [
                'No',
                'Tanggal Kunjungan',
                'Jam Kunjungan',
                'Nama Lengkap Tamu',
                'Nomor WhatsApp / HP',
                'Asal Instansi / Alamat',
                'Bagian / Bidang Tujuan',
                'Pejabat / Staf Yang Ditemui',
                'Keperluan Kunjungan',
                'Foto Bukti (Path)',
            ]);

            $no = 1;
            foreach ($guests as $g) {
                fputcsv($handle, [
                    $no++,
                    $g->visit_date ? Carbon::parse($g->visit_date)->format('d/m/Y') : '-',
                    substr((string) $g->visit_time, 0, 5) . ' WIB',
                    $g->name,
                    "'" . $g->phone, // Prefix with apostrophe to prevent Excel dropping leading zero
                    $g->institution,
                    $g->division ? $g->division->name : '-',
                    $g->person_to_meet ?: '-',
                    $g->purpose,
                    $g->photo_url,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
