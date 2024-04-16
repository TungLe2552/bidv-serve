<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enum\PasswordPolicyEnum;
use App\Helpers\System\LogHelper;
use App\Helpers\System\PasswordPolicyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\WebAuthRequest;
use App\Models\Auth\CashFlow;
use App\Models\Auth\PinCode;
use App\Models\Auth\User;
use App\Models\Res\Device;
use App\Traits\ResponseType;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AuthenticateController extends Controller
{
    /**
     * Get the guard to be used during authentication.
     *
     */
    use ResponseType;
    public function guard()
    {
        return Auth::guard();
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request)
    {
        $user = User::create([
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'user_name' => $request->get('user_name')

        ]);
        PinCode::create(['code'=>$request->get('pin'),'user_id'=>$user->id]);
        CashFlow::crete(['value'=>'100000000','limit'=>'50000000','user_id'=>$user->id]);
        return $this->responseSuccess();
    }
    public function getInfo(Request $request){
        $result = User::with(['cash'])->where('email',$request->get('email'))->first();
        return $this->responseSuccess($result);
    }
    public function bankTransactions(Request $request){

    }
}
