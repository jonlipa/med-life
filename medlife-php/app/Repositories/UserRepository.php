<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        return $this->fetch($this->baseUserSql() . ' WHERE u.id = :id', ['id' => $id]);
    }

    public function findByIdentifier(string $identifier): ?array
    {
        return $this->fetch(
            $this->baseUserSql() . ' WHERE u.email = :email_identifier OR u.username = :username_identifier',
            [
                'email_identifier' => $identifier,
                'username_identifier' => $identifier,
            ],
        );
    }

    public function listUsers(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        $sql = $this->baseUserSql() . ' WHERE 1 = 1';
        $params = [];

        if (($filters['role'] ?? '') !== '') {
            $sql .= ' AND u.role = :role';
            $params['role'] = $filters['role'];
        }

        if (($filters['q'] ?? '') !== '') {
            $sql .= ' AND (
                u.full_name LIKE :q_name
                OR u.email LIKE :q_email
                OR u.username LIKE :q_username
                OR COALESCE(d.specialization, "") LIKE :q_specialization
                OR COALESCE(p.medical_record_number, "") LIKE :q_mrn
            )';
            $search = '%' . trim((string) $filters['q']) . '%';
            $params['q_name'] = $search;
            $params['q_email'] = $search;
            $params['q_username'] = $search;
            $params['q_specialization'] = $search;
            $params['q_mrn'] = $search;
        }

        $sql .= " ORDER BY FIELD(u.role, 'admin', 'doctor', 'reception', 'patient'), u.full_name ASC";

        if ($limit !== null && $offset !== null) {
            $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        }

        return $this->fetchAll($sql, $params);
    }

    public function recentUsers(int $limit = 6): array
    {
        $limit = max(1, $limit);

        return $this->fetchAll(
            $this->baseUserSql() . " ORDER BY u.created_at DESC LIMIT {$limit}"
        );
    }

    public function createUser(array $data): int
    {
        return $this->insert(
            'INSERT INTO users (role, username, email, password_hash, full_name, title, phone, avatar_path, created_at)
             VALUES (:role, :username, :email, :password_hash, :full_name, :title, :phone, :avatar_path, NOW())',
            [
                'role' => $data['role'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => $data['password_hash'],
                'full_name' => $data['full_name'],
                'title' => $data['title'] ?? '',
                'phone' => $data['phone'] ?? '',
                'avatar_path' => $data['avatar_path'] ?? null,
            ],
        );
    }

    public function updateUser(int $id, array $data): void
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['full_name', 'email', 'phone', 'title'] as $field) {
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
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id',
            $params,
        );
    }

    public function enableTwoFactor(int $id, string $secret): void
    {
        $this->execute(
            'UPDATE users
             SET two_factor_secret = :secret,
                 two_factor_enabled = 1,
                 two_factor_confirmed_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'secret' => strtoupper(trim($secret)),
            ],
        );
    }

    public function disableTwoFactor(int $id): void
    {
        $this->execute(
            'UPDATE users
             SET two_factor_secret = NULL,
                 two_factor_enabled = 0,
                 two_factor_confirmed_at = NULL
             WHERE id = :id',
            ['id' => $id],
        );
    }

    public function setEmailOtpEnabled(int $id, bool $enabled): void
    {
        $this->execute(
            'UPDATE users SET email_otp_enabled = :enabled WHERE id = :id',
            [
                'id' => $id,
                'enabled' => $enabled ? 1 : 0,
            ],
        );
    }

    public function createEmailVerificationCode(
        int $userId,
        string $email,
        string $purpose,
        string $code,
        string $expiresAt
    ): void {
        $this->createEmailVerificationCodeLocal($userId, $email, $purpose, $code, $expiresAt);
    }

    public function findActiveEmailVerificationCode(int $userId, string $purpose): ?array
    {
        return $this->findActiveEmailVerificationCodeLocal($userId, $purpose);
    }

    public function consumeEmailVerificationCode(int $id): void
    {
        $this->execute(
            'UPDATE email_verification_codes SET consumed_at = NOW() WHERE id = :id',
            ['id' => $id],
        );
    }

    public function clearEmailVerificationCodes(int $userId, string $purpose): void
    {
        $this->execute(
            'UPDATE email_verification_codes
             SET consumed_at = NOW()
             WHERE user_id = :user_id AND purpose = :purpose AND consumed_at IS NULL',
            [
                'user_id' => $userId,
                'purpose' => $purpose,
            ],
        );
    }

    public function createPasswordResetToken(int $userId, string $email, string $token, string $expiresAt): void
    {
        $this->insert(
            'INSERT INTO password_reset_tokens (user_id, email, token, expires_at, created_at)
             VALUES (:user_id, :email, :token, :expires_at, NOW())',
            [
                'user_id' => $userId,
                'email' => $email,
                'token' => $token,
                'expires_at' => $expiresAt,
            ],
        );
    }

    public function findPasswordResetToken(string $token): ?array
    {
        return $this->fetch(
            'SELECT id, user_id, email, token, expires_at, used_at
             FROM password_reset_tokens
             WHERE token = :token AND used_at IS NULL AND expires_at > NOW()',
            ['token' => $token],
        );
    }

    public function resetPassword(int $userId, string $passwordHash, string $token): void
    {
        $this->execute(
            'UPDATE users SET password_hash = :password_hash WHERE id = :id',
            ['password_hash' => $passwordHash, 'id' => $userId],
        );
        $this->execute(
            'UPDATE password_reset_tokens SET used_at = NOW() WHERE token = :token',
            ['token' => $token],
        );
    }

    public function cleanExpiredResetTokens(): void
    {
        $this->execute('DELETE FROM password_reset_tokens WHERE used_at IS NOT NULL OR expires_at < NOW()');
    }

    public function countAll(): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM users');
    }

    public function countUsers(array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) FROM users u WHERE 1 = 1';
        $params = [];

        if (($filters['role'] ?? '') !== '') {
            $sql .= ' AND u.role = :role';
            $params['role'] = $filters['role'];
        }

        if (($filters['q'] ?? '') !== '') {
            $sql .= ' AND (
                u.full_name LIKE :q_name
                OR u.email LIKE :q_email
                OR u.username LIKE :q_username
            )';
            $search = '%' . trim((string) $filters['q']) . '%';
            $params['q_name'] = $search;
            $params['q_email'] = $search;
            $params['q_username'] = $search;
        }

        return (int) $this->scalar($sql, $params);
    }

    public function countByRole(string $role): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM users WHERE role = :role', ['role' => $role]);
    }

    public function countUsersByRole(array $filters = []): array
    {
        $sql = 'SELECT u.role, COUNT(*) AS cnt FROM users u WHERE 1 = 1';
        $params = [];

        if (($filters['role'] ?? '') !== '') {
            $sql .= ' AND u.role = :role';
            $params['role'] = $filters['role'];
        }

        if (($filters['q'] ?? '') !== '') {
            $sql .= ' AND (
                u.full_name LIKE :q_name
                OR u.email LIKE :q_email
                OR u.username LIKE :q_username
            )';
            $search = '%' . trim((string) $filters['q']) . '%';
            $params['q_name'] = $search;
            $params['q_email'] = $search;
            $params['q_username'] = $search;
        }

        $sql .= ' GROUP BY u.role';

        $rows = $this->fetchAll($sql, $params);
        $count = [];
        foreach ($rows as $row) {
            $count[$row['role']] = (int) $row['cnt'];
        }

        return $count;
    }

    private function createEmailVerificationCodeLocal(
        int $userId,
        string $email,
        string $purpose,
        string $code,
        string $expiresAt
    ): void {
        $this->execute(
            'UPDATE email_verification_codes
             SET consumed_at = NOW()
             WHERE user_id = :user_id AND purpose = :purpose AND consumed_at IS NULL',
            [
                'user_id' => $userId,
                'purpose' => $purpose,
            ],
        );

        $this->insert(
            'INSERT INTO email_verification_codes (user_id, email, purpose, code_hash, expires_at, created_at)
             VALUES (:user_id, :email, :purpose, :code_hash, :expires_at, NOW())',
            [
                'user_id' => $userId,
                'email' => $email,
                'purpose' => $purpose,
                'code_hash' => password_hash($code, PASSWORD_DEFAULT),
                'expires_at' => $expiresAt,
            ],
        );
    }

    private function findActiveEmailVerificationCodeLocal(int $userId, string $purpose): ?array
    {
        return $this->fetch(
            'SELECT id, user_id, email, purpose, code_hash, expires_at, consumed_at, created_at
             FROM email_verification_codes
             WHERE user_id = :user_id
               AND purpose = :purpose
               AND consumed_at IS NULL
               AND expires_at > NOW()
             ORDER BY created_at DESC, id DESC
             LIMIT 1',
            [
                'user_id' => $userId,
                'purpose' => $purpose,
            ],
        );
    }

    private function baseUserSql(): string
    {
        return '
            SELECT
                u.*,
                d.id AS doctor_profile_id,
                d.department,
                d.specialization,
                d.room,
                p.id AS patient_profile_id,
                p.medical_record_number
            FROM users u
            LEFT JOIN doctors d ON d.user_id = u.id
            LEFT JOIN patients p ON p.user_id = u.id
        ';
    }
}
