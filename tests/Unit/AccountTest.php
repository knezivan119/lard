<?php

namespace Tests\Unit;

use App\Enums\AccountStatusEnum;
use App\Models\Account;
// use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected $account;

    protected $name = 'Test Account';
    protected $description = 'Lorem ipsum';
    protected $comment = 'Comment';
    protected $data = [];
    protected $extra = [];
    protected $status = AccountStatusEnum::Draft;

    protected function setUp(): void
    {
        parent::setUp();

        $a = [
            'name' => $this->name,
            'description' => $this->description,
            'comment' => $this->comment,
            'data' => $this->data,
            'extra' => $this->extra,
            'status' => $this->status,
        ];

        $this->account = Account::factory()->create( $a );
    }

    public function test_AccountCreateAndRead(): void
    {
        $foundAccount = Account::find( $this->account->id );

        $this->assertInstanceOf( Account::class, $foundAccount );
        $this->assertEquals( $this->name, $foundAccount->name );
        $this->assertEquals( $this->description, $foundAccount->description );
        $this->assertEquals( $this->comment, $foundAccount->comment );
        $this->assertEquals( $this->data, $foundAccount->data );
        $this->assertEquals( $this->extra, $foundAccount->extra );
        $this->assertEquals( $this->status, $foundAccount->status );
    }

    public function test_AccountUpdate(): void
    {
        $name = 'Buggs Bunny';
        $this->account->update([
            'name' => $name,
        ]);

        $this->assertEquals( $name, $this->account->fresh()->name );
    }

    // public function test_AccountSoftDelete(): void
    // {
    //     $this->account->delete();

    //     $this->assertSoftDeleted( 'accounts', [ 'id' => $this->account->id ] );
    // }

    // public function test_AccountRestoreAfterDeletion(): void
    // {
    //     $this->account->restore();
    //     $this->assertNotSoftDeleted( 'accounts', [ 'id' => $this->account->id ]);
    // }

    public function test_AccountDestroy(): void
    {
        $this->account->forceDelete();
        $this->assertDatabaseMissing( 'accounts', [ 'id' => $this->account->id ]);
    }

    #[DataProvider('statusProvider')]
    public function test_AccountStatusEnum( $status, $expected ): void
    {
        $this->assertSame( $expected, $status->isActive() );
    }

    public static function statusProvider(): array
    {
        return [
            'Active' => [ AccountStatusEnum::Active, true ],
            'Draft' => [ AccountStatusEnum::Draft, true ],
            'Cancelled' => [ AccountStatusEnum::Cancelled, false ],
            'Expired' => [ AccountStatusEnum::Expired, false ],
        ];
    }


}
