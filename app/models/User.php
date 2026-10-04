<?php
/**
 * Hesabdar - User Model & Authentication Security
 */

require_once __DIR__ . '/Model.php';

class User extends Model {
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, name, email, status, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Authenticate credentials with password_verify
     */
    public function authenticate(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        if ($user['status'] !== 'active') {
            return null;
        }

        if (password_verify($password, $user['password_hash'])) {
            // Optional: rehash if algorithmic cost changed
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
                $update->execute(['hash' => $newHash, 'id' => $user['id']]);
            }
            return $user;
        }

        return null;
    }

    /**
     * Check if client IP or email is currently locked out due to excessive failed attempts
     */
    public function isLockedOut(string $ip, string $email, int $maxAttempts = 5, int $lockoutMinutes = 15): bool {
        $since = date('Y-m-d H:i:s', time() - ($lockoutMinutes * 60));

        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total 
            FROM login_attempts 
            WHERE (ip_address = :ip OR email = :email) 
              AND attempted_at >= :since
        ");
        $stmt->execute([
            'ip' => $ip,
            'email' => strtolower(trim($email)),
            'since' => $since
        ]);

        $count = (int)$stmt->fetchColumn();
        return $count >= $maxAttempts;
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt(string $ip, string $email): void {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (ip_address, email, attempted_at) 
            VALUES (:ip, :email, NOW())
        ");
        $stmt->execute([
            'ip' => $ip,
            'email' => strtolower(trim($email))
        ]);

        // Cleanup stale attempts older than 24 hours
        $cleanup = $this->db->prepare("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $cleanup->execute();
    }

    /**
     * Clear failed attempts upon successful login
     */
    public function clearLoginAttempts(string $ip, string $email): void {
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts 
            WHERE ip_address = :ip OR email = :email
        ");
        $stmt->execute([
            'ip' => $ip,
            'email' => strtolower(trim($email))
        ]);
    }

    /**
     * Update user name and email
     */
    public function updateProfile(int $id, string $name, string $email): bool {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET name = :name, email = :email 
            WHERE id = :id
        ");
        return $stmt->execute([
            'name' => trim($name),
            'email' => strtolower(trim($email)),
            'id' => $id
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_hash = :hash 
            WHERE id = :id
        ");
        return $stmt->execute([
            'hash' => $hash,
            'id' => $id
        ]);
    }

    /**
     * Create user (used in installer or admin)
     */
    public function create(string $name, string $email, string $password): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password_hash, status, created_at)
            VALUES (:name, :email, :hash, 'active', NOW())
        ");
        $stmt->execute([
            'name' => trim($name),
            'email' => strtolower(trim($email)),
            'hash' => $hash
        ]);
        return (int)$this->db->lastInsertId();
    }
}
