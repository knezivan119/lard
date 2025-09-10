<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Http\Resources\AccountResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
// use App\Http\Resources\AccountCurrentResource;

class AccountController extends Controller
{
    public function index( Request $request )
    {
        $perPage = $request->pagination['rowsPerPage'] ?? 10;
        $page = $request->pagination['page'] ?? 1;

        $accounts = Account::with(
                'users',
            )
            ->active()
            ->paginate( $perPage, ['*'], 'page', $page )
        ;

        return AccountResource::collection( $accounts );
    }


    public function show( Account $account )
    {
        $account->loadMissing('users');
        return new AccountResource( $account );
    }

    public function current( Request $request )
    {
        $user = $request->user();
        $account = $user->accounts()->first();
        $account->loadMissing('users');
        return new AccountResource( $account );
    }


    public function store( AccountRequest $request )
    {
        $account = DB::transaction( function() use ( $request ) {
            // return ( new Account )->store( $request->all() );
            return Account::create( $request->validated() );
        });

        $account->loadMissing('users');
        return new AccountResource( $account );
    }


    public function storeLogo( Account $account, Request $request )
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $disk = 'public';
        $storage = 'logos';
        $filename = $account->logoName();
        $path = $storage . '/' . $filename;

        if ( Storage::disk( $disk )->exists( $path ) ) {
            Storage::disk( $disk )->delete( $path );
        }

        $file = $request->file('logo');
        $mimeType = $file->getMimeType();
        $ext = match( $mimeType ) {
            'image/png' => '.png',
            'image/jpg',
            'image/jpeg' => '.jpg',
            'image/gif' => '.gif',
            // 'image/svg' => '.svg',
        };

        $logo = $request->logo->storeAs( $storage, $filename.$ext, $disk );

        $data = $account->data;
        $data['logo'] = $logo;
        $account->data = $data;

        $account->save();

        $account->loadMissing('users');
        return new AccountResource( $account );
    }


    public function update( Account $account, AccountRequest $request )
    {
        DB::transaction( function() use ( $request, $account ) {
            $account->update( $request->validated() );

            // if ( !empty( $request['meta'] ) ) {
            //     $account->meta()->updateOrCreate(
            //         ['id' => $request['meta']['id'] ?? null ],
            //         $request['meta']
            //     );
            // }
        });

        return new AccountResource( $account );
    }


    public function destroy( Account $account )
    {
        $account->delete();

        return response()->noContent();
    }
}
