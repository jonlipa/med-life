<?php

declare(strict_types=1);

namespace App\Repositories;

final class ClinicalRepository extends BaseRepository
{
    public function listServices(): array
    {
        return $this->fetchAll('SELECT * FROM services ORDER BY department ASC, name ASC');
    }

    public function addMedicalRecord(int $patientId, int $doctorId, string $diagnosis, string $allergies, string $notes): int
    {
        return $this->insert(
            'INSERT INTO medical_records (patient_id, doctor_id, diagnosis_summary, allergies, clinical_notes, updated_at)
             VALUES (:patient_id, :doctor_id, :diagnosis_summary, :allergies, :clinical_notes, NOW())',
            [
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'diagnosis_summary' => $diagnosis,
                'allergies' => $allergies,
                'clinical_notes' => $notes,
            ],
        );
    }

    public function addPrescription(int $patientId, int $doctorId, string $medicationName, string $instructions): int
    {
        return $this->insert(
            'INSERT INTO prescriptions (patient_id, doctor_id, medication_name, instructions, created_at)
             VALUES (:patient_id, :doctor_id, :medication_name, :instructions, NOW())',
            [
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'medication_name' => $medicationName,
                'instructions' => $instructions,
            ],
        );
    }

    public function addLabResult(int $patientId, int $doctorId, string $name, string $status, string $resultSummary): int
    {
        return $this->insert(
            'INSERT INTO lab_results (patient_id, doctor_id, name, status, requested_at, result_summary, completed_at)
             VALUES (:patient_id, :doctor_id, :name, :status, NOW(), :result_summary, :completed_at)',
            [
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'name' => $name,
                'status' => $status,
                'result_summary' => $resultSummary,
                'completed_at' => $status === 'completed' ? date('Y-m-d H:i:s') : null,
            ],
        );
    }

    public function latestRecordsByDoctor(int $doctorId): array
    {
        return $this->fetchAll(
            'SELECT
                mr.*,
                p.medical_record_number,
                pu.full_name AS patient_name
             FROM medical_records mr
             INNER JOIN patients p ON p.id = mr.patient_id
             LEFT JOIN users pu ON pu.id = p.user_id
             WHERE mr.doctor_id = :doctor_id
             ORDER BY mr.updated_at DESC',
            ['doctor_id' => $doctorId],
        );
    }

    public function listPrescriptionsByPatient(int $patientId): array
    {
        return $this->fetchAll(
            'SELECT
                pr.*,
                du.full_name AS doctor_name
             FROM prescriptions pr
             INNER JOIN doctors d ON d.id = pr.doctor_id
             INNER JOIN users du ON du.id = d.user_id
             WHERE pr.patient_id = :patient_id
             ORDER BY pr.created_at DESC',
            ['patient_id' => $patientId],
        );
    }

    public function listLabResultsByPatient(int $patientId): array
    {
        return $this->fetchAll(
            'SELECT
                lr.*,
                du.full_name AS doctor_name
             FROM lab_results lr
             INNER JOIN doctors d ON d.id = lr.doctor_id
             INNER JOIN users du ON du.id = d.user_id
             WHERE lr.patient_id = :patient_id
             ORDER BY lr.requested_at DESC',
            ['patient_id' => $patientId],
        );
    }

    public function listBillings(?int $patientId = null, array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        $sql = '
            SELECT
                b.*,
                p.medical_record_number,
                pu.full_name AS patient_name
            FROM billings b
            INNER JOIN patients p ON p.id = b.patient_id
            LEFT JOIN users pu ON pu.id = p.user_id
        ';
        $params = [];
        $conditions = [];

        if ($patientId !== null) {
            $conditions[] = 'b.patient_id = :patient_id';
            $params['patient_id'] = $patientId;
        }

        if (($filters['status'] ?? '') !== '') {
            $conditions[] = 'b.status = :status';
            $params['status'] = $filters['status'];
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY b.issued_at DESC';

        if ($limit !== null && $offset !== null) {
            $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        }

        return $this->fetchAll($sql, $params);
    }

    public function countBillings(?int $patientId = null, array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) FROM billings b';
        $params = [];
        $conditions = [];

        if ($patientId !== null) {
            $conditions[] = 'b.patient_id = :patient_id';
            $params['patient_id'] = $patientId;
        }

        if (($filters['status'] ?? '') !== '') {
            $conditions[] = 'b.status = :status';
            $params['status'] = $filters['status'];
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        return (int) $this->scalar($sql, $params);
    }

    public function billingSummary(): array
    {
        return [
            'total' => (float) $this->scalar('SELECT COALESCE(SUM(amount), 0) FROM billings'),
            'paid' => (float) $this->scalar("SELECT COALESCE(SUM(amount), 0) FROM billings WHERE status = 'paid'"),
            'pending' => (int) $this->scalar("SELECT COUNT(*) FROM billings WHERE status = 'pending'"),
            'overdue' => (int) $this->scalar("SELECT COUNT(*) FROM billings WHERE status = 'overdue'"),
        ];
    }

    public function listNotifications(int $userId): array
    {
        return $this->fetchAll(
            'SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC',
            ['user_id' => $userId],
        );
    }

    public function markNotificationRead(int $notificationId, int $userId): void
    {
        $this->execute(
            'UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id',
            ['id' => $notificationId, 'user_id' => $userId],
        );
    }

    public function markAllNotificationsRead(int $userId): void
    {
        $this->execute(
            'UPDATE notifications SET is_read = 1 WHERE user_id = :user_id',
            ['user_id' => $userId],
        );
    }

    public function unreadCount(int $userId): int
    {
        return (int) $this->scalar(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0',
            ['user_id' => $userId],
        );
    }

    public function listAuditEvents(int $limit = 50, ?int $offset = null): array
    {
        $limit = max(1, $limit);

        $sql = 'SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT ' . $limit;

        if ($offset !== null) {
            $limitParam = max(1, $limit);
            $offsetVal = max(0, (int) $offset);
            $sql = 'SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT ' . $limitParam . ' OFFSET ' . $offsetVal;
        }

        return $this->fetchAll($sql);
    }

    public function countAuditEvents(): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM audit_logs');
    }

    public function logAudit(?int $actorUserId, string $actorName, string $actionText, string $targetText, string $severity = 'info'): void
    {
        $this->insert(
            'INSERT INTO audit_logs (actor_user_id, actor_name, action_text, target_text, severity, created_at)
             VALUES (:actor_user_id, :actor_name, :action_text, :target_text, :severity, NOW())',
            [
                'actor_user_id' => $actorUserId,
                'actor_name' => $actorName,
                'action_text' => $actionText,
                'target_text' => $targetText,
                'severity' => $severity,
            ],
        );
    }

    public function getSettings(): array
    {
        $rows = $this->fetchAll('SELECT setting_key, setting_value FROM settings ORDER BY setting_key ASC');
        $settings = [];

        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->execute(
                'INSERT INTO settings (setting_key, setting_value, updated_at)
                 VALUES (:setting_key, :setting_value, NOW())
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()',
                [
                    'setting_key' => $key,
                    'setting_value' => (string) $value,
                ],
            );
        }
    }

    public function reportMetrics(): array
    {
        return [
            'appointments' => (int) $this->scalar('SELECT COUNT(*) FROM appointments'),
            'lab_results' => (int) $this->scalar('SELECT COUNT(*) FROM lab_results'),
            'active_bills' => (int) $this->scalar("SELECT COUNT(*) FROM billings WHERE status IN ('pending', 'overdue')"),
            'revenue_monthly' => $this->fetchAll(
                'SELECT DATE_FORMAT(issued_at, "%Y-%m") AS month_key, SUM(amount) AS total
                 FROM billings
                 GROUP BY DATE_FORMAT(issued_at, "%Y-%m")
                 ORDER BY month_key DESC
                LIMIT 6'
            ),
        ];
    }

    public function updateBillingStatus(int $billingId, string $status): void
    {
        $this->execute(
            'UPDATE billings SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $billingId],
        );
    }
}
