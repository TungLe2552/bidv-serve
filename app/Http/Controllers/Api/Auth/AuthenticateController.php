<?php

namespace App\Http\Controllers\Api\Auth;

use App\Constants\UniCode;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Auth\CashFlow;
use App\Models\EmailOtp;
use App\Models\Auth\PinCode;
use App\Models\Auth\TransactionData;
use App\Models\Auth\User;
use App\Traits\ResponseType;
use Crypt;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mail;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

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
        try {
            $user = User::create([
                'email' => $request->get('email'),
                'password' => $request->get('password'),
                'user_name' => $request->get('user_name')

            ]);
            if ($request->has('pin')) {
                PinCode::create(['code' => $request->get('pin'), 'user_id' => $user->id]);
            }
            CashFlow::create(['value' => '100000000', 'limit' => '50000000', 'user_id' => $user->id]);
            DB::commit();
            return $this->responseSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getInfo(Request $request)
    {
        $result = User::with(['cash'])->where('email', $request->get('email'))->first();
        return $this->responseSuccess($result);
    }
    public function sendOtp(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
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
        $user = User::where('email', $credentials['email'])->first();
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
        return $this->responseSuccess($user);
    }
    public function bankTransactions(Request $request)
    {
        $account_number = self::encode($request->get('account_number'));
        $bank_name = self::encode($request->get('bank_name'));
        $note = self::encode($request->get('note'));
        $postage = self::encode($request->get('postage'));
        $transaction_type = self::encode($request->get('transaction_type'));
        $value = self::encode($request->get('value'));
        TransactionData::create([
            "account_number" => $account_number,
            "bank_name" => $bank_name,
            "note" => $note,
            "postage" => $postage,
            "transaction_type" => $transaction_type,
            "value" => $value
        ]);
        return $this->responseSuccess();
    }
    public function transactionData(Request $request)
    {
        $transactions = TransactionData::query()->get();
        $data = [];
        foreach($transactions as $transaction){
            $data[] = [
                "account_number" => self::decode($transaction->account_number),
                "bank_name" => self::decode($transaction->bank_name),
                "note" => self::decode($transaction->note),
                "postage" => self::decode($transaction->postage),
                "transaction_type" => self::decode($transaction->transaction_type),
                "value" => self::decode($transaction->value),
            ];
        }
        return $this->responseSuccess($data);
    }
    private function encode($datas)
    {
        $value = UniCode::decode;
        $array_data = str_split($datas);
        foreach ($array_data as $data) {
            $arr_encode[] = $value[$data];
        }
        $data_encode = Crypt::encrypt(implode("", $arr_encode));
        return $data_encode;
    }
    private function decode($datas)
    {
        $encode = Crypt::decrypt($datas);
        $value = UniCode::encode;
        $array_data = str_split($encode, 8);
        foreach ($array_data as $data) {
            $arr_decode[] = $value[$data];
        }
        $data_decode = implode("", $arr_decode);

        return $data_decode;
    }
}
