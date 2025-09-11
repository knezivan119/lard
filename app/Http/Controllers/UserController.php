<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCurrentResource;


class UserController extends Controller
{
    public function index( Request $request ): mixed
    {
        $perPage = $request->pagination['rowsPerPage'] ?? 10;
        $page = $request->pagination['page'] ?? 1;

        $users = User::with(
                'meta',
                'roles',
            )
            ->paginate( $perPage, ['*'], 'page', $page )
        ;

        return UserResource::collection( $users );
    }


    public function show( User $user ): mixed
    {
        $user->loadMissing('meta', 'roles');
        return new UserResource( $user );
    }

    // public function current( Request $request )
    // {
    //     $user = $request->user();
    //     $user->loadMissing('meta', 'roles');
    //     return new UserCurrentResource( $user );
    // }


    public function store( UserRequest $request ): mixed
    {
        $user = DB::transaction( function() use ( $request ) {
            return ( new User )->store( $request->validated() );
        });

        $user->loadMissing('meta');
        return new UserResource( $user );
    }


    public function update( User $user, UserRequest $request ): mixed
    {
        DB::transaction( function() use ( $request, $user ) {
            $user->update( $request->all() );

            if ( !empty( $request['meta'] ) ) {
                $user->meta()->updateOrCreate(
                    ['user_id' => $user->id ],
                    $request['meta']
                );
            }
        });

        return new UserResource( $user );
    }


    public function destroy( User $user ): mixed
    {
        $user->delete();

        return response()->noContent();
    }
}
