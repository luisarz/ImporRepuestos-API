<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    #[Test]
    public function loginTest(): void
    {
        $credential=['email'=>'johndoe@example.com','password'=>'password'];
        $response = $this->post("api/v1/login",$credential);
        $response->dump();
        $response->assertStatus(200);
    }
}
