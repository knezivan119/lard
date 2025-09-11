<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\UserMeta;

class UserMetaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function payload(): array
    {
        return [
            'user_id'    => $this->user->id,
            'first_name' => 'Ada',
            'last_name'  => 'Lovelace',
            'middle_name'=> null,
            'phones'     => [
                [ 'e164' => '+61412345678', 'type' => 'mobile', 'primary' => true ],
            ],
            'addresses'  => [
                [ 'line1' => '1 Sample St', 'suburb' => 'Camden', 'state' => 'NSW', 'postcode' => '2570', 'country' => 'AU', 'primary' => true ],
            ],
            'notes'      => [ 'vip' => true ],
            'extra'      => [ 'newsletter' => false ],
        ];
    }

    public function test_createEmpty(): void
    {
        $meta = UserMeta::create( [
            'user_id' => $this->user->id,
        ] );

        $this->assertSame( $meta->user_id, $meta->user?->getKey() );
        $this->assertNotNull( $meta->user_id );
        $this->assertNull( $meta->first_name );
        $this->assertNull( $meta->phones );
        $this->assertNull( $meta->addresses );
        $this->assertNull( $meta->notes );
        $this->assertNull( $meta->extra );
    }


    public function test_create(): void
    {
        $payload = $this->payload();

        $meta = UserMeta::create( $payload );

        $this->assertSame( 'Ada', $meta->first_name );
        $this->assertIsArray( $meta->phones );
        $this->assertIsArray( $meta->addresses );
        $this->assertIsArray( $meta->notes );
        $this->assertIsArray( $meta->extra );
        $this->assertSame( '+61412345678', $meta->phones[ 0 ][ 'e164' ] );
        $this->assertSame( 'NSW', $meta->addresses[ 0 ][ 'state' ] );
        $this->assertTrue( $meta->notes[ 'vip' ] );
        $this->assertFalse( $meta->extra[ 'newsletter' ] );
    }


    public function test_massAssignmentAndUpdate(): void
    {
        $meta = UserMeta::create( $this->payload() );

        $meta->update( [
            'phones' => [
                [ 'e164' => '+61280123456', 'type' => 'landline', 'primary' => false ],
            ],
        ] );

        $meta->refresh();

        $this->assertSame( '+61280123456', data_get( $meta, 'phones.0.e164' ) );
        $this->assertSame( 'NSW', data_get( $meta, 'addresses.0.state' ) );
        $this->assertTrue( data_get( $meta, 'notes.vip' ) );
        $this->assertFalse( data_get( $meta, 'extra.newsletter' ) );
    }
}
