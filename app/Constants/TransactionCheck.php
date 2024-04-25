<?php

namespace App\Constants;

final class TransactionCheck
{
    public const age_group = [
        [18, 25], // Khoảng tuổi 18-25
        [26, 36], // Khoảng tuổi 26-36
    ];
    public const data = [
        "0" => [
            '18' => [
                '0' => [
                    'cntt' => [
                        'learn' => '5000000',
                        'utilities' => '1000000',
                        'shopping' => '500000',
                        'tranfer' => '500000',
                        'phone_recharge' => '100000'
                    ]
                ],
                '1' => [
                    'health_care' => [
                        'utilities' => '15000000',
                        'shopping' => '1000000',
                        'tranfer' => '5000000',
                        'insurance' => '500000',
                        'learn' => '8000000',
                        'phone_recharge' => '500000',
                        'savings' => '1000000'
                    ]
                ]
            ],
            '26' => [
                '0' => [
                    'doctor' => [
                        'learn' => '5000000',
                        'utilities' => '2000000',
                        'shopping' => '2000000',
                        'tranfer' => '5000000',
                        'phone_recharge' => '500000',
                        'insurance' => '500000',
                        'savings' => '1000000'
                    ]
                ],
                '1' => [
                    'teacher' => [
                        'learn' => '8000000',
                        'utilities' => '2000000',
                        'shopping' => '2000000',
                        'tranfer' => '2000000',
                        'phone_recharge' => '500000',
                        'insurance' => '500000',
                        'savings' => '1000000'
                    ]
                ],
            ]
        ],
        '1' => [
            '26' => [
                '0' => [
                    'marketing' => [
                        'utilities' => '1000000',
                        'shopping' => '2000000',
                        'tranfer' => '2000000',
                        'insurance' => '500000',
                        'phone_recharge' => '500000',
                        'savings' => '1000000'
                    ]
                ],
                '1' => [
                    'engineer' => [
                        'utilities' => '5000000',
                        'shopping' => '2000000',
                        'tranfer' => '5000000',
                        'insurance' => '500000',
                        'phone_recharge' => '500000',
                        'savings' => '1000000'
                    ]
                ]
            ]
        ]
    ];
}
