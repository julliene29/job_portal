
<?php

class Application
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    // Get all job applications
public function getAll(): array
{
    $sql = "SELECT applications.*, 
                   users.name, 
                   users.email,
                   jobss.title
            FROM applications
            INNER JOIN users 
                ON applications.user_id = users.id
            INNER JOIN jobss 
                ON applications.job_id = jobss.id
            ORDER BY applications.applied_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Apply for a job
    public function apply(int $userId, int $jobId): bool
    {
        // Check whether the user has already applied
        $checkSql = "SELECT id
                     FROM applications
                     WHERE user_id = :user_id
                     AND job_id = :job_id";

        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([
            'user_id' => $userId,
            'job_id' => $jobId
        ]);

        if ($checkStmt->fetch()) {
            return false;
        }

        // Save the application
        $sql = "INSERT INTO applications (user_id, job_id)
                VALUES (:user_id, :job_id)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id' => $userId,
            'job_id' => $jobId
        ]);
    }

    // Get all applications submitted by one user
    public function getByUser(int $userId): array
    {
        $sql = "SELECT applications.*, jobss.title, jobss.salary, jobss.type
                FROM applications
                INNER JOIN jobss ON applications.job_id = jobss.id
                WHERE applications.user_id = :user_id
                ORDER BY applications.applied_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all applicants for one job
    public function getByJob(int $jobId): array
    {
        $sql = "SELECT applications.*, users.name, users.email
                FROM applications
                INNER JOIN users ON applications.user_id = users.id
                WHERE applications.job_id = :job_id
                ORDER BY applications.applied_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['job_id' => $jobId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update application status
    public function updateStatus(
        int $applicationId,
        string $status
    ): bool {
        $allowedStatuses = ['pending', 'accepted', 'rejected'];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $sql = "UPDATE applications
                SET status = :status
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'status' => $status,
            'id' => $applicationId
        ]);
    }
}
