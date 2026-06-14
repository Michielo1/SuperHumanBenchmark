<?php
/**
 * TestAttempt Model Class
 * Represents a single attempt at a test in the database
 */

require_once __DIR__ . '/../bootstrap.php';
require_once INCLUDES_PATH . '/Database.php';
require_once CLASSES_PATH . '/TestScore.php';

class TestAttempt {
    private ?int $id = null;
    private int $testscore_id;
    private float $score;
    private ?string $attempted_at = null;
    private ?array $metadata = null;
    private ?int $analytics_event_id = null;

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
        if (isset($data['testscore_id'])) $this->testscore_id = (int)$data['testscore_id'];
        if (isset($data['score'])) $this->score = (float)$data['score'];
        if (isset($data['attempted_at'])) $this->attempted_at = $data['attempted_at'];
        if (isset($data['metadata'])) {
            // Handle JSON metadata from database
            if (is_string($data['metadata'])) {
                $this->metadata = json_decode($data['metadata'], true);
            } else {
                $this->metadata = $data['metadata'];
            }
        }
        if (isset($data['analytics_event_id'])) {
            $this->analytics_event_id = $data['analytics_event_id'] !== null ? (int)$data['analytics_event_id'] : null;
        }
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTestscoreId(): int { return $this->testscore_id; }
    public function getScore(): float { return $this->score; }
    public function getAttemptedAt(): ?string { return $this->attempted_at; }
    public function getMetadata(): ?array { return $this->metadata; }

    // Setters
    public function setTestscoreId(int $testscore_id): void { $this->testscore_id = $testscore_id; }
    public function setScore(float $score): void { $this->score = $score; }
    public function setMetadata(?array $metadata): void { $this->metadata = $metadata; }
    public function getAnalyticsEventId(): ?int { return $this->analytics_event_id; }
    public function setAnalyticsEventId(?int $id): void { $this->analytics_event_id = $id; }

    /**
     * Find TestAttempt by ID
     *
     * @param int $id
     * @return TestAttempt|null
     */
    public static function findById(int $id): ?TestAttempt {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM testattempt WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();
        return $data ? new TestAttempt($data) : null;
    }

    /**
     * Get all attempts for a specific TestScore
     *
     * @param int $testscore_id
     * @param int $limit Optional limit for number of attempts to retrieve
     * @return array
     */
    public static function getByTestscoreId(int $testscore_id, ?int $limit = null): array {
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM testattempt WHERE testscore_id = :testscore_id ORDER BY attempted_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':testscore_id', $testscore_id, PDO::PARAM_INT);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();

        $attempts = [];
        while ($data = $stmt->fetch()) {
            $attempts[] = new TestAttempt($data);
        }
        return $attempts;
    }

    /**
     * Get recent attempts for an account on a specific test
     *
     * @param int $account_id
     * @param int $test_id
     * @param int $limit
     * @return array
     */
    public static function getRecentByAccountAndTest(int $account_id, int $test_id, int $limit = 10): array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
               "SELECT ta.*
             FROM testattempt ta
             INNER JOIN testscore ts ON ta.testscore_id = ts.id
             WHERE ts.account_id = :account_id AND ts.test_id = :test_id
               ORDER BY ta.attempted_at DESC
             LIMIT :limit"
        );

        $stmt->bindValue(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->bindValue(':test_id', $test_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $attempts = [];
        while ($data = $stmt->fetch()) {
            $attempts[] = new TestAttempt($data);
        }
        return $attempts;
    }

    /**
     * Save TestAttempt to database (create only - attempts shouldn't be updated)
     *
     * @return bool
     */
    public function save(): bool {
        $pdo = Database::getConnection();

        if ($this->id === null) {
            // Insert new TestAttempt
            $stmt = $pdo->prepare(
                "INSERT INTO testattempt (testscore_id, score, metadata, analytics_event_id)
                 VALUES (:testscore_id, :score, :metadata, :analytics_event_id)"
            );

            $metadata_json = $this->metadata !== null ? json_encode($this->metadata) : null;

            $result = $stmt->execute([
                'testscore_id' => $this->testscore_id,
                'score' => $this->score,
                'metadata' => $metadata_json,
                'analytics_event_id' => $this->analytics_event_id
            ]);

            if ($result) {
                $this->id = (int)$pdo->lastInsertId();
            }

            return $result;
        } else {
            // TestAttempts should not be updated after creation
            // Return false or throw exception
            return false;
        }
    }

    /**
     * Delete TestAttempt from database
     *
     * @return bool
     */
    public function delete(): bool {
        if ($this->id === null) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM testattempt WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Convert TestAttempt to array
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'testscore_id' => $this->testscore_id,
            'score' => $this->score,
            'attempted_at' => $this->attempted_at,
            'metadata' => $this->metadata,
            'analytics_event_id' => $this->analytics_event_id
        ];
    }
}
