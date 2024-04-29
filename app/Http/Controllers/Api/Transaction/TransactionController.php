<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Constants\TransactionCheck;
use App\Constants\UniCode;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Auth\BankCard;
use App\Models\Auth\Occupation;
use App\Models\Auth\PinCode;
use App\Models\Auth\TransactionData;
use App\Models\Auth\User;
use App\Models\EmailOtp;
use App\Traits\ResponseType;
use Crypt;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use Number;

class TransactionController extends Controller
{
    /**
     * Get the guard to be used during authentication.
     *
     */
    use ResponseType;

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankTransactions(Request $request)
    {
        $user = $request->user();
        $bank_card = BankCard::where('user_id', $user->id)->first();
        $pin = PinCode::where('user_id', $user->id)->first();
        if (!$bank_card->active && $bank_card->count_false_pin >= 3) {
            abort(400, 'Thẻ của bạn đã bị khoá giao dịch do nhập sai mã pin giao dịch quá 3 lần');
        }
        if (!Hash::check($request->get('pin_code'), $pin->code)) {
            $bank_card->count_false_pin += 1;
            $bank_card->save();
            abort(400, 'Mã pin giao dịch không chính xác');
        }

        DB::beginTransaction();
        try {
            // mã hoá dữ liệu sang aes
            $account_number = self::encode($request->get('account_number'));
            $bank_name = self::encode($request->get('bank_name'));
            $note = self::encode($request->get('note'));
            $postage = self::encode($request->get('postage'));
            $transaction_type = self::encode($request->get('transaction_type'));
            $value = self::encode($request->get('value'));
            //  kiểm tra dữ liệu
            if (intval($request->get('value')) > intval($bank_card->limit)) {
                abort(100, 'Bạn chỉ được giao dịch tối đa 50.000.000 cho 1 lần giao dịch');
            }
            $mount = intval($bank_card->mount) - intval($request->get('value'));
            if ($mount < 0) {
                abort(100, 'Tài khoản của bạn không đủ để thực hiện giao dịch');
            }
            $check = self::checkTransaction($value, $transaction_type, $user->id);
            if ($check) {
                TransactionData::create([
                    "account_number" => $account_number,
                    "bank_name" => $bank_name,
                    "note" => $note,
                    "postage" => $postage,
                    "transaction_type" => $transaction_type,
                    "value" => $value
                ]);
                $bank_card->mount = $mount;
                $bank_card->count_false_pin = 0;
                $bank_card->save();
                DB::commit();
                return $this->responseSuccess(['has_otp' => false]);
            } else {
                $otp = mt_rand(100000, 999999);
                $expiredAt = now()->addSecond(30);
                $email = self::decodeUni($user->email);
                // Lưu OTP vào database
                EmailOtp::create([
                    'email' => $email,
                    'otp_code' => $otp,
                    'expired_at' => $expiredAt,
                ]);
                // Gửi OTP qua email
                Mail::to($email)->send(new OtpMail($otp));
                $bank_card->count_false_pin = 0;
                $bank_card->save();
                DB::commit();
                return $this->responseSuccess(['has_otp' => true]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function acceptOtpBankTransaction(Request $request)
    {
        $user = $request->user();
        $bank_card = BankCard::where('user_id', $user->id)->first();
        if (!$bank_card->active && $bank_card->count_false_otp >= 3) {
            abort(400, 'Thẻ của bạn đã bị khoá giao dịch do nhập sai mã otp quá 3 lần');
        }
        $otp = $request->input('otp_code');
        $email = self::decodeUni($user->email);
        $otpRecord = EmailOtp::where('email', $email)
            ->where('otp_code', $otp)
            ->where('expired_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$otpRecord) {
            $bank_card->count_false_otp += 1;
            $bank_card->save();
            abort(400, 'Mã OTP không đúng hoặc đã hết hạn');
        }
        DB::beginTransaction();
        try {
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
            if (intval($request->get('value')) > intval($bank_card->limit)) {
                abort(100, 'Bạn chỉ được giao dịch tối đa 50.000.000 cho 1 lần giao dịch');
            }
            $mount = intval($bank_card->mount) - intval($request->get('value'));
            if ($mount < 0) {
                abort(100, 'Tài khoản của bạn không đủ để thực hiện giao dịch');
            }
            $bank_card->mount = $mount;
            $bank_card->count_false_otp = 0;
            $bank_card->save();
            DB::commit();
            return $this->responseSuccess();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function sentOptTran(Request $request){

        $otp = mt_rand(100000, 999999); // Sinh mã OTP ngẫu nhiên
        $expiredAt = now()->addSecond(30); // Thời gian hết hạn của OTP
        $user = $request->user();
        $email = self::decodeUni($user->email);
        // Lưu OTP vào database
        EmailOtp::create([
            'email' => $email,
            'otp_code' => $otp,
            'expired_at' => $expiredAt,
        ]);

        // Gửi OTP qua email
        Mail::to($email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP has been sent']);
    }
    public function transactionData(Request $request)
    {
        $transactions = TransactionData::query()->get();
        $data = [];
        if ($transactions) {
            foreach ($transactions as $transaction) {
                $data[] = [
                    "account_number" => self::decode($transaction->account_number ?? null),
                    "bank_name" => self::decode($transaction->bank_name ?? null),
                    "note" => self::decode($transaction->note ?? null),
                    "postage" => self::decode($transaction->postage ?? null),
                    "transaction_type" => self::decode($transaction->transaction_type ?? null),
                    "value" => self::decode($transaction->value ?? null),
                    "created_at" => $transaction->created_at,
                ];
            }
        }
        return $this->responseSuccess($data);
    }
    private function encode($datas)
    {
        if (!$datas) {
            return;
        }
        //  mã hoá sang bit
        $value = UniCode::encode;
        $array_data = str_split($datas);
        foreach ($array_data as $data) {
            $arr_encode[] = $value[$data];
        }
        // mã hoá aes
        $data_encode = Crypt::encrypt(implode("", $arr_encode));
        return $data_encode;
    }
    private function decode($datas)
    {
        $value = UniCode::decode;
        // chuyển aes sang bit
        if (!$datas) {
            return;
        }
        $encode = Crypt::decrypt($datas);
        // chuyển bit sang unicode
        $array_data = str_split($encode, 8);
        foreach ($array_data as $data) {
            $arr_decode[] = $value[$data];
        }
        $data_decode = implode("", $arr_decode);

        return $data_decode;
    }
    private function decodeUni($data)
    {
        $value = UniCode::decode;
        $array_data = str_split($data, 8);
        foreach ($array_data as $data) {
            $arr_decode[] = $value[$data];
        }
        $data_decode = implode("", $arr_decode);
        return $data_decode;
    }
    private function checkTransaction($value, $type, $user_id)
    {

        $type_decode = self::decode($type);
        $value_decode = self::decode($value);
        $data_check = TransactionCheck::data;
        $age_group = TransactionCheck::age_group;
        $user_info = User::with(['bankCard', 'partner'])->find($user_id);
        $occupation = Occupation::find($user_info->partner->occupation_id);
        $age = \Carbon\Carbon::parse($user_info->partner->birth_date)->age;
        $age_check = null;
        if (intval($value_decode) >= 10000000) {
            return false;
        }
        foreach ($age_group as $range) {
            $start = $range[0];
            $end = $range[1];
            if ($age >= $start && $age <= $end) {
                $age_check = $start;
                break;
            }
        }
        $check_gender = $data_check[$user_info->partner->gender];
        $check_age = $check_gender[$age_check] ?? null;
        if ($check_age) {
            $check_married = $check_age[$user_info->partner->married] ?? null;
        } else {
            return true;
        }

        if ($check_married) {
            $check_job = $check_married[$occupation->code] ?? null;
        } else {
            return true;
        }

        if ($check_job) {
            $check_type = $check_job[$type_decode] ?? null;
        } else {
            return true;
        }
        if ($check_type) {
            $check_value = intval($value_decode) < intval($check_type);
        } else {
            return true;
        }
        return $check_value;
    }
}
