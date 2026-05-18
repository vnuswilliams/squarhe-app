<?php

namespace App\Jobs;

use App\Enums\StatusEnum;
use App\Models\Company;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Throwable;

class ImportEmployeesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Maximum execution time (seconds).
     * Increase for very large files.
     */
    public int $timeout = 300;

    /**
     * Number of retry attempts on failure.
     */
    public int $tries = 1;

    /**
     * Fields that go into the JSON `data` column instead of direct columns.
     */
    private const DATA_FIELDS = [
        'birth_date',
        'nationality',
        'civility',
        'phone',
        'child',
        'niu',
        'cnps_number',
        'email',
        'category',
        'average_salary',
        'smic',
        'leaves_majority',
        'leaves_seniority',
        'leaves_child',
    ];

    // ──────────────────────────────────────────────
    //  Constructor
    // ──────────────────────────────────────────────

    public function __construct(
        public readonly string $tempPath,   // Storage::disk('local') relative path
        public readonly int    $companyId,
        public readonly int    $userId,
    ) {}

    // ──────────────────────────────────────────────
    //  Handle
    // ──────────────────────────────────────────────

    public function handle(SubscriptionService $subscriptionService): void
    {
        $company  = Company::findOrFail($this->companyId);
        $filePath = Storage::disk('local')->path($this->tempPath);

        if (!file_exists($filePath)) {
            Log::error('[ImportEmployees] File not found', ['path' => $filePath]);
            $this->fail(new \RuntimeException("Import file not found: {$filePath}"));
            return;
        }

        // Company-level leave defaults (fallback for each row)
        $defaultLeaves = [
            'leaves_majority'  => $company->data['monthlyLeave'] ?? 1.5,
            'leaves_seniority' => $company->data['seniorLeave']  ?? 2,
            'leaves_child'     => $company->data['childLeave']   ?? 2,
        ];

        $imported = 0;
        $failed   = 0;
        $errors   = [];

        DB::transaction(function () use (
            $company, $filePath, $defaultLeaves,
            $subscriptionService, &$imported, &$failed, &$errors
        ) {
            (new FastExcel)->import($filePath, function (array $row) use (
                $company, $defaultLeaves,
                $subscriptionService, &$imported, &$failed, &$errors
            ) {
                try {
                    $row = $this->normalizeRow($row, $defaultLeaves);

                    // Salary defaults: average_salary and smic fall back to base_salary
                    $row['average_salary'] = (empty($row['average_salary']) || (float)$row['average_salary'] === 0.0)
                        ? $row['base_salary']
                        : $row['average_salary'];

                    $row['smic'] = (empty($row['smic']) || (float)$row['smic'] === 0.0)
                        ? $row['base_salary']
                        : $row['smic'];

                    // Split into direct columns vs data JSON
                    $dataFieldValues = collect($row)->only(self::DATA_FIELDS)->toArray();
                    $employeeFields  = collect($row)->except(self::DATA_FIELDS)->toArray();

                    $employeeFields['data']   = $dataFieldValues;
                    $employeeFields['status'] = StatusEnum::APPROVED->value;

                    $company->employees()->create($employeeFields);

                    $subscriptionService->consumeEmployeeSlot($company);
                    $imported++;
                } catch (Throwable $e) {
                    $failed++;
                    $errors[] = $e->getMessage();
                    Log::warning('[ImportEmployees] Row error', [
                        'company_id' => $company->id,
                        'row'        => $row,
                        'error'      => $e->getMessage(),
                    ]);
                }

                return null; // We handle persistence ourselves
            });
        });

        // Cleanup temp file
        Storage::disk('local')->delete($this->tempPath);

        // Notify the user who triggered the import
        $this->notifyUser($imported, $failed, $errors);

        Log::info('[ImportEmployees] Completed', [
            'company_id' => $this->companyId,
            'imported'   => $imported,
            'failed'     => $failed,
        ]);
    }

    // ──────────────────────────────────────────────
    //  Failure handler
    // ──────────────────────────────────────────────

    public function failed(Throwable $exception): void
    {
        // Clean up the temp file even on hard failure
        Storage::disk('local')->delete($this->tempPath);

        Log::error('[ImportEmployees] Job failed', [
            'company_id' => $this->companyId,
            'error'      => $exception->getMessage(),
        ]);

        // Optionally notify the user of the fatal error
        $this->notifyUser(0, 0, [$exception->getMessage()], fatal: true);
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    /**
     * Normalise and trim all string values in a row.
     * Injects leave defaults if the columns are absent/empty.
     */
    private function normalizeRow(array $row, array $defaultLeaves): array
    {
        $row = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);

        // Convert empty strings to null for nullable fields
        foreach ($row as $key => $value) {
            if ($value === '') {
                $row[$key] = null;
            }
        }

        // Inject company leave defaults when not provided in the file
        foreach ($defaultLeaves as $field => $default) {
            if (empty($row[$field])) {
                $row[$field] = $default;
            }
        }

        return $row;
    }

    /**
     * Send an in-app / database notification to the user.
     * Swap the notification class for your own implementation.
     */
    private function notifyUser(int $imported, int $failed, array $errors, bool $fatal = false): void
    {
        $user = User::whereId($this->userId)->first();
        if (!$user) {
            return;
        }

        // ─────────────────────────────────────────────────────────────
        // Replace this block with your own Notification class, e.g.:
        //
        //   $user->notify(new EmployeeImportCompletedNotification(
        //       imported: $imported,
        //       failed: $failed,
        //       errors: $errors,
        //       fatal: $fatal,
        //   ));
        //
        // For now we just log so the job works out of the box.
        // ─────────────────────────────────────────────────────────────
        Log::info('[ImportEmployees] Notify user', [
            'user_id'  => $this->userId,
            'imported' => $imported,
            'failed'   => $failed,
            'fatal'    => $fatal,
        ]);
    }
}