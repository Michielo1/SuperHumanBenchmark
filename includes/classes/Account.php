<?php
/**
 * Account Model Class
 * Represents an Account in the database
 */

require_once __DIR__ . '/../bootstrap.php';
require_once INCLUDES_PATH . '/Database.php';

class Account {
    private ?int $id = null;
    private string $username;
    private string $first_name;
    private ?string $infix = null;
    private string $last_name;
    private string $email;
    private string $wachtwoord;
    private ?string $banner_image = null;
    private ?string $profile_image = null;
    private ?string $about_you = null;
    private ?string $created_at = null;
    private ?string $reset_token = null;
    private ?string $reset_token_expires = null;
    private bool $is_admin = false;

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
        if (isset($data['username'])) $this->username = $data['username'];
        if (isset($data['first_name'])) $this->first_name = $data['first_name'];
        if (isset($data['infix'])) $this->infix = $data['infix'];
        if (isset($data['last_name'])) $this->last_name = $data['last_name'];
        if (isset($data['email'])) $this->email = $data['email'];
        if (isset($data['wachtwoord'])) $this->wachtwoord = $data['wachtwoord'];
        if (isset($data['banner_image'])) $this->banner_image = $data['banner_image'];
        if (isset($data['profile_image'])) $this->profile_image = $data['profile_image'];
        if (isset($data['about_you'])) $this->about_you = $data['about_you'];
        if (isset($data['created_at'])) $this->created_at = $data['created_at'];
        if (isset($data['reset_token'])) $this->reset_token = $data['reset_token'];
        if (isset($data['reset_token_expires'])) $this->reset_token_expires = $data['reset_token_expires'];
        if (isset($data['is_admin'])) $this->is_admin = (bool)$data['is_admin'];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getFirstName(): string { return $this->first_name; }
    public function getInfix(): ?string { return $this->infix; }
    public function getLastName(): string { return $this->last_name; }
    public function getEmail(): string { return $this->email; }
    public function getWachtwoord(): string { return $this->wachtwoord; }
    public function getBannerImage(): ?string { return $this->banner_image; }
    public function getProfileImage(): ?string { return $this->profile_image; }
    public function getAboutYou(): ?string { return $this->about_you; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getResetToken(): ?string { return $this->reset_token; }
    public function getResetTokenExpires(): ?string { return $this->reset_token_expires; }

    // Setters
    public function setUsername(string $username): void { $this->username = $username; }
    public function setFirstName(string $first_name): void { $this->first_name = $first_name; }
    public function setInfix(?string $infix): void { $this->infix = $infix; }
    public function setLastName(string $last_name): void { $this->last_name = $last_name; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setWachtwoord(string $wachtwoord): void { $this->wachtwoord = $wachtwoord; }
    public function setBannerImage(?string $banner_image): void { $this->banner_image = $banner_image; }
    public function setProfileImage(?string $profile_image): void { $this->profile_image = $profile_image; }
    public function setAboutYou(?string $about_you): void { $this->about_you = $about_you; }

    public function setResetToken(string $token, string $expires): void {
        $this->reset_token = $token;
        $this->reset_token_expires = $expires;
    }

    public function clearResetToken(): void {
        $this->reset_token = null;
        $this->reset_token_expires = null;
    }

    public function getIsAdmin(): bool { return $this->is_admin; }
    public function setIsAdmin(bool $is_admin): void { $this->is_admin = $is_admin; }

    /**
     * Find account by ID
     *
     * @param int $id
     * @return Account|null
     */
    public static function findById(int $id): ?Account {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM account WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();
        return $data ? new Account($data) : null;
    }

    /**
     * Find account by email
     *
     * @param string $email
     * @return Account|null
     */
    public static function findByEmail(string $email): ?Account {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM account WHERE email = :email");
        $stmt->execute(['email' => $email]);

        $data = $stmt->fetch();
        return $data ? new Account($data) : null;
    }

     /**
     * Find account by username
     *
     * @param string $email
     * @return Account|null
     */
    public static function findByUsername(string $username): ?Account {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM account WHERE username = :username");
        $stmt->execute(['username' => $username]);

        $data = $stmt->fetch();
        return $data ? new Account($data) : null;
    }

    /**
     * Get full name (with infix if present)
     *
     * @return string
     */
    public function getFullName(): string {
        if ($this->infix) {
            return $this->first_name . ' ' . $this->infix . ' ' . $this->last_name;
        }
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get all accounts
     *
     * @return Account[]
     */
    public static function findAll(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM account ORDER BY created_at DESC");

        $accounts = [];
        while ($data = $stmt->fetch()) {
            $accounts[] = new Account($data);
        }

        return $accounts;
    }

    /**
     * Save account (insert or update)
     *
     * @return bool
     */
    public function save(): bool
    {
        if (trim($this->username) === '') {
            throw new Exception('Username is not set before save()');
        }

        $pdo = Database::getConnection();

        if ($this->id === null) {
            // Insert new account
            $stmt = $pdo->prepare(
                "INSERT INTO account (
                    username,
                    first_name,
                    infix,
                    last_name,
                    email,
                    wachtwoord,
                    banner_image,
                    profile_image,
                    about_you,
                    reset_token,
                    reset_token_expires,
                    is_admin
                ) VALUES (
                    :username,
                    :first_name,
                    :infix,
                    :last_name,
                    :email,
                    :wachtwoord,
                    :banner_image,
                    :profile_image,
                    :about_you,
                    :reset_token,
                    :reset_token_expires,
                    :is_admin
                )"
            );

            $result = $stmt->execute([
                'username' => $this->username,
                'first_name' => $this->first_name,
                'infix' => $this->infix,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'wachtwoord' => $this->wachtwoord,
                'banner_image' => $this->banner_image,
                'profile_image' => $this->profile_image,
                'about_you' => $this->about_you,
                'reset_token' => $this->reset_token,
                'reset_token_expires' => $this->reset_token_expires,
                'is_admin' => $this->is_admin ? 1 : 0,
            ]);

            if ($result) {
                $this->id = (int) $pdo->lastInsertId();
                return true;
            }

            return false;
        }

        // Update existing account
            $stmt = $pdo->prepare(
            "UPDATE account SET
                username = :username,
                first_name = :first_name,
                infix = :infix,
                last_name = :last_name,
                email = :email,
                wachtwoord = :wachtwoord,
                banner_image = :banner_image,
                profile_image = :profile_image,
                about_you = :about_you,
                reset_token = :reset_token,
                reset_token_expires = :reset_token_expires,
                is_admin = :is_admin
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $this->id,
            'username' => $this->username,
            'first_name' => $this->first_name,
            'infix' => $this->infix,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'wachtwoord' => $this->wachtwoord,
            'banner_image' => $this->banner_image,
            'profile_image' => $this->profile_image,
            'about_you' => $this->about_you,
            'reset_token' => $this->reset_token,
            'reset_token_expires' => $this->reset_token_expires,
            'is_admin' => $this->is_admin ? 1 : 0,
        ]);
    }

    /**
     * Delete account
     *
     * @return bool
     */
    public function delete(): bool {
        if ($this->id === null) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM account WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Verify password against stored hash
     *
     * @param string $password Plain text password
     * @return bool
     */
    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->wachtwoord);
    }

    /**
     * Hash and set password
     *
     * @param string $password Plain text password
     */
    public function hashAndSetPassword(string $password): void {
        $this->wachtwoord = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Convert to array (without password)
     *
     * @param bool $includePassword Whether to include password hash
     * @return array
     */
    public function toArray(bool $includePassword = false): array {
        $data = [
            'id' => $this->id,
            'username' => $this->username,
            'first_name' => $this->first_name,
            'infix' => $this->infix,
            'last_name' => $this->last_name,
            'full_name' => $this->getFullName(),
            'email' => $this->email,
            'banner_image' => $this->banner_image,
            'profile_image' => $this->profile_image,
            'about_you' => $this->about_you,
            'created_at' => $this->created_at,
            'is_admin' => $this->is_admin
        ];

        if ($includePassword) {
            $data['wachtwoord'] = $this->wachtwoord;
        }

        return $data;
    }
}
