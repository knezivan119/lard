<?php

namespace Database\Factories;

use App\Enums\AccountStatusEnum;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        $fake = fake('en_AU');

        // return [
        //     'name' => 'Market Makers (Aust) Pty Ltd',
        //     'description' => '',
        //     'comment' => '',
        //     'data' => [
        //         'email' => 'office@marketmakers.net.au',
        //         'website' => 'https://www.marketmakers.net.au',
        //         'address' => 'Unit 2/10 Gallipoli Street',
        //         'suburb' => 'Smeaton Grange',
        //         'postcode' => '2567',
        //         'state' => 'NSW',
        //         'country' => 'AU',
        //         'phone' => '+61 2 4647-2788',
        //         // 'quotePrefix' => 'QU-',
        //         // 'quoteStart' => 10100,
        //         'invoicePrefix' => 'IN-',
        //         'invoiceStart' => 10100,
        //         'abn' => '52 052 290 721',
        //         'acn' => '052 290 721',
        //     ],
        //     'status' => AccountStatusEnum::Active,
        // ];

        return [
            'name' => ucwords( $fake->company(3, true) ),
            'description' => $fake->sentence(4),
            'comment' => '',
            'data' => [
                'email' => $fake->safeEmail(),
                'website' => $fake->safeEmail(),
                'address' => $fake->streetAddress,
                'suburb' => $fake->city,
                'postcode' => $fake->postcode,
                'state' => $fake->stateAbbr,
                'country' => 'AU',
                'phone' => $fake->phoneNumber,
                'quotePrefix' => 'QU-',
                'quoteStart' => 10200,
                // 'invoicePrefix' => 'IN-',
                // 'invoiceStart' => 10100,
                'abn' => '11 222 333 444',
                'acn' => '000 500 005',
            ],
            'status' => AccountStatusEnum::Active,
        ];
    }
}
