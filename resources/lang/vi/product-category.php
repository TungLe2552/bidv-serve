<?php

return
    [
        "title" => [
            "create_new" => "Thêm mới nhóm hàng hóa",
            "edit" => "Sửa nhóm hàng hóa",
            "delete" => "Xóa nhóm hàng hóa "
        ],
        "sub_title" => [
            "create_new" => "Thêm mới thông tin nhóm hàng hóa",
            "edit" => "Chỉnh sửa thông tin nhóm hàng hóa",
            "delete" => "Bạn muốn xoá nhóm hàng hóa này khỏi hệ thống"
        ],
        "field" => [
            "id" => "mã hệ thống",
            "name" => "tên",
            "code" => "mã",
            "short_name" => "tên viết tắt",
            "description" => "mô tả",
            "active" => "trạng thái",
            "type" => "loại",
            "priority" => "ưu tiên",
            "parent_id" => "thuộc",
            "parent_name" => "thuộc nhóm hàng hóa",
            "is_selected_in_contract" => "chọn trên hợp đồng",
        ],
        "required" => [
            "name" => "Vui lòng nhập tên nhóm hàng hóa",
            "code" => "Vui lòng nhập mã nhóm hàng hóa",
            "is_selected_in_contract" => "Vui lòng nhập chọn trên hợp đồng",
            "active" => "Vui lòng nhập trạng thái nhóm hàng hóa",
        ],
        "unique" => [
            "code" => "Mã kho đã tồn tại trong hệ thống",
            "short_name" => "Tên viết tắt kho đã tồn tại trong hệ thống",
        ],
        "message" => [
            "success_add" => "Thành công",
            "success_desc_add" => "Thêm mới nhóm hàng hóa thành công",
            "error_add" => "Thất bại",
            "error_desc_add" => "Thêm mới nhóm hàng hóa thất bại",
            "success_delete" => "Thành công",
            "success_desc_delete" => "Xoá nhóm hàng hóa thành công",
            "error_delete" => "Thất bại",
            "error_desc_delete" => "Xoá nhóm hàng hóa thất bại",
            "success_edit" => "Thành công",
            "success_desc_edit" => "Sửa nhóm hàng hóa thành công",
            "error_edit" => "Thất bại",
            "error_desc_edit" => "Sửa nhóm hàng hóa thất bại"
        ],
        "hint" => [
            "name" => "Tên nhóm hàng hóa",
            "code" => "Mã nhóm hàng hóa",
            "short_name" => "Tên viết tắt",
            "description" => "Mô tả",
            "active" => "Trạng thái",
            "type" => "Kiểu",
            "priority" => "Ưu tiên",
            "parent_id" => "Thuộc nhóm hàng hóa",
            "is_selected_in_contract" => "Chọn trên hợp đồng",
            "action" => "Hành động",
        ],
        "action" => [
            "create_new" => "Thêm mới",
            "accept" => "Xác nhận",
            "delete" => "Xóa",
            "cancel" => "Hủy"
        ]
    ];
