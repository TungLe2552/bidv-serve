<?php

return
    [
        "title" => [
            "create_new" => "Thêm mới hàng hóa",
            "edit" => "Sửa hàng hóa",
            "delete" => "Xóa hàng hóa"
        ],
        "sub_title" => [
            "create_new" => "Thêm mới thông tin hàng hóa",
            "edit" => "Chỉnh sửa thông tin hàng hóa",
            "delete" => "Bạn muốn xoá hàng hóa này khỏi hệ thống"
        ],
        "field" => [
            "id" => "mã hệ thống",
            "code" => "mã hàng hóa",
            "name" => "tên hàng hóa",
            "product_category_name" => "tên nhóm",
            "product_category_code" => "mã nhóm",
            "hs_code" => "mã HS",
            "accountant_product_code" => "mã hàng hóa kế toán",
            "qlct_code" => "mã QLCT",
            "unit_name" => "đơn vị tính",
            "conversion_factor" => "quy đổi sang kg",
            "is_consumable" => "tiêu hao",
            "active" => "trạng thái",
            "action" => "hành động",
        ],
        "required" => [
            "name" => "Vui lòng nhập tên hàng hóa",
            "code" => "Vui lòng nhập mã hàng hóa",
            "product_category_id" => "Vui lòng nhập mã nhóm hàng hóa",
            "unit_id" => "Vui lòng nhập đơn vị tính",
            "active" => "Vui lòng nhập trạng thái hàng hóa",
        ],
        "unique" => [
            "code" => "Mã hàng hóa đã tồn tại trong hệ thống",
        ],
        "message" => [
            "success_add" => "Thành công",
            "success_desc_add" => "Thêm mới hàng hóa thành công",
            "error_add" => "Thất bại",
            "error_desc_add" => "Thêm mới hàng hóa thất bại",
            "success_delete" => "Thành công",
            "success_desc_delete" => "Xoá hàng hóa thành công",
            "error_delete" => "Thất bại",
            "error_desc_delete" => "Xoá hàng hóa thất bại",
            "success_edit" => "Thành công",
            "success_desc_edit" => "Sửa hàng hóa thành công",
            "error_edit" => "Thất bại",
            "error_desc_edit" => "Sửa hàng hóa thất bại"
        ],
        "hint" => [
            "code" => "Mã hàng hóa",
            "name" => "Tên hàng hóa",
            "product_category_id" => "Mã nhóm hàng hóa",
            "hs_code" => "Mã HS",
            "accountant_product_code" => "Mã hàng hóa kế toán",
            "qlct_code" => "Mã QLCT",
            "unit_id" => "Đơn vị tính",
            "conversion_factor" => "Quy đổi sang kg",
            "is_consumable" => "Tiêu hao",
            "active" => "Trạng thái",
            "action" => "Hành động",
        ],
        "action" => [
            "create_new" => "Thêm mới",
            "accept" => "Xác nhận",
            "delete" => "Xóa",
            "cancel" => "Hủy"
        ]
    ];
