<?php

return
    [
        "field" => [
            "id" => "mã hệ thống",
            "code" => "số phiếu",
            "origin_id" => "phiếu nhập kho",
            "reference_id" => "lệnh điều xe",
            "type" => "loại",
            "delivery_date" => "ngày thực hiện",
            "approved_date" => "ngày phê duyệt",
            "approved_auto" => "thông tin lương tự động tạo và phê duyệt",
            "total_cost" => "tổng chi phí",
            "number_of_worker" => "số lượng công nhân",
            "created_at" => "ngày lập",
            "update_at" => "ngày cập nhật",
            "created_by_id" => "người tạo",
            "updated_by_id" => "người cập nhật",
            "state" => "trạng thái",
            "note" => "ghi chú",
        ],
        "required" => [
            "code" => "Vui lòng nhập số phiếu",
            "type" => "Vui lòng loại phiếu",
            "delivery_date" => "Vui lòng nhập ngày thực hiện",
            "number_of_worker" => "Vui lòng nhập số lượng công nhân",
            "state" => "Vui lòng nhập trạng thái",
        ],
    ];
