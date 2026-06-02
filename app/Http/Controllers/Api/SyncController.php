<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceKey;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    public function register(Request $request)
    {
        $secret = bin2hex(random_bytes(32));
        $deviceId = (string) Str::uuid();

        DeviceKey::create([
            'user_id' => auth()->id(),
            'device_id' => $deviceId,
            'secret' => $secret
        ]);

        return response()->json([
            'device_id' => $deviceId,
            'secret' => $secret
        ]);
    }

    public function snapshot(Request $request)
    {
        $deviceId = $request->header('X-Device-Id');
        $device = DeviceKey::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json(['error' => 'Appareil non enregistré'], 403);
        }

        $company = $request->user()->company;
        if (!$company) {
            return response()->json(['error' => 'Entreprise non trouvée'], 404);
        }

        $data = [
            'employees' => $company->employees()->with(['payslip', 'remunerations', 'leaves', 'overtimes'])->get(),
            'company' => $company,
            'server_time' => now()->timestamp
        ];

        $signature = hash_hmac('sha256', json_encode($data), $device->secret);

        return response()->json($data)
                         ->header('X-Server-Signature', $signature);
    }

    public function sync(Request $request)
    {
        $deviceId = $request->header('X-Device-Id');
        $signature = $request->header('X-Payload-Signature');
        $payloadRaw = $request->getContent();
        $payload = json_decode($payloadRaw, true);

        $device = DeviceKey::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json(['error' => 'Appareil non enregistré'], 403);
        }

        // Vérification HMAC
        $expectedSignature = hash_hmac('sha256', $payloadRaw, $device->secret);
        if (!hash_equals($expectedSignature, (string)$signature)) {
            return response()->json(['error' => 'Signature invalide'], 403);
        }

        return DB::transaction(function () use ($payload, $device) {
            $ack = [];
            
            // PUSH
            if (isset($payload['changes'])) {
                foreach ($payload['changes'] as $change) {
                    // Ici on simule pour Employee, à généraliser selon les besoins
                    $employee = Employee::find($change['id']);
                    $data = $change['data']; // Note: dans une vraie version, le serveur déchiffrerait si besoin ou stockerait brut

                    if ($employee) {
                        if (strtotime($change['updated_at']) > $employee->updated_at->timestamp) {
                            $employee->update($data);
                        }
                    } else {
                        Employee::create(array_merge($data, ['id' => $change['id']]));
                    }
                    $ack[] = $change['id'];
                }
            }

            // PULL
            $updates = Employee::where('updated_at', '>', $device->last_sync_at ?? '1970-01-01')->get();
            $device->update(['last_sync_at' => now()]);

            $responseData = [
                'updates' => $updates,
                'ack' => $ack,
                'server_time' => now()->timestamp
            ];

            $responseSignature = hash_hmac('sha256', json_encode($responseData), $device->secret);

            return response()->json($responseData)
                             ->header('X-Server-Signature', $responseSignature);
        });
    }
}
