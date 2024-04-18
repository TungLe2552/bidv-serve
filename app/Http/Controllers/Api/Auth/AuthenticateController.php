<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enum\PasswordPolicyEnum;
use App\Helpers\System\LogHelper;
use App\Helpers\System\PasswordPolicyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\WebAuthRequest;
use App\Mail\OtpMail;
use App\Models\Auth\CashFlow;
use App\Models\EmailOtp;
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
use Mail;

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
        DB::beginTransaction();
        try{
            $user = User::create([
                'email' => $request->get('email'),
                'password' => $request->get('password'),
                'user_name' => $request->get('user_name')

            ]);
            if($request->has('pin')){
                PinCode::create(['code'=>$request->get('pin'),'user_id'=>$user->id]);
            }
            CashFlow::create(['value'=>'100000000','limit'=>'50000000','user_id'=>$user->id]);
            DB::commit();
            return $this->responseSuccess();
        }catch(\Throwable $th){
            DB::rollBack();
            throw $th;
        }
    }
    public function getInfo(Request $request){
        $result = User::with(['cash'])->where('email',$request->get('email'))->first();
        return $this->responseSuccess($result);
    }
    public function bankTransactions(Request $request){
    }
    public function sendOtp(Request $request){
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
        if(!$user || !Hash::check($credentials['password'], $user->password)) {
            abort(400, 'Tên đăng nhập hoặc mật khẩu không đúng');
        }

        $otp = mt_rand(100000, 999999); // Sinh mã OTP ngẫu nhiên
        $expiredAt = now()->addSecond(30); // Thời gian hết hạn của OTP
        // Lưu OTP vào database
        EmailOtp::create([
            'email' => $credentials['email'],
            'otp_code' => $otp,
            'expired_at' => $expiredAt,
        ]);

        // Gửi OTP qua email
        Mail::to($credentials['email'])->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP has been sent']);
    }
    public function login(Request $request){
        $otp = $request->input('otp_code');
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
        if(!$user || !Hash::check($credentials['password'], $user->password)) {
            abort(400, 'Tên đăng nhập hoặc mật khẩu không đúng');
        }
        // Kiểm tra xem OTP có tồn tại và chưa hết hạn không
        $otpRecord = EmailOtp::where('email',$credentials['email'])
            ->where('otp_code', $otp)
            ->where('expired_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            // Xác thực thành công
            return response()->json(['message' => 'Mã OTP không đúng hoặc đã hết hạn'], 401);

        }
        return $this->responseSuccess($user);
    }
}
