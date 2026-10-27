<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestLogRequest;
use App\Models\Division;
use App\Models\GuestLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuestBookController extends Controller
{
    /**
     * Display the public kiosk / guest book form.
     */
    public function index(): View
    {
        $divisions = Division::active()->orderBy('name', 'asc')->get();

        return view('guestbook.index', compact('divisions'));
    }

    /**
     * Store a newly created guest log entry.
     */
    public function store(StoreGuestLogRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $photoData = $validated['photo'];

        // Extract base64 payload
        if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
            $photoData = substr($photoData, strpos($photoData, ',') + 1);
        }

        $decodedImage = base64_decode($photoData, true);

        if ($decodedImage === false) {
            return $this->errorResponse($request, 'Format foto kamera tidak valid, silakan ambil foto kembali.');
        }

        // Security check 1: Enforce maximum payload size (Max 5MB)
        if (strlen($decodedImage) > 5 * 1024 * 1024) {
            return $this->errorResponse($request, 'Ukuran file foto kamera melebihi batas maksimal (5MB).');
        }

        // Security check 2: Verify binary is an authentic image and validate MIME type
        $imageInfo = @getimagesizefromstring($decodedImage);
        if ($imageInfo === false || !isset($imageInfo['mime'])) {
            return $this->errorResponse($request, 'File yang dikirimkan bukan format gambar yang valid.');
        }

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!array_key_exists($imageInfo['mime'], $allowedMimes)) {
            return $this->errorResponse($request, 'Format gambar harus berupa JPEG, PNG, atau WEBP.');
        }

        $extension = $allowedMimes[$imageInfo['mime']];

        $now = Carbon::now('Asia/Jakarta');
        $dateStr = $now->toDateString();
        $timeStr = $now->toTimeString();
        $filename = 'guests/' . $dateStr . '_' . Str::random(16) . '.' . $extension;

        Storage::disk('public')->put($filename, $decodedImage);

        // Security check 3: Sanitize all string fields against HTML/script injection
        $guestLog = GuestLog::create([
            'division_id' => $validated['division_id'],
            'name' => strip_tags(trim($validated['name'])),
            'phone' => preg_replace('/[^0-9+\-\s]/', '', trim($validated['phone'])),
            'institution' => strip_tags(trim($validated['institution'])),
            'person_to_meet' => !empty($validated['person_to_meet']) ? strip_tags(trim($validated['person_to_meet'])) : null,
            'purpose' => strip_tags(trim($validated['purpose'])),
            'photo_path' => $filename,
            'visit_date' => $dateStr,
            'visit_time' => $timeStr,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data buku tamu Anda berhasil disimpan. Terima kasih atas kunjungan Anda!',
                'data' => [
                    'id' => $guestLog->id,
                    'name' => e($guestLog->name),
                ],
            ]);
        }

        return redirect()->route('guestbook.index')->with('success', 'Data buku tamu Anda berhasil disimpan. Terima kasih atas kunjungan Anda!');
    }

    /**
     * Auto-fill lookup endpoint: check if phone number has previous logs.
     */
    public function lookup(Request $request): JsonResponse
    {
        $rawPhone = (string) $request->query('phone', '');
        
        // Strict input sanitization: allow only digits, plus sign, space, hyphens
        $phone = preg_replace('/[^0-9+\-\s]/', '', trim($rawPhone));

        if (empty($phone) || strlen($phone) < 4 || strlen($phone) > 25) {
            return response()->json([
                'found' => false,
            ]);
        }

        $guest = GuestLog::where('phone', $phone)
            ->latest('id')
            ->first(['name', 'institution']);

        if (!$guest) {
            return response()->json([
                'found' => false,
            ]);
        }

        return response()->json([
            'found' => true,
            'data' => [
                'name' => e($guest->name),
                'institution' => e($guest->institution),
            ],
        ]);
    }

    /**
     * Return error response based on request format.
     */
    private function errorResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withInput()->with('error', $message);
    }
}
