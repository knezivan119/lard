<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Resources\UserCurrentResource;

class AuthController extends Controller
{
    public function issueToken( Request $request ): JsonResponse
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

        return response()->json([ 'token' => $token ]);
    }


    public function current( Request $request ): UserCurrentResource
    {
        $user = $request->user();
        $user->loadMissing('meta', 'roles');

        return new UserCurrentResource( $user );
    }
}
