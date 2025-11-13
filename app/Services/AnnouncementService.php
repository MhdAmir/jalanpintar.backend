<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;

class AnnouncementService
{
    public function importFromCsv(string $formId, UploadedFile $file): array
    {
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();
        $imported = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($records as $index => $record) {
                try {
                    $this->validateCsvRecord($record, $index);

                    // Find matching submission by phone or email
                    $submission = $this->findMatchingSubmission($formId, $record);

                    Announcement::create([
                        'form_id' => $formId,
                        'submission_id' => $submission?->id,
                        'name' => $record['name'] ?? $record['nama'],
                        'phone' => $record['phone'] ?? $record['telepon'] ?? $record['hp'],
                        'email' => $record['email'] ?? null,
                        'status' => $this->normalizeStatus($record['status']),
                        'note' => $record['note'] ?? $record['catatan'] ?? null,
                        'announced_at' => now(),
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'imported' => $imported,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'imported' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    private function validateCsvRecord(array $record, int $index): void
    {
        $requiredFields = ['identifier', 'name', 'status'];
        $alternativeFields = [
            'identifier' => ['nomor_pendaftaran', 'no_pendaftaran', 'registration_number'],
            'name' => ['nama'],
        ];

        foreach ($requiredFields as $field) {
            $found = isset($record[$field]);

            if (!$found && isset($alternativeFields[$field])) {
                foreach ($alternativeFields[$field] as $alt) {
                    if (isset($record[$alt])) {
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Validate status
        $status = $this->normalizeStatus($record['status']);
        if (!in_array($status, ['accepted', 'rejected', 'pending'])) {
            throw new \Exception("Invalid status value: " . $record['status']);
        }
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));

        $statusMap = [
            'lolos' => 'accepted',
            'lulus' => 'accepted',
            'passed' => 'accepted',
            'accepted' => 'accepted',
            'diterima' => 'accepted',
            'tidak lolos' => 'rejected',
            'tidak_lolos' => 'rejected',
            'tidaklolos' => 'rejected',
            'gagal' => 'rejected',
            'failed' => 'rejected',
            'rejected' => 'rejected',
            'ditolak' => 'rejected',
            'pending' => 'pending',
            'menunggu' => 'pending',
        ];

        return $statusMap[$status] ?? 'pending';
    }

    private function findMatchingSubmission(string $formId, array $record): ?Submission
    {
        $phone = $record['phone'] ?? $record['telepon'] ?? $record['hp'] ?? null;
        $email = $record['email'] ?? null;

        if (!$phone && !$email) {
            return null;
        }

        $query = Submission::where('form_id', $formId);

        if ($phone) {
            $query->where('phone', 'like', '%' . $phone . '%');
        }

        if ($email) {
            $query->orWhere('email', $email);
        }

        return $query->first();
    }

    public function getStatistics(string $formId): array
    {
        $announcements = Announcement::where('form_id', $formId)->get();

        return [
            'total' => $announcements->count(),
            'accepted' => $announcements->where('status', 'accepted')->count(),
            'rejected' => $announcements->where('status', 'rejected')->count(),
            'pending' => $announcements->where('status', 'pending')->count(),
        ];
    }
}
