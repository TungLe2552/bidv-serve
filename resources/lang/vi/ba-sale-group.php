<?php

return
    [
        "field" => [
            "id" => "mã hệ thống",
            "name" => "tên",
            "code" => "khách hàng",
            "employees" => "nhân viên",
            "description" => "Mô tả",
            "active" => "Trạng thái",
            "manager_id" => "Nhóm kinh doanh",
        ],
        "required" => [
            "name" => "Vui lòng nhập tên",
            "code" => "Vui lòng nhập mã",

            "max_name" => "Tên tối đa 100 kí tự",
            "max_code" => "Mã tối đa 10 kí tự",

            "unique_code" => "Mã này đã tồn tại trong hệ thống",
        ],
        "message" => [
            "success_add" => "Thành công",
            "success_desc_add" => "Thêm mới nhóm kinh doanh thành công",
            "error_add" => "Thất bại",
            "error_desc_add" => "Thêm mới nhóm kinh doanh thất bại",
            "success_delete" => "Thành công",
            "success_desc_delete" => "Xoá nhóm kinh doanh thành công",
            "error_delete" => "Thất bại",
            "error_desc_delete" => "Xoá nhóm kinh doanh thất bại",
            "success_edit" => "Thành công",
            "success_desc_edit" => "Sửa nhóm kinh doanh thành công",
            "error_edit" => "Thất bại",
            "error_desc_edit" => "Sửa nhóm kinh doanh thất bại"
        ],
    ];
