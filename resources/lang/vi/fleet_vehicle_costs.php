<?php

return
    [
        "field" => [
            "id" => "mã hệ thống",
            "name" => "tên khoản chi phí",
            "vehicle_id" => "xe vận tải",
            "cost_subtype_id" => "dịch vụ",
            "amount" => "tổng tiền",
            "cost_type" => "hạng mục chi phí",
            "parent_id" => "thuộc chi phí",
            "odometer" => "số Km ghi lại gần nhất",
            "date" => "ngày phát sinh chi phí",
            "contract_id" => "hợp đồng",
            "auto_generated" => "khoản chi phí tự động tạo ra",
            "state" => "trạng thái",
            "created_at" => "thời gian tạo",
            "created_by_id" => "người tạo",
            "updated_at" => "thời gian cập nhật",
            "updated_by_id" => "người cập nhật",
            "approved_date" => "ngày xác nhận",
            "approved_by" => "người xác nhận",
            "month" => "tháng",
            "year" => "năm",
        ],
        "required" => [
            "vehicle_id" => "Vui lòng nhập xe vận tải",
            "cost_type" => "Vui lòng nhập hạng mục chi phí",
            "date" => "Vui lòng nhập ngày phát sinh chi phí",
        ],
    ];
