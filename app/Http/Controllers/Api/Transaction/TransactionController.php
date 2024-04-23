<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Constants\UniCode;
use App\Http\Controllers\Controller;
use App\Models\Auth\TransactionData;
use App\Traits\ResponseType;
use Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $value = UniCode::encode;
        $array_data = str_split($datas);
        foreach ($array_data as $data) {
            $arr_encode[] = $value[$data];
        }
        $data_encode = Crypt::encrypt(implode("", $arr_encode));
        return $data_encode;
    }
    private function decode($datas)
    {
        $value = UniCode::decode;
        $encode = Crypt::decrypt($datas);
        $array_data = str_split($encode, 8);
        foreach ($array_data as $data) {
            $arr_decode[] = $value[$data];
        }
        $data_decode = implode("", $arr_decode);

        return $data_decode;
    }
}
