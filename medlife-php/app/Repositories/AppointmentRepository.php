<?php

declare(strict_types=1);

namespace App\Repositories;

final class AppointmentRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        return $this->fetch(
            'SELECT
                a.*,
                p.medical_record_number,
                pu.full_name AS patient_name,
                du.full_name AS doctor_name,
                s.name AS service_name
             FROM appointments a
             INNER JOIN patients p ON p.id = a.patient_id
             LEFT JOIN users pu ON pu.id = p.user_id
             INNER JOIN doctors d ON d.id = a.doctor_id
             INNER JOIN users du ON du.id = d.user_id
             LEFT JOIN services s ON s.id = a.service_id
             WHERE a.id = :id',
            ['id' => $id],
        );
    }

    public function listAppointments(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        $sql = '
            SELECT
                a.*,
                p.medical_record_number,
                pu.full_name AS patient_name,
                du.full_name AS doctor_name,
                s.name AS service_name
            FROM appointments a
            INNER JOIN patients p ON p.id = a.patient_id
            LEFT JOIN users pu ON pu.id = p.user_id
            INNER JOIN doctors d ON d.id = a.doctor_id
            INNER JOIN users du ON du.id = d.user_id
            LEFT JOIN services s ON s.id = a.service_id
            WHERE 1 = 1
        ';

        $params = [];
        foreach (['doctor_id', 'patient_id', 'status'] as $field) {
            if (!isset($filters[$field]) || $filters[$field] === '' || $filters[$field] === null) {
                continue;
            }

            $sql .= " AND a.{$field} = :{$field}";
            $params[$field] = $filters[$field];
        }

        $sql .= ' ORDER BY a.scheduled_for ASC';

        if ($limit !== null && $offset !== null) {
            $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        }

        return $this->fetchAll($sql, $params);
    }

    public function createAppointment(array $data): int
    {
        return $this->insert(
            'INSERT INTO appointments (
                patient_id, doctor_id, service_id, scheduled_for, status, location, created_by_user_id, notes, created_at
             ) VALUES (
                :patient_id, :doctor_id, :service_id, :scheduled_for, :status, :location, :created_by_user_id, :notes, NOW()
             )',
            [
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'service_id' => $data['service_id'],
                'scheduled_for' => $data['scheduled_for'],
                'status' => $data['status'] ?? 'requested',
                'location' => $data['location'] ?? 'Qendra Med Life',
                'created_by_user_id' => $data['created_by_user_id'],
                'notes' => $data['notes'] ?? '',
            ],
        );
    }

    public function upcomingForDoctor(int $doctorId, int $limit = 5): array
    {
        $limit = max(1, $limit);

        return $this->fetchAll(
            'SELECT
                a.*,
                p.medical_record_number,
                pu.full_name AS patient_name,
                s.name AS service_name
             FROM appointments a
             INNER JOIN patients p ON p.id = a.patient_id
             LEFT JOIN users pu ON pu.id = p.user_id
             LEFT JOIN services s ON s.id = a.service_id
             WHERE a.doctor_id = :doctor_id
               AND a.scheduled_for >= NOW()
               AND a.status NOT IN (\'completed\', \'cancelled\')
             ORDER BY a.scheduled_for ASC
             LIMIT ' . $limit,
            ['doctor_id' => $doctorId],
        );
    }

    public function upcomingForPatient(int $patientId, int $limit = 5): array
    {
        $limit = max(1, $limit);

        return $this->fetchAll(
            'SELECT
                a.*,
                du.full_name AS doctor_name,
                s.name AS service_name
             FROM appointments a
             INNER JOIN doctors d ON d.id = a.doctor_id
             INNER JOIN users du ON du.id = d.user_id
             LEFT JOIN services s ON s.id = a.service_id
             WHERE a.patient_id = :patient_id
               AND a.scheduled_for >= NOW()
               AND a.status NOT IN (\'completed\', \'cancelled\')
             ORDER BY a.scheduled_for ASC
             LIMIT ' . $limit,
            ['patient_id' => $patientId],
        );
    }

    public function countToday(): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM appointments WHERE DATE(scheduled_for) = CURDATE()');
    }

    public function updateStatus(int $appointmentId, string $status): void
    {
        $this->execute(
            'UPDATE appointments SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $appointmentId],
        );
    }

    public function countAll(): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM appointments');
    }

    public function countAppointments(array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) FROM appointments a WHERE 1 = 1';
        $params = [];

        foreach (['doctor_id', 'patient_id', 'status'] as $field) {
            if (!isset($filters[$field]) || $filters[$field] === '' || $filters[$field] === null) {
                continue;
            }

            $sql .= " AND a.{$field} = :{$field}";
            $params[$field] = $filters[$field];
        }

        return (int) $this->scalar($sql, $params);
    }
}
