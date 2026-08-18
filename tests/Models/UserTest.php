<?php

declare(strict_types=1);

namespace App\Tests\Models;

use App\Tests\Support\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    public function testCreateStoresHashedPassword(): void
    {
        $user = new User($this->pdo);
        $id = $user->create('carol', 'secret-pass');
        $this->assertNotSame(0, $id);

        $row = $user->findByUsername('carol');
        $this->assertNotNull($row);
        $this->assertNotSame('secret-pass', $row['password_hash']);
        $this->assertTrue(password_verify('secret-pass', $row['password_hash']));
    }

    public function testWrongPasswordFailsVerification(): void
    {
        $user = new User($this->pdo);
        $user->create('dave', 'right-pass');
        $row = $user->findByUsername('dave');
        $this->assertFalse(password_verify('wrong-pass', $row['password_hash']));
    }

    public function testFindByUsernameReturnsNullOnUnknown(): void
    {
        $user = new User($this->pdo);
        $this->assertNull($user->findByUsername('nobody'));
    }
}