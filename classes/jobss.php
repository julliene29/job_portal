
<?php

class Jobs
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

   
    public function create(
        string $title,
        string $description,
        float $salary,
        string $type,
        int $createdBy
    ): bool {
        if (!in_array($type, ['on-site', 'remote'], true)) {
            return false;
        }

        if ($salary < 0) {
            return false;
        }

        $sql = "INSERT INTO jobss
                (title, description, salary, type, created_by)
                VALUES
                (:title, :description, :salary, :type, :created_by)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'title' => $title,
            'description' => $description,
            'salary' => $salary,
            'type' => $type,
            'created_by' => $createdBy
        ]);
    }

    // Get all jobs
    public function getAll(): array
    {
        $sql = "SELECT jobss.*, users.name AS admin_name
                FROM jobss
                INNER JOIN users ON jobss.created_by = users.id
                ORDER BY jobss.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get one job by ID
    public function getById(int $id): array|false
    {
        $sql = "SELECT *
                FROM jobss
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update a job
    public function update(
        int $id,
        string $title,
        string $description,
        float $salary,
        string $type
    ): bool {
        if (!in_array($type, ['on-site', 'remote'], true)) {
            return false;
        }

        if ($salary < 0) {
            return false;
        }

        $sql = "UPDATE jobss
                SET title = :title,
                    description = :description,
                    salary = :salary,
                    type = :type
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'title' => $title,
            'description' => $description,
            'salary' => $salary,
            'type' => $type,
            'id' => $id
        ]);
    }

    // Delete a job
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM jobss
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }
}
