<?php

namespace App\Http\Controllers\Api\Auth;

use App\Constants\UniCode;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Auth\BankCard;
use App\Models\Auth\CashFlow;
use App\Models\Auth\Occupation;
use App\Models\Auth\Partner;
use App\Models\EmailOtp;
use App\Models\Auth\PinCode;
use App\Models\Auth\User;
use App\Traits\ResponseType;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $value = UniCode::encode;
        $email_arr = str_split($request->get('email'));
        $arr_encode = [];
        foreach ($email_arr as $item) {
            $arr_encode[] = $value[$item];
        }

        try {
            $user = User::create([
                'email' => implode("", $arr_encode),
                'password' => $request->get('password'),
                'face_id' => $request->get('face_id') || null
            ]);
            if ($request->has('pin')) {
                PinCode::create(['code' => $request->get('pin'), 'user_id' => $user->id]);
            }
            $code = mt_rand(1000000000000, 9999999999999);
            BankCard::create(['mount' => '100000000', 'limit' => '50000000', 'user_id' => $user->id,'code'=>$code]);
            Partner::create([
                'name' => $request->get('user_name'),
                'gender' => $request->get('gender'),
                'married' => $request->get('married'),
                'occupation_id' => $request->get('occupation_id'),
                'user_id' => $user->id,
                'birth_date'=>$request->get('birth_date')
            ]);
            DB::commit();
            return $this->responseSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getInfo(Request $request, $id)
    {
        $result = User::with(['bankCard','partner'])->find($id);
        return $this->responseSuccess($result);
    }
    public function sendOtp(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $value = UniCode::encode;
        $email_arr = str_split($credentials['email']);
        $arr_encode = [];
        foreach ($email_arr as $item) {
            $arr_encode[] = $value[$item];
        }
        $user = User::where('email', implode("", $arr_encode))->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
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
    public function login(Request $request)
    {
        $otp = $request->input('otp_code');
        $credentials = $request->only('email', 'password');
        $value = UniCode::encode;
        $email_arr = str_split($credentials['email']);
        $arr_encode = [];
        foreach ($email_arr as $item) {
            $arr_encode[] = $value[$item];
        }
        $user = User::where('email', implode("", $arr_encode))->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            abort(400, 'Tên đăng nhập hoặc mật khẩu không đúng');
        }
        // Kiểm tra xem OTP có tồn tại và chưa hết hạn không
        $otpRecord = EmailOtp::where('email', $credentials['email'])
            ->where('otp_code', $otp)
            ->where('expired_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            // Xác thực thành công
            return response()->json(['message' => 'Mã OTP không đúng hoặc đã hết hạn'], 401);
        }
        $token = $user->createToken('login_token');

        return $this->responseSuccess($token->plainTextToken, ['user_id' => $user->id]);
    }
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        // Xóa token
        $token->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }
    public function getOccupations(){
        $occupations = Occupation::query()->get();
        return $this->responseSuccess($occupations);
    }
}
