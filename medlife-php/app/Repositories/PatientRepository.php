<?php

declare(strict_types=1);

namespace App\Repositories;

use RuntimeException;

final class PatientRepository extends BaseRepository
{
    public function listPatients(?int $doctorId = null, ?int $limit = null, ?int $offset = null): array
    {
        $sd = $this->softDeleteClause('p');

        $sql = '
            SELECT
                p.*,
                u.full_name,
                u.email AS account_email,
                u.phone AS account_phone,
                d.id AS doctor_id,
                du.full_name AS doctor_name
            FROM patients p
            LEFT JOIN users u ON u.id = p.user_id
            LEFT JOIN doctors d ON d.id = p.current_doctor_id
            LEFT JOIN users du ON du.id = d.user_id
        ';

        $params = [];
        if ($doctorId !== null) {
            $sql .= ' WHERE p.current_doctor_id = :doctor_id' . $sd;
            $params['doctor_id'] = $doctorId;
        } else {
            $sql .= ' WHERE 1=1' . $sd;
        }

        $sql .= ' ORDER BY p.created_at DESC';

        if ($limit !== null && $offset !== null) {
            $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        }

        return $this->fetchAll($sql, $params);
    }

    public function countPatients(?int $doctorId = null): int
    {
        $sd = $this->softDeleteClause('p');

        if ($doctorId !== null) {
            return (int) $this->scalar(
                'SELECT COUNT(*) FROM patients p WHERE p.current_doctor_id = :doctor_id' . $sd,
                ['doctor_id' => $doctorId],
            );
        }

        return (int) $this->scalar('SELECT COUNT(*) FROM patients p WHERE 1=1' . $sd);
    }

    public function findById(int $id): ?array
    {
        $sd = $this->softDeleteClause('p');

        return $this->fetch(
            'SELECT
                p.*,
                u.full_name,
                u.email AS account_email,
                u.phone AS account_phone,
                d.id AS doctor_id,
                du.full_name AS doctor_name,
                du.email AS doctor_email
             FROM patients p
             LEFT JOIN users u ON u.id = p.user_id
             LEFT JOIN doctors d ON d.id = p.current_doctor_id
             LEFT JOIN users du ON du.id = d.user_id
             WHERE p.id = :id' . $sd,
            ['id' => $id],
        );
    }

    public function findByUserId(int $userId): ?array
    {
        $sd = $this->softDeleteClause('p');

        return $this->fetch(
            'SELECT
                p.*,
                u.full_name,
                u.email AS account_email,
                u.phone AS account_phone,
                d.id AS doctor_id,
                du.full_name AS doctor_name,
                du.email AS doctor_email
             FROM patients p
             INNER JOIN users u ON u.id = p.user_id
             LEFT JOIN doctors d ON d.id = p.current_doctor_id
             LEFT JOIN users du ON du.id = d.user_id
             WHERE p.user_id = :user_id' . $sd,
            ['user_id' => $userId],
        );
    }

    public function registerPatient(array $userData, array $patientData): int
    {
        $this->db->beginTransaction();

        try {
            $userId = $this->insert(
                'INSERT INTO users (role, username, email, password_hash, full_name, title, phone, created_at)
                 VALUES ("patient", :username, :email, :password_hash, :full_name, "Pacient", :phone, NOW())',
                [
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password_hash' => $userData['password_hash'],
                    'full_name' => $userData['full_name'],
                    'phone' => $userData['phone'],
                ],
            );

            $patientId = $this->insert(
                'INSERT INTO patients (
                    user_id, current_doctor_id, medical_record_number, date_of_birth, phone, email,
                    address, emergency_contact, insurance_provider, blood_type, summary, allergies,
                    clinical_notes, created_at
                 ) VALUES (
                    :user_id, :current_doctor_id, :medical_record_number, :date_of_birth, :phone, :email,
                    :address, :emergency_contact, :insurance_provider, :blood_type, :summary, :allergies,
                    :clinical_notes, NOW()
                 )',
                [
                    'user_id' => $userId,
                    'current_doctor_id' => $patientData['current_doctor_id'],
                    'medical_record_number' => $this->nextRecordNumber(),
                    'date_of_birth' => $patientData['date_of_birth'],
                    'phone' => $patientData['phone'],
                    'email' => $patientData['email'],
                    'address' => $patientData['address'],
                    'emergency_contact' => $patientData['emergency_contact'],
                    'insurance_provider' => $patientData['insurance_provider'],
                    'blood_type' => $patientData['blood_type'],
                    'summary' => $patientData['summary'],
                    'allergies' => $patientData['allergies'],
                    'clinical_notes' => $patientData['clinical_notes'],
                ],
            );

            $this->db->commit();

            return $patientId;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            if ($exception instanceof \PDOException) {
                throw $exception;
            }

            throw new RuntimeException('Patient registration failed.', 0, $exception);
        }
    }

    public function createIntakePatient(array $data, int $editorUserId): int
    {
        $this->db->beginTransaction();

        try {
            $patientId = $this->insert(
                'INSERT INTO patients (
                    user_id, current_doctor_id, medical_record_number, date_of_birth, phone, email,
                    address, emergency_contact, insurance_provider, blood_type, summary, allergies,
                    clinical_notes, created_at
                 ) VALUES (
                    NULL, :current_doctor_id, :medical_record_number, :date_of_birth, :phone, :email,
                    :address, :emergency_contact, :insurance_provider, :blood_type, :summary, :allergies,
                    :clinical_notes, NOW()
                 )',
                [
                    'current_doctor_id' => $data['current_doctor_id'],
                    'medical_record_number' => $this->nextRecordNumber(),
                    'date_of_birth' => $data['date_of_birth'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'address' => $data['address'],
                    'emergency_contact' => $data['emergency_contact'],
                    'insurance_provider' => $data['insurance_provider'],
                    'blood_type' => $data['blood_type'],
                    'summary' => $data['summary'],
                    'allergies' => $data['allergies'],
                    'clinical_notes' => $data['clinical_notes'],
                ],
            );

            $this->insert(
                'INSERT INTO intake_forms (
                    patient_id, edited_by_user_id, reason_for_visit, insurance_provider, intake_notes,
                    status, created_at, updated_at
                 ) VALUES (
                    :patient_id, :edited_by_user_id, :reason_for_visit, :insurance_provider, :intake_notes,
                    :status, NOW(), NOW()
                )',
                [
                    'patient_id' => $patientId,
                    'edited_by_user_id' => $editorUserId,
                    'reason_for_visit' => $data['reason_for_visit'],
                    'insurance_provider' => $data['insurance_provider'],
                    'intake_notes' => $data['intake_notes'],
                    'status' => $data['status'] ?? 'new',
                ],
            );

            $this->db->commit();

            return $patientId;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            if ($exception instanceof \PDOException) {
                throw $exception;
            }

            throw new RuntimeException('Reception intake failed.', 0, $exception);
        }
    }

    public function listQueue(?string $status = null): array
    {
        $sd = $this->softDeleteClause('p');

        $sql = 'SELECT
                i.*,
                p.medical_record_number,
                p.email,
                p.phone,
                d.id AS doctor_id,
                du.full_name AS doctor_name
             FROM intake_forms i
             INNER JOIN patients p ON p.id = i.patient_id
             LEFT JOIN doctors d ON d.id = p.current_doctor_id
             LEFT JOIN users du ON du.id = d.user_id
        ';

        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= ' WHERE i.status = :status' . $sd;
            $params['status'] = $status;
        } else {
            $sql .= ' WHERE 1=1' . $sd;
        }

        $sql .= ' ORDER BY i.updated_at DESC';

        return $this->fetchAll($sql, $params);
    }

    public function findQueueEntry(int $intakeId): ?array
    {
        $sd = $this->softDeleteClause('p');

        return $this->fetch(
            'SELECT i.*, p.current_doctor_id, p.medical_record_number
             FROM intake_forms i
             INNER JOIN patients p ON p.id = i.patient_id
             WHERE i.id = :id' . $sd,
            ['id' => $intakeId],
        );
    }

    public function updateQueueStatus(int $intakeId, string $status): void
    {
        $this->execute(
            'UPDATE intake_forms SET status = :status, updated_at = NOW() WHERE id = :id',
            ['status' => $status, 'id' => $intakeId],
        );
    }

    public function updatePatientByUser(int $userId, array $data): void
    {
        $fields = [];
        $params = ['user_id' => $userId];

        foreach (['phone', 'email', 'address', 'emergency_contact', 'insurance_provider', 'blood_type', 'summary', 'clinical_notes'] as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $fields[] = "{$field} = :{$field}";
            $params[$field] = $data[$field];
        }

        if ($fields === []) {
            return;
        }

        $this->execute(
            'UPDATE patients SET ' . implode(', ', $fields) . ' WHERE user_id = :user_id',
            $params,
        );
    }

    public function markDeleted(int $patientId): void
    {
        $this->execute(
            'UPDATE patients SET deleted_at = NOW() WHERE id = :id',
            ['id' => $patientId],
        );
    }

    public function restore(int $patientId): void
    {
        $this->execute(
            'UPDATE patients SET deleted_at = NULL WHERE id = :id',
            ['id' => $patientId],
        );
    }

    public function listDeleted(): array
    {
        return $this->fetchAll(
            'SELECT p.*, u.full_name, u.email AS account_email
             FROM patients p
             LEFT JOIN users u ON u.id = p.user_id
             WHERE p.deleted_at IS NOT NULL
             ORDER BY p.deleted_at DESC'
        );
    }

    private function nextRecordNumber(): string
    {
        $number = (int) $this->scalar('SELECT COALESCE(MAX(id), 0) + 24017 FROM patients');

        return 'ML-' . $number;
    }
}
