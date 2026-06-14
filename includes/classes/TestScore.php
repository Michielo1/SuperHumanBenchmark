<?php
/**
 * TestScore Model Class
 * Represents a TestScore in the database (user's score for a specific test)
 */

require_once __DIR__ . '/../bootstrap.php';
require_once INCLUDES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Account.php';
require_once CLASSES_PATH . '/Test.php';

class TestScore {
    private ?int $id = null;
    private int $account_id;
    private int $test_id;
    private float $last_score;
    private float $high_score;
    private int $attempt_count = 0;
    private ?string $updated_at = null;

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
        if (isset($data['account_id'])) $this->account_id = (int)$data['account_id'];
        if (isset($data['test_id'])) $this->test_id = (int)$data['test_id'];
        if (isset($data['last_score'])) $this->last_score = (float)$data['last_score'];
        if (isset($data['high_score'])) $this->high_score = (float)$data['high_score'];
        if (isset($data['attempt_count'])) $this->attempt_count = (int)$data['attempt_count'];
        if (isset($data['updated_at'])) $this->updated_at = $data['updated_at'];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getAccountId(): int { return $this->account_id; }
    public function getTestId(): int { return $this->test_id; }
    public function getLastScore(): float { return $this->last_score; }
    public function getHighScore(): float { return $this->high_score; }
    public function getAttemptCount(): int { return $this->attempt_count; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    // Setters
    public function setAccountId(int $account_id): void { $this->account_id = $account_id; }
    public function setTestId(int $test_id): void { $this->test_id = $test_id; }
    public function setLastScore(float $last_score): void { $this->last_score = $last_score; }
    public function setHighScore(float $high_score): void { $this->high_score = $high_score; }
    public function setAttemptCount(int $attempt_count): void { $this->attempt_count = $attempt_count; }

    /**
     * Find TestScore by ID
     *
     * @param int $id
     * @return TestScore|null
     */
    public static function findById(int $id): ?TestScore {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM testscore WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();
        return $data ? new TestScore($data) : null;
    }

    /**
     * Find TestScore by account_id and test_id
     *
     * @param int $account_id
     * @param int $test_id
     * @return TestScore|null
     */
    public static function findByAccountAndTest(int $account_id, int $test_id): ?TestScore {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM testscore WHERE account_id = :account_id AND test_id = :test_id"
        );
        $stmt->execute([
            'account_id' => $account_id,
            'test_id' => $test_id
        ]);

        $data = $stmt->fetch();
        return $data ? new TestScore($data) : null;
    }

    /**
     * Get leaderboard for a specific test
     *
     * @param int $test_id
     * @param int $limit
     * @return array Array of associative arrays with score and account info
     */
    public static function getLeaderboard(int $test_id, int $limit = 15): array {
        $pdo = Database::getConnection();

        // First get the test to determine sort order
        $test = Test::findById($test_id);
        if (!$test) {
            return [];
        }

        // Determine sort order based on test type
        $sortOrder = $test->isMinimize() ? 'ASC' : 'DESC';

        $stmt = $pdo->prepare(
            "SELECT
                ts.id,
                ts.account_id,
                ts.high_score,
                ts.attempt_count,
                ts.updated_at,
                a.first_name,
                a.infix,
                a.last_name
             FROM testscore ts
             INNER JOIN account a ON ts.account_id = a.id
             WHERE ts.test_id = :test_id
             ORDER BY ts.high_score $sortOrder
             LIMIT :limit"
        );

        $stmt->bindValue(':test_id', $test_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get leaderboard by test name
     *
     * @param string $test_naam
     * @param int $limit
     * @return array Array of associative arrays with score and account info
     */
    public static function getLeaderboardByTestName(string $test_naam, int $limit = 15): array {
        $test = Test::findByTestName($test_naam);
        if (!$test) {
            return [];
        }

        return self::getLeaderboard($test->getId(), $limit);
    }

    /**
     * Get all scores for a specific account
     *
     * @param int $account_id
     * @return array
     */
    public static function getByAccountId(int $account_id): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM testscore WHERE account_id = :account_id");
        $stmt->execute(['account_id' => $account_id]);

        $scores = [];
        while ($data = $stmt->fetch()) {
            $scores[] = new TestScore($data);
        }
        return $scores;
    }

    /**
     * Save TestScore to database (create or update)
     *
     * @return bool
     */
    public function save(): bool {
        $pdo = Database::getConnection();

        if ($this->id === null) {
            // Insert new TestScore
            $stmt = $pdo->prepare(
                  "INSERT INTO testscore (account_id, test_id, last_score, high_score, attempt_count)
                      VALUES (:account_id, :test_id, :last_score, :high_score, :attempt_count)"
            );

            $result = $stmt->execute([
                'account_id' => $this->account_id,
                'test_id' => $this->test_id,
                     'last_score' => $this->last_score,
                'high_score' => $this->high_score,
                     'attempt_count' => $this->attempt_count
            ]);

            if ($result) {
                $this->id = (int)$pdo->lastInsertId();
            }

            return $result;
        } else {
            // Update existing TestScore
            $stmt = $pdo->prepare(
                "UPDATE testscore
                     SET last_score = :last_score,
                         high_score = :high_score,
                         attempt_count = :attempt_count
                 WHERE id = :id"
            );

            return $stmt->execute([
                'id' => $this->id,
                    'last_score' => $this->last_score,
                'high_score' => $this->high_score,
                    'attempt_count' => $this->attempt_count
            ]);
        }
    }

    /**
     * Delete TestScore from database
     *
     * @return bool
     */
    public function delete(): bool {
        if ($this->id === null) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM testscore WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Convert TestScore to array
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'test_id' => $this->test_id,
            'last_score' => $this->last_score,
            'high_score' => $this->high_score,
            'attempt_count' => $this->attempt_count,
            'updated_at' => $this->updated_at
        ];
    }
}
