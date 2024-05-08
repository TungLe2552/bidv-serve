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
use App\Models\Auth\UserMaxTransaction;
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
        // lấy thông tin request
        $user = $request->user();

        // Tìm thông tin tài khoản ngân hàng theo user
        $bank_card = BankCard::where('user_id', $user->id)->first();

        // Tìm thông mã pin giao dịch theo user
        $pin = PinCode::where('user_id', $user->id)->first();

        // Báo lỗi nếu khách hàng cố tình ko nhập mã pin
        if(empty($request->get('pin_code'))){
            abort(400, 'Bạn cần nhập mã pin giao dịch');
        }

        // Báo lỗi nếu thẻ bị khoá
        if (!$bank_card->active && $bank_card->count_false_pin >= 3) {
            abort(400, 'Thẻ của bạn đã bị khoá giao dịch do nhập sai mã pin giao dịch quá 3 lần');
        }

        // nếu nhập sai mã pin thì tự cộng dồn số lần nhập sai, nếu quá 3 lần sẽ khoá thẻ
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

            // dd([
            //     "account_number"=>self::decode($account_number),
            //     "bank_name"=>self::decode($bank_name),
            //     "transaction_type"=>self::decode($transaction_type),
            //     "postage"=>self::decode($postage),
            //     "value"=>self::decode($value),
            //     "note"=>self::decode($note),
            // ]);
            //  kiểm tra dữ liệu

            if (intval($request->get('value')) > intval($bank_card->limit)) {
                abort(400, 'Bạn chỉ được giao dịch tối đa 50.000.000 cho 1 lần giao dịch');
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
                // lưu lại giá trị tiền sau giao dịch
                $bank_card->mount = $mount;
                $bank_card->count_false_pin = 0;
                $bank_card->save();
                DB::commit();
                return $this->responseSuccess(['has_otp' => false]);
            } else {
                $otp = mt_rand(100000, 999999);
                $expiredAt = now()->addSecond(60);
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
        $max_value_data = UserMaxTransaction::where('user_id',$user->id)->where('transaction_type',$request->get('transaction_type'))->first();
        $bank_card = BankCard::where('user_id', $user->id)->first();
        if (!$bank_card->active && $bank_card->count_false_otp >= 3) {
            abort(400, 'Thẻ của bạn đã bị khoá giao dịch do nhập sai mã otp quá 3 lần');
        }
        $otp = $request->input('otp_code');
        $email = self::decodeUni($user->email);
        $pin = PinCode::where('user_id', $user->id)->first();
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
        if (!Hash::check($request->get('pin_code'), $pin->code)) {
            $bank_card->count_false_pin += 1;
            $bank_card->save();
            abort(400, 'Mã pin giao dịch không chính xác');
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
            UserMaxTransaction::updateOrCreate([
                'user_id' => $user ->id,
                'transaction_type'=>$request->get('transaction_type')
            ],[
                'max_value'=>$max_value_data && (intval($max_value_data->max_value) > intval($request->get('value'))) ? $max_value_data->max_value: $request->get('value')
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
            $bank_card->count_false_pin = 0;
            $bank_card->save();
            DB::commit();
            return $this->responseSuccess();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function sentOptTran(Request $request){

        $otp = mt_rand(100000, 999999); // Sinh mã OTP ngẫu nhiên
        $expiredAt = now()->addSecond(60); // Thời gian hết hạn của OTP
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
    public function transactionData(Request $request,)
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
        // lấy mã unicode từ constants ra để giải mã
        $type_decode = self::decode($type);

        // lấy hết dữ liệu max giao dịch của loại hình theo user
        $type_check_data = UserMaxTransaction::where('user_id',$user_id)->get()->mapWithKeys(function ($item, $key) {
            return [$item->transaction_type => $item->max_value];
        });

        // giải mã dữ liệu
        $value_decode = self::decode($value);

        // lấy ra cụm dũ liệu từ constants
        $data_check = TransactionCheck::data;
        $age_group = TransactionCheck::age_group;

        //
        $user_info = User::with(['bankCard', 'partner'])->find($user_id);
        $occupation = Occupation::find($user_info->partner->occupation_id);
        // chuyển đổi ngày tháng năm sinh ra tuổi
        $age = \Carbon\Carbon::parse($user_info->partner->birth_date)->age;
        $age_check = null;

        // đặt trước biến check giá trị bằng true
        $check_value = true;

        // nếu số tiền gd lớn hơn hoặc bằng 10tr thì return false, bắt gửi otp
        if (intval($value_decode) >= 10000000) {
            return false;
        }

        // lấy ra tuổi của user thuộc cụm nào
        foreach ($age_group as $range) {
            $start = $range[0];
            $end = $range[1];
            if ($age >= $start && $age <= $end) {
                $age_check = $start;
                break;
            }
        }

        // chèck theo cụm dữ liệu
        // check giới tính
        $check_gender = $data_check[$user_info->partner->gender];

        // check tuổi
        $check_age = $check_gender[$age_check] ?? null;

        if ($check_age) {
            // nếu tuổi có tồn tại thì so sánh tuổi để biết tình trạng hôn nhân
            $check_married = $check_age[$user_info->partner->married] ?? null;
        } else {
            // nếu tuổi không tồn tại thì trả về true bắt nhập mã otp
            return false;
        }

        if ($check_married) {
            // nếu tình trạng hôn nhân có tồn tại thì so sánh tình trạng hôn nhân để lấy dữ liệu nghề nghiệp

            $check_job = $check_married[$occupation->code] ?? null;
        } else {
            // nếu tình trạng hôn nhân không tồn tại thì trả về false bắt nhập mã otp

            return false;
        }

        if ($check_job) {
            // nếu dữ liệu nghề nghiệp có tồn tại thì so sánh dữ liệu nghề nghiệp để lấy dữ liệu loại giao dịch

            $check_type = $check_job[$type_decode] ?? null;
        } else {
            // nếu dữ liệu nghề nghiệp không tồn tại thì trả về false bắt nhập mã otp

            return false;
        }
        if ($check_type) {
            // nếu dữ liệu loại giao dịch có tồn tại thì so sánh số  tiền max mỗi giao dịch

            // tạo trước biến data_value
            $data_value = null;
            if(count($type_check_data)>0){

                // nếu dữ liệu max giao dịch của loại hình theo user có tồn tại thì tìm kiếm dữ liệu max của loại giao dịch do client gửi xuống
                $data_value = $type_check_data[$type_decode]??null;
            }

            if($data_value){
                // nếu tìm được max giao dịch của loại hình theo user thì so sánh giá trị đấy với dữ liệu số tiền gửi từ client
                $check_value = intval($value_decode) <= intval($data_value);
            }else{
                // nếu không tìm được max giao dịch của loại hình theo user thì so sánh giá trị số tiền gửi từ client với cụm dữ liệu sẵn có
                $check_value = intval($value_decode) <= intval($check_type);
            }
        } else {
            return false;
            // nếu dữ liệu loại giao dịch không tồn tại thì trả về false bắt nhập mã otp

        }
        return $check_value;
    }
}
