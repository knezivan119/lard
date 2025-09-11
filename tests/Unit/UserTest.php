<?php

namespace Tests\Unit;

use App\Models\User;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $name = 'Yosemite Sam';
    protected string $email = 'jo@example.com';
    protected string $password = 'password';

    protected function setUp(): void
    {
        parent::setUp();

        $a = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt( $this->password ),
        ];

        $this->user = User::factory()->create( $a );
    }

    public function test_UserCreateAndRead(): void
    {
        $foundUser = User::find( $this->user->id );

        $this->assertInstanceOf( User::class, $foundUser );
        $this->assertEquals( $this->name, $foundUser->name );
        $this->assertEquals( $this->email, $foundUser->email );
    }

    public function test_UserUpdate(): void
    {
        $name = 'Buggs Bunny';
        $this->user->update([
            'name' => $name,
        ]);

        $this->assertEquals( $name, $this->user->fresh()->name );
    }

    // public function test_UserSoftDelete(): void
    // {
    //     $this->user->delete();

    //     $this->assertSoftDeleted( 'users', [ 'id' => $this->user->id ] );
    // }

    // public function test_UserRestoreAfterDeletion(): void
    // {
    //     $this->user->restore();
    //     $this->assertNotSoftDeleted( 'users', [ 'id' => $this->user->id ]);
    // }

    public function test_UserDestroy(): void
    {
        $this->user->forceDelete();
        $this->assertDatabaseMissing( 'users', [ 'id' => $this->user->id ]);
    }

}
