
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
    int $createdBy,
    string $deadline
): bool {
    if (!in_array($type, ['on-site', 'remote'], true)) {
        return false;
    }

    if ($salary < 0) {
        return false;
    }

    $sql = "INSERT INTO jobss
            (title, description, salary, type, created_by, deadline)
            VALUES
            (:title, :description, :salary, :type, :created_by, :deadline)";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        'title' => $title,
        'description' => $description,
        'salary' => $salary,
        'type' => $type,
        'created_by' => $createdBy,
        'deadline' => $deadline
    ]);
}


    // Get all jobs
  

public function getAll(
    string $search = '',
    string $type = '',
    float $minSalary = 0,
    ?float $maxSalary = null
): array {
    $sql = "SELECT jobss.*, users.name AS admin_name
            FROM jobss
            INNER JOIN users ON jobss.created_by = users.id
            WHERE jobss.title LIKE :search";

    $params = [
        'search' => '%' . $search . '%'
    ];

    if (in_array($type, ['on-site', 'remote'], true)) {
        $sql .= " AND jobss.type = :type";
        $params['type'] = $type;
    }

    if ($minSalary > 0) {
        $sql .= " AND jobss.salary >= :min_salary";
        $params['min_salary'] = $minSalary;
    }

    if ($maxSalary !== null && $maxSalary >= 0) {
        $sql .= " AND jobss.salary <= :max_salary";
        $params['max_salary'] = $maxSalary;
    }

    $sql .= " ORDER BY jobss.created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

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

 
public function delete(int $id): bool
{
    // Check whether this job has applications
    $checkSql = "SELECT COUNT(*)
                 FROM applications
                 WHERE job_id = :job_id";

    $checkStmt = $this->db->prepare($checkSql);
    $checkStmt->execute(['job_id' => $id]);

    $applicationCount = (int) $checkStmt->fetchColumn();

    // If applications exist, close the job
    if ($applicationCount > 0) {
        $sql = "UPDATE jobss
                SET status = 'closed'
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    // If no applications exist, delete the job
    $sql = "DELETE FROM jobss
            WHERE id = :id";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute(['id' => $id]);
}

}