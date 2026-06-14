<?php
/**
 * Test Model Class
 * Represents a Test/Benchmark in the database
 */

require_once __DIR__ . '/../bootstrap.php';
require_once INCLUDES_PATH . '/Database.php';

class Test {
    private ?int $id = null;
    private string $test_name;
    private ?string $test_description = null;
    private string $test_type; // 'minimize' or 'maximize'
    private ?string $created_at = null;

    /**
     * Constructor
     */
    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Populate object from array
     */
    private function hydrate(array $data): void {
        if (isset($data['id'])) $this->id = (int)$data['id'];
        if (isset($data['test_name'])) $this->test_name = $data['test_name'];
        if (isset($data['test_description'])) $this->test_description = $data['test_description'];
        elseif (isset($data['description'])) $this->test_description = $data['description'];
        if (isset($data['test_type'])) $this->test_type = $data['test_type'];
        if (isset($data['created_at'])) $this->created_at = $data['created_at'];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTestName(): string { return $this->test_name; }
    public function getTestDescription(): ?string { return $this->test_description; }
    public function getTestType(): string { return $this->test_type; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    // Setters
    public function setTestName(string $test_name): void { $this->test_name = $test_name; }
    public function setTestDescription(?string $test_description): void { $this->test_description = $test_description; }
    public function setTestType(string $test_type): void { $this->test_type = $test_type; }

    /**
     * Find test by ID
     *
     * @param int $id
     * @return Test|null
     */
    public static function findById(int $id): ?Test {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM test WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();
        return $data ? new Test($data) : null;
    }

    /**
     * Find test by name
     *
     * @param string $naam
     * @return Test|null
     */
    public static function findByTestName(string $test_name): ?Test {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM test WHERE test_name = :test_name");
        $stmt->execute(['test_name' => $test_name]);

        $data = $stmt->fetch();
        return $data ? new Test($data) : null;
    }

    /**
     * Get all tests
     *
     * @return array
     */
    public static function getAll(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM test ORDER BY id ASC");

        $tests = [];
        while ($data = $stmt->fetch()) {
            $tests[] = new Test($data);
        }
        return $tests;
    }

    /**
     * Check if test type is minimize (lower is better)
     *
     * @return bool
     */
    public function isMinimize(): bool {
        return $this->test_type === 'minimize';
    }

    /**
     * Check if test type is maximize (higher is better)
     *
     * @return bool
     */
    public function isMaximize(): bool {
        return $this->test_type === 'maximize';
    }

    /**
     * Save test to database (create or update)
     *
     * @return bool
     */
    public function save(): bool {
        $pdo = Database::getConnection();

        if ($this->id === null) {
            // Insert new test
            $stmt = $pdo->prepare(
                "INSERT INTO test (test_name, test_description, test_type)
                 VALUES (:test_name, :test_description, :test_type)"
                );

            $result = $stmt->execute([
                'test_name' => $this->test_name,
                'test_description' => $this->test_description,
                'test_type' => $this->test_type
            ]);

            if ($result) {
                $this->id = (int)$pdo->lastInsertId();
            }

            return $result;
        } else {
            // Update existing test
            $stmt = $pdo->prepare(
                "UPDATE test
                 SET test_name = :test_name, test_description = :test_description, test_type = :test_type
                 WHERE id = :id"
            );

            return $stmt->execute([
                'id' => $this->id,
                'test_name' => $this->test_name,
                'test_description' => $this->test_description,
                'test_type' => $this->test_type
            ]);
        }
    }

    /**
     * Delete test from database
     *
     * @return bool
     */
    public function delete(): bool {
        if ($this->id === null) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM test WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Convert test to array
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'test_name' => $this->test_name,
            'test_description' => $this->test_description,
            'test_type' => $this->test_type,
            'created_at' => $this->created_at
        ];
    }
}
