<?php

return
    [
        "title" => [
            "create_new" => "Thêm mới kho",
            "edit" => "Sửa kho",
            "delete" => "Xóa kho "
        ],
        "sub_title" => [
            "create_new" => "Thêm mới thông tin kho",
            "edit" => "Chỉnh sửa thông tin kho",
            "delete" => "Bạn muốn xoá kho này khỏi hệ thống"
        ],
        "field" => [
            "id" => "mã hệ thống",
            "name" => "tên",
            "code" => "mã",
            "description" => "mô tả",
            "street" => "đường",
            "district_name" => "quận/huyện",
            "province_name" => "tỉnh/thành phố",
            "accounting_warehouse_code" => "mã kho kế toán",
            "contract_address" => "địa chỉ",
            "active" => "trạng thái",
            "employee_id" => "người quản lý kho",
            "address" => "địa chỉ",
            "employee_name" => "người quản lý",
            "employee_code" => "mã người quản lý",
            "action" => "hành động",
            "street" => "đường/phố",
        ],
        "required" => [
            "name" => "Vui lòng nhập tên kho",
            "code" => "Vui lòng nhập mã kho",
            "active" => "Vui lòng nhập trạng thái kho",
        ],
        "unique" => [
            "code" => "Mã kho đã tồn tại trong hệ thống",
        ],
        "message" => [
            "success_add" => "Thành công",
            "success_desc_add" => "Thêm mới kho thành công",
            "error_add" => "Thất bại",
            "error_desc_add" => "Thêm mới kho thất bại",
            "success_delete" => "Thành công",
            "success_desc_delete" => "Xoá kho thành công",
            "error_delete" => "Thất bại",
            "error_desc_delete" => "Xoá kho thất bại",
            "success_edit" => "Thành công",
            "success_desc_edit" => "Sửa kho thành công",
            "error_edit" => "Thất bại",
            "error_desc_edit" => "Sửa kho thất bại"
        ],
        "hint" => [
            "name" => "Tên kho",
            "code" => "Mã kho",
            "description" => "Mô tả",
        ],
        "action" => [
            "create_new" => "Thêm mới",
            "accept" => "Xác nhận",
            "delete" => "Xóa",
            "cancel" => "Hủy"
        ]
    ];
