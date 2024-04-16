<?php

namespace App\Traits\Model;

/**
 * Trait ResponseType.
 */
trait NumberToWords
{
    public $digits = [
        0 => 'Không',
        1 => 'Một',
        2 => 'Hai',
        3 => 'Ba',
        4 => 'Bốn',
        5 => 'Năm',
        6 => 'Sáu',
        7 => 'Bảy',
        8 => 'Tám',
        9 => 'Chín',
    ];

    public $powersOfTen = [
        3 => 'Nghìn',
        6 => 'Triệu',
        9 => 'Tỷ',
        // Thêm các đơn vị khác nếu cần thiết
    ];

    public function convertToWords($number)
    {
        if (!is_numeric($number)) {
            return 'Không phải là một số';
        }

        $number = (int) $number;
        $words = [];

        $chunks = str_split(strrev($number), 3);

        foreach ($chunks as $index => $chunk) {
            $chunk = (int) strrev($chunk);

            if ($chunk > 0) {
                $chunkWords = [];

                if ($chunk >= 100) {
                    $chunkWords[] = $this->digits[$chunk / 100] . ' Trăm';
                    $chunk %= 100;
                }

                if ($chunk >= 10 && $chunk <= 99) {
                    $chunkWords[] = $this->digits[$chunk / 10] . ' Mươi';
                    $chunk %= 10;
                }

                if ($chunk > 0) {
                    $chunkWords[] = $this->digits[$chunk];
                }

                if ($index > 0 && isset($this->powersOfTen[$index * 3])) {
                    $chunkWords[] = $this->powersOfTen[$index * 3];
                }

                $words = array_merge($chunkWords, $words);
            }
        }

        return implode(' ', $words) . ' Đồng';
    }
}
