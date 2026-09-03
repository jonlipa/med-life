<?php

declare(strict_types=1);

namespace App\Repositories;

final class DoctorRepository extends BaseRepository
{
    public function listDoctors(?int $limit = null, ?int $offset = null): array
    {
        $sd = $this->softDeleteClause('d');
        $spd = $this->softDeleteClause('pt');

        $sql = 'SELECT
                d.*,
                u.full_name,
                u.email,
                u.phone,
                u.avatar_path,
                COALESCE(pc.assigned_patients, 0) AS assigned_patients
             FROM doctors d
             INNER JOIN users u ON u.id = d.user_id
             LEFT JOIN (
                SELECT pt.current_doctor_id, COUNT(*) AS assigned_patients
                FROM patients pt
                WHERE 1=1' . $spd . '
                GROUP BY pt.current_doctor_id
             ) pc ON pc.current_doctor_id = d.id
             WHERE 1=1' . $sd . '
             ORDER BY u.full_name ASC';

        if ($limit !== null && $offset !== null) {
            $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        }

        return $this->fetchAll($sql);
    }

    public function featuredDoctors(int $limit = 3): array
    {
        $limit = max(1, $limit);
        $sd = $this->softDeleteClause('d');

        return $this->fetchAll(
            'SELECT d.*, u.full_name, u.email, u.phone
             FROM doctors d
             INNER JOIN users u ON u.id = d.user_id
             WHERE 1=1' . $sd . '
             ORDER BY d.id ASC
             LIMIT ' . (int) $limit
        );
    }

    public function findById(int $id): ?array
    {
        $sd = $this->softDeleteClause('d');

        return $this->fetch(
            'SELECT d.*, u.full_name, u.email, u.phone, u.id AS user_id
             FROM doctors d
             INNER JOIN users u ON u.id = d.user_id
             WHERE d.id = :id' . $sd,
            ['id' => $id],
        );
    }

    public function findByUserId(int $userId): ?array
    {
        $sd = $this->softDeleteClause('d');

        return $this->fetch(
            'SELECT d.*, u.full_name, u.email, u.phone, u.id AS user_id
             FROM doctors d
             INNER JOIN users u ON u.id = d.user_id
             WHERE d.user_id = :user_id' . $sd,
            ['user_id' => $userId],
        );
    }

    public function createForUser(int $userId, array $data): int
    {
        return $this->insert(
            'INSERT INTO doctors (
                user_id, department, specialization, experience_years, availability_text,
                room, bio, availability_notes, hero_image_path, image_path, created_at
             ) VALUES (
                :user_id, :department, :specialization, :experience_years, :availability_text,
                :room, :bio, :availability_notes, :hero_image_path, :image_path, NOW()
             )',
            [
                'user_id' => $userId,
                'department' => $data['department'] ?? 'Mjekesi e Pergjithshme',
                'specialization' => $data['specialization'] ?? 'Doktor',
                'experience_years' => (int) ($data['experience_years'] ?? 5),
                'availability_text' => $data['availability_text'] ?? 'E hene - e premte, 08:00 - 16:00',
                'room' => $data['room'] ?? 'A-01',
                'bio' => $data['bio'] ?? 'Konsulta klinike dhe ndjekje periodike e pacienteve.',
                'availability_notes' => $data['availability_notes'] ?? 'Rezervimet kryhen permes recepsionit.',
                'hero_image_path' => $data['hero_image_path'] ?? 'assets/images/doctor-hero.png',
                'image_path' => $data['image_path'] ?? 'assets/images/doctors/doctor-1.png',
            ],
        );
    }

    public function updateAvailability(int $doctorId, string $availabilityText, string $availabilityNotes): void
    {
        $this->execute(
            'UPDATE doctors
             SET availability_text = :availability_text, availability_notes = :availability_notes
             WHERE id = :id',
            [
                'availability_text' => $availabilityText,
                'availability_notes' => $availabilityNotes,
                'id' => $doctorId,
            ],
        );
    }

    public function countDoctors(): int
    {
        $sd = $this->softDeleteClause('d');
        return (int) $this->scalar('SELECT COUNT(*) FROM doctors d WHERE 1=1' . $sd);
    }

    public function departmentLoad(): array
    {
        $sd = $this->softDeleteClause('d');
        $spd = $this->softDeleteClause('pt');

        return $this->fetchAll(
            'SELECT d.department, COUNT(pt.id) AS patients_count
             FROM doctors d
             LEFT JOIN patients pt ON pt.current_doctor_id = d.id' . $spd . '
             WHERE 1=1' . $sd . '
             GROUP BY d.department
             ORDER BY patients_count DESC, d.department ASC'
        );
    }

    public function markDeleted(int $doctorId): void
    {
        $this->execute(
            'UPDATE doctors SET deleted_at = NOW() WHERE id = :id',
            ['id' => $doctorId],
        );
    }

    public function restore(int $doctorId): void
    {
        $this->execute(
            'UPDATE doctors SET deleted_at = NULL WHERE id = :id',
            ['id' => $doctorId],
        );
    }

    public function listDeleted(): array
    {
        return $this->fetchAll(
            'SELECT d.*, u.full_name, u.email
             FROM doctors d
             LEFT JOIN users u ON u.id = d.user_id
             WHERE d.deleted_at IS NOT NULL
             ORDER BY d.deleted_at DESC'
        );
    }
}
