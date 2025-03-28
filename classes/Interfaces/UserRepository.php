<?php
namespace Interfaces;

use Entities\User;
use Entities\Email;

interface UserRepositoryInterface {
    public function findByEmail(Email $email): ?User;
    public function save(User $user): void;
    public function emailExists(Email $email): bool;
}

