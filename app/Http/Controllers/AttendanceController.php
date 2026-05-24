<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Mahasiswa;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function scanner()
    {
        return view('project.nfc_absensi');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'serialNumber' => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'gagal',
                'namaMahasiswa' => null,
                'message' => 'Data serial number tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $serialNumber = strtoupper(trim($request->input('serialNumber')));

        if ($serialNumber === '') {
            return response()->json([
                'status' => 'gagal',
                'namaMahasiswa' => null,
                'message' => 'Serial number NFC tidak boleh kosong.',
            ], 422);
        }

        $mahasiswa = Mahasiswa::query()
            ->whereRaw('UPPER(serial_number_nfc) = ?', [$serialNumber])
            ->first();

        if (!$mahasiswa) {
            return response()->json([
                'status' => 'gagal',
                'namaMahasiswa' => null,
                'message' => 'Kartu NFC tidak terdaftar.',
            ], 404);
        }

        $today = Carbon::today()->toDateString();

        try {
            $isDuplicate = DB::transaction(function () use ($mahasiswa, $today): bool {
                $existing = Absensi::query()
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->where('attendance_date', $today)
                    ->lockForUpdate()
                    ->exists();

                if ($existing) {
                    return true;
                }

                Absensi::create([
                    'mahasiswa_id' => $mahasiswa->id,
                    'waktu_hadir' => now(),
                    'attendance_date' => $today,
                    'status' => 'hadir',
                ]);

                return false;
            }, 3);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'gagal',
                'namaMahasiswa' => $mahasiswa->nama,
                'message' => 'Terjadi kesalahan server saat menyimpan absensi.',
            ], 500);
        }

        if ($isDuplicate) {
            return response()->json([
                'status' => 'gagal',
                'namaMahasiswa' => $mahasiswa->nama,
                'message' => 'Absensi hari ini sudah tercatat.',
            ], 409);
        }

        return response()->json([
            'status' => 'sukses',
            'namaMahasiswa' => $mahasiswa->nama,
            'message' => 'Absensi berhasil disimpan.',
        ], 201);
    }
}
