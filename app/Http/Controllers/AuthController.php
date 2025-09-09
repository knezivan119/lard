<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Resources\UserCurrentResource;

class AuthController extends Controller
{
    public function issueToken( Request $request )
    {
        $creds = $request->validate( [
            'email' => [ 'required', 'email' ],
            'password' => [ 'required' ],
            'device_name' => [ 'required' ],
        ] );

        $user = User::where( 'email', $creds[ 'email' ] )->first();

        if ( ! $user || ! Hash::check( $creds[ 'password' ], $user->password ) ) {
            return response()->json( [ 'message' => 'Invalid credentials' ], 422 );
        }

        $token = $user->createToken( $creds[ 'device_name' ] )->plainTextToken;

        return [ 'token' => $token ];
    }


    public function current( Request $request )
    {
        // die('babo glavu');
        $user = $request->user();
        // $user->loadMissing('meta', 'roles');
        // return true;
        return new UserCurrentResource( $user );
    }
}
