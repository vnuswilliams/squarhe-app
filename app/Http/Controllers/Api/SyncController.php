<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceKey;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SyncController extends Controller
{
    private const SYNCABLE_EMPLOYEE_FIELDS = [
        'name',
        'status',
        'department',
        'job_title',
        'contract_type',
        'start_date',
        'end_date',
        'base_salary',
        'data',
    ];

    public function register(Request $request): JsonResponse
    {
        $secret = bin2hex(random_bytes(32));
        $deviceId = (string) Str::uuid();

        DeviceKey::create([
            'user_id' => $request->user()->id,
            'device_id' => $deviceId,
            'secret' => $secret,
            'last_sync_at' => null,
        ]);

        return response()->json([
            'device_id' => $deviceId,
            'secret' => $secret,
        ]);
    }

    public function snapshot(Request $request): JsonResponse
    {
        $device = $this->deviceForRequest($request);

        if (! $device) {
            return response()->json(['error' => __('offline.device_not_registered')], 403);
        }

        $company = $request->user()->company;

        if (! $company) {
            return response()->json(['error' => __('offline.company_not_found')], 404);
        }

        $responseData = $this->snapshotData($request, null);
        $device->update(['last_sync_at' => now()]);

        return $this->signedResponse($responseData, $device);
    }

    public function sync(Request $request): JsonResponse
    {
        $device = $this->deviceForRequest($request);

        if (! $device) {
            return response()->json(['error' => __('offline.device_not_registered')], 403);
        }

        $payload = json_decode($request->getContent(), true);

        if (! is_array($payload)) {
            return response()->json(['error' => __('offline.invalid_payload')], 422);
        }

        if (! $this->hasValidSignature($payload, $request->header('X-Payload-Signature'), $device)) {
            return response()->json(['error' => __('offline.invalid_signature')], 403);
        }

        if (! $request->user()->company) {
            return response()->json(['error' => __('offline.company_not_found')], 404);
        }

        $validator = Validator::make($payload, [
            'timestamp' => ['required', 'integer'],
            'changes' => ['sometimes', 'array', 'max:250'],
            'changes.*.id' => ['required', 'string', 'max:80'],
            'changes.*.store' => ['required', Rule::in(['employees'])],
            'changes.*.operation' => ['required', Rule::in(['upsert', 'delete'])],
            'changes.*.updated_at' => ['required', 'date'],
            'changes.*.data' => ['required_if:changes.*.operation,upsert', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $payload, $device): JsonResponse {
            $ack = [];
            $conflicts = [];
            $lastSyncAt = $device->last_sync_at;

            foreach ($payload['changes'] ?? [] as $change) {
                $result = $this->applyEmployeeChange($request, $change);

                if ($result['conflict'] ?? false) {
                    $conflicts[] = $result['conflict'];
                    continue;
                }

                $ack[] = [
                    'id' => $change['id'],
                    'store' => 'employees',
                    'key' => 'employees:'.$change['id'],
                    'synced_at' => now()->toISOString(),
                ];
            }

            $responseData = $this->snapshotData($request, $lastSyncAt, $ack, $conflicts);
            $device->update(['last_sync_at' => now()]);

            return $this->signedResponse($responseData, $device);
        });
    }

    private function applyEmployeeChange(Request $request, array $change): array
    {
        $company = $request->user()->company;
        $clientUpdatedAt = Carbon::parse($change['updated_at']);
        $employee = $company->employees()->whereKey($change['id'])->first();

        if (($change['operation'] ?? 'upsert') === 'delete') {
            if ($employee && $employee->updated_at->greaterThan($clientUpdatedAt)) {
                return ['conflict' => $this->conflictPayload('employees', $employee)];
            }

            $employee?->delete();

            return ['ok' => true];
        }

        $data = Arr::only($change['data'] ?? [], self::SYNCABLE_EMPLOYEE_FIELDS);

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:80'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['nullable', 'string', 'max:80'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'base_salary' => ['nullable', 'integer', 'min:0'],
            'data' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return ['conflict' => [
                'id' => $change['id'],
                'store' => 'employees',
                'reason' => 'validation',
                'errors' => $validator->errors()->toArray(),
            ]];
        }

        if ($employee && $employee->updated_at->greaterThan($clientUpdatedAt)) {
            return ['conflict' => $this->conflictPayload('employees', $employee)];
        }

        $company->employees()->updateOrCreate(
            ['id' => $change['id']],
            array_merge($validator->validated(), ['company_id' => $company->id]),
        );

        return ['ok' => true];
    }

    private function snapshotData(Request $request, ?Carbon $since = null, array $ack = [], array $conflicts = []): array
    {
        $company = $request->user()->company;
        $employees = $company->employees()
            ->with(['payslip', 'remunerations', 'leaves', 'overtimes', 'documents'])
            ->when($since, fn ($query) => $query->where('updated_at', '>', $since))
            ->get();

        return [
            'ack' => $ack,
            'conflicts' => $conflicts,
            'datasets' => [
                'companies' => [$company->fresh()],
                'employees' => $employees,
                'payslips' => $employees->pluck('payslip')->filter()->values(),
                'remunerations' => $employees->flatMap->remunerations->values(),
                'leaves' => $employees->flatMap->leaves->values(),
                'overtimes' => $employees->flatMap->overtimes->values(),
                'documents' => $employees->flatMap->documents->values(),
            ],
            'server_time' => now()->toISOString(),
        ];
    }

    private function conflictPayload(string $store, Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'store' => $store,
            'reason' => 'server_newer',
            'server_record' => $employee->fresh(),
        ];
    }

    private function deviceForRequest(Request $request): ?DeviceKey
    {
        $deviceId = $request->header('X-Device-Id');

        if (! is_string($deviceId) || $deviceId === '') {
            return null;
        }

        return DeviceKey::query()
            ->where('user_id', $request->user()->id)
            ->where('device_id', $deviceId)
            ->first();
    }

    private function hasValidSignature(array $payload, ?string $signature, DeviceKey $device): bool
    {
        if (! is_string($signature) || $signature === '') {
            return false;
        }

        return hash_equals($this->signatureFor($payload, $device), $signature);
    }

    private function signedResponse(array $responseData, DeviceKey $device): JsonResponse
    {
        return response()->json($responseData)
            ->header('X-Server-Signature', $this->signatureFor($responseData, $device));
    }

    private function signatureFor(array $payload, DeviceKey $device): string
    {
        return hash_hmac('sha256', $this->canonicalJson($payload), $device->secret);
    }

    private function canonicalJson(mixed $value): string
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                return '['.collect($value)
                    ->map(fn ($item) => $this->canonicalJson($item))
                    ->implode(',').']';
            }

            ksort($value);

            return '{'.collect($value)
                ->map(fn ($item, $key) => json_encode((string) $key).':'.$this->canonicalJson($item))
                ->implode(',').'}';
        }

        if ($value instanceof \JsonSerializable) {
            return $this->canonicalJson($value->jsonSerialize());
        }

        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            return $this->canonicalJson($value->toArray());
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
