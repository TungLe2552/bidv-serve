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
use Crypt;
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

        // lấy dữ unicode tự định nghĩa từ constain
        $value = UniCode::encode;

        // Lấy dữ liệu email request từ client gửi xuống
        $email = $request->get('email');

        // cắt string thành mảng
        $email_arr = str_split($email);

        // chuyển email sang dạng unicode
        $arr_encode = [];
        foreach ($email_arr as $item) {
            $arr_encode[] = $value[$item];
        }
        $email_encode = implode("", $arr_encode);
        try {
            // tạo user mới
            $user = User::create([
                'email' => $email_encode,
                'password' => $request->get('password'),
                'face_id' => $request->get('face_id') || null
            ]);
            // Tạo mã pin theo user nếu có gửi xuống
            if ($request->has('pin_code')) {
                PinCode::create(['code' => $request->get('pin_code'), 'user_id' => $user->id]);
            }
            // Tạo dữ liệu tài khoản thẻ
            $code = mt_rand(1000000000000, 9999999999999);
            BankCard::create(['mount' => '100000000', 'limit' => '50000000', 'user_id' => $user->id,'code'=>$code]);

            // tạo thông tin bổ sung cho user
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
    public function login(Request $request)
    {
        // lấy thông tin từ request
        $otp = $request->input('otp_code');
        $credentials = $request->only('email', 'password');

        // lấy dữ liệu unicode tuwf contains
        $value = UniCode::encode;
        // cắt chuỗi
        $email_arr = str_split($credentials['email']);
        $arr_encode = [];
        foreach ($email_arr as $item) {
            $arr_encode[] = $value[$item];
        }
        $email_encode = implode("", $arr_encode);
        // Tìm user theo email
        $user = User::where('email', $email_encode)->first();

        // Check sự tồn tại nếu khôg tìm thấy user hoặc sai mật khẩu thì báo lỗi lên client
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            abort(400, 'Tên đăng nhập hoặc mật khẩu không đúng');
        }

        // Kiểm tra xem OTP có tồn tại và chưa hết hạn không
        // nếu user có tồn tại thì kiểm tra xem tài khoản đấy còn hoạt động không
        if(!$user->active){
            abort(400, 'Tài khoản của bạn đã bị khoá vì nhập sai mã OTP quá 3 lần');
        }
        // Tìm mã otp đã được lưu trong db xem có tồn tại và còn hạn không
        $otpRecord = EmailOtp::where('email', $credentials['email'])
            ->where('otp_code', $otp)
            ->where('expired_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        // Nếu mã otp không tồn tại thì do người dùng nhập sai hoặc chậm
        if (!$otpRecord) {
            // Cộng dồn số lần nhập sai mã của người dùng
            $user->count_false += 1;
            $user->save();
            abort(400,'Mã OTP không đúng hoặc đã hết hạn');
        }

        // nếu nhập đúng sẽ tạo token và cho người dùng đăng nhập
        $token = $user->createToken('login_token');
        $user->count_false = 0;
        $user->save();

        return $this->responseSuccess($token->plainTextToken, ['user_id' => $user->id]);
    }
    public function sendOtp(Request $request)
    {
        // Lấy dữ liệu request
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
        $expiredAt = now()->addSecond(60); // Thời gian hết hạn của OTP
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
    public function getInfo($id)
    {
        $result = User::with(['bankCard','partner'])->find($id);
        return $this->responseSuccess($result);
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
