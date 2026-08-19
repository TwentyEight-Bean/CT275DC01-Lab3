<?php

namespace CT275\Labs;

use PDO;

class Contact
{
  private ?PDO $db;
    public int $id = -1;
    public string $name = '';
    public string $phone = '';
    public string $notes = '';
    public ?string $avatar = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public array $errors = [];

  public function __construct(?PDO $pdo)
  {
    $this->db = $pdo;
  }

 public function fillFromDbRow(array $row): Contact
    {
        $this->id = (int)$row['id'];
        $this->name = $row['name'];
        $this->phone = $row['phone'];
        $this->notes = $row['notes'];
        $this->avatar = $row['avatar'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        return $this;
    }

    public function fill(array $data): Contact
    {
        $this->name = trim($data['name'] ?? '');
        $this->phone = trim($data['phone'] ?? '');
        $this->notes = trim($data['notes'] ?? '');
        if (array_key_exists('avatar', $data)) {
            $this->avatar = $data['avatar'];
        }
        return $this;
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Tên không được để trống.';
        }
        if (empty(trim($data['phone'] ?? ''))) {
            $errors['phone'] = 'Số điện thoại không được để trống.';
        } elseif (!preg_match('/^[0-9]{10,11}$/', trim($data['phone']))) {
            $errors['phone'] = 'Số điện thoại không hợp lệ (10-11 chữ số).';
        }
        $this->errors = $errors;
        return $errors;
    }

    public function all(): array
    {
        $contacts = [];
        $statement = $this->db->prepare('SELECT * FROM contacts ORDER BY id DESC');
        $statement->execute();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $contact = new Contact($this->db);
            $contact->fillFromDbRow($row);
            $contacts[] = $contact;
        }
        return $contacts;
    }

    public function count(): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM contacts');
        $statement->execute();
        return (int)$statement->fetchColumn();
    }

    public function paginate(int $offset = 0, int $limit = 10): array
    {
        $contacts = [];
        $statement = $this->db->prepare('SELECT * FROM contacts ORDER BY id DESC LIMIT :limit OFFSET :offset');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $contact = new Contact($this->db);
            $contact->fillFromDbRow($row);
            $contacts[] = $contact;
        }
        return $contacts;
    }

    public function find(int $id): ?Contact
    {
        $statement = $this->db->prepare('SELECT * FROM contacts WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->fillFromDbRow($row);
            return $this;
        }
        return null;
    }

    public function save(): bool
    {
        if ($this->id >= 0) {
            $statement = $this->db->prepare(
                'UPDATE contacts SET name = :name, phone = :phone, notes = :notes, avatar = :avatar, updated_at = NOW() WHERE id = :id'
            );
            return $statement->execute([
                'name'   => $this->name,
                'phone'  => $this->phone,
                'notes'  => $this->notes,
                'avatar' => $this->avatar,
                'id'     => $this->id
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO contacts (name, phone, notes, avatar, created_at, updated_at) VALUES (:name, :phone, :notes, :avatar, NOW(), NOW())'
            );
            $result = $statement->execute([
                'name'   => $this->name,
                'phone'  => $this->phone,
                'notes'  => $this->notes,
                'avatar' => $this->avatar
            ]);
            if ($result) {
                $this->id = (int)$this->db->lastInsertId();
            }
            return $result;
        }
    }

    public function delete(): bool
    {
        if (!empty($this->avatar)) {
            $avatarPath = __DIR__ . '/../../public/' . $this->avatar;
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }
        $statement = $this->db->prepare('DELETE FROM contacts WHERE id = :id');
        return $statement->execute(['id' => $this->id]);
    }
  
}
