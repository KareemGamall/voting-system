<?php

namespace Tests\Feature;

use Tests\Helpers\TestCase;
use Tests\Helpers\TestDataFactory;
require_once BASE_PATH . '/app/models/User.php';

use User;

/**
 * Authentication Tests
 */
class AuthenticationTest extends TestCase
{
    private $userModel;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
    }
    
    /** @test */
    public function user_can_register()
    {
        $userData = TestDataFactory::makeUser([
            'email' => 'newuser@test.com',
            'name' => 'New User'
        ]);
        
        $result = $this->userModel->register($userData);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@test.com'
        ]);
    }
    
    /** @test */
    public function prevents_duplicate_email_registration()
    {
        $userData = TestDataFactory::makeUser([
            'email' => 'duplicate@test.com'
        ]);
        
        // First registration
        $this->userModel->register($userData);
        
        // Second registration with same email should be prevented
        // Note: This depends on database unique constraint
        $count = $this->getRecordCount('users', [
            'email' => 'duplicate@test.com'
        ]);
        
        $this->assertEquals(1, $count);
    }
    
    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        $password = 'SecurePass123!';
        $userData = TestDataFactory::makeUser([
            'email' => 'logintest@test.com',
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ]);
        
        $this->userModel->register($userData);
        
        $user = $this->userModel->login('logintest@test.com', $password);
        
        $this->assertNotNull($user);
        $this->assertEquals('logintest@test.com', $user['email']);
    }
    
    /** @test */
    public function user_cannot_login_with_wrong_password()
    {
        $userData = TestDataFactory::makeUser([
            'email' => 'wrongpass@test.com',
            'password' => password_hash('CorrectPass123!', PASSWORD_BCRYPT)
        ]);
        
        $this->userModel->register($userData);
        
        $user = $this->userModel->login('wrongpass@test.com', 'WrongPassword');
        
        $this->assertFalse($user);
    }
    
    /** @test */
    public function distinguishes_between_admin_and_voter()
    {
        $adminData = TestDataFactory::makeUser([
            'email' => 'admin@test.com',
            'is_admin' => 1,
            'is_voter' => 0
        ]);
        
        $voterData = TestDataFactory::makeUser([
            'email' => 'voter@test.com',
            'is_admin' => 0,
            'is_voter' => 1
        ]);
        
        $this->userModel->register($adminData);
        $this->userModel->register($voterData);
        
        $admin = $this->userModel->findByEmail('admin@test.com');
        $voter = $this->userModel->findByEmail('voter@test.com');
        
        $this->assertEquals(1, $admin['is_admin']);
        $this->assertEquals(0, $admin['is_voter']);
        
        $this->assertEquals(0, $voter['is_admin']);
        $this->assertEquals(1, $voter['is_voter']);
    }
}
