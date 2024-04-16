<?php

return
    [
        "field" => [
            "id" => "mã hệ thống",
            "delivery_note_id" => "phiếu lên hàng hoặc xuống hàng",
            "product_id" => "hàng hóa",
            "product_qty" => "số lượng hàng hóa",
            "product_uom" => "đơn vị hàng hóa",
            "unit_price" => "đơn giá cho mỗi đơn vị hàng hóa",
            "cost" => "tổng chi phí",
            "reference_id" => "phiếu tham chiếu, thường là giao dịch kho",
            "notes" => "ghi chú",
        ],
        "required" => [
            "delivery_note_id" => "Vui lòng nhập phiếu lên hàng hoặc xuống hàng",
            "product_id" => "Vui lòng nhập hàng hóa",
            "product_uom" => "Vui lòng nhập đơn vị hàng hóa",
            "unit_price" => "Vui lòng nhập đơn giá cho mỗi đơn vị hàng hóa",
        ],
    ];
