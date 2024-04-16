<?php

return
    [
        "field" => [
            "id" => "Mã hệ thống",
            "name" => "Tên",
            "created_at" => "Ngày bắt đầu",
            "date_done" => "Ngày hết kỳ",
            "state" => "Trạng thái",
            "warehouse_id" => "Kho",
        ],
        "required" => [
            "name" => "Vui lòng nhập tên",
            "created_at" => "Vui lòng nhập ngày bắt đầu",
            "state" => "Vui lòng chọn trạng thái",
            "warehouse_id" => "Vui lòng chọn Kho",
        ],
        "date" => [
            "created_at" => "Ngày bắt đầu phải có giá trị thuộc kiểu ngày",
            "date_done" => "Ngày hết kỳ phải có giá trị thuộc kiểu ngày",
        ],
        "before_or_equal" => [
            "created_at" => "ngày bắt đầu phải có giá trị bé hơn hoặc bằng ngày hết kỳ",
        ],
        "after_or_equal" => [
            "date_done" => "Ngày hết kỳ phải có giá trị lớn hơn hoặc bằng ngày bắt đầu",
        ],
    ];
