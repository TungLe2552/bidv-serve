<?php

namespace App\Constants;

use App\Models\Auth\UserView;
use App\Models\HR\Department;
use App\Models\HR\DepartmentView;
use App\Models\IM\Inventory;
use App\Models\IM\View\YeuCauNhapKhoHangHoa;
use App\Models\IM\View\YeuCauXuatKhoTheoCcdc;
use App\Models\IM\View\YeuCauXuatKhoTheoHangHoa;
use App\Models\IM\Warehouse;
use App\Models\Logistic\NormSalaryWorkerTerritory;
use App\Models\Logistic\View\AccountInvoiceForBBGNView;
use App\Models\Logistic\View\DeliveryNote\DeliveryNoteView;
use App\Models\Logistic\View\DeliveryNote\LenHangView;
use App\Models\Logistic\View\DeliveryNote\XuongHangView;
use App\Models\Logistic\View\FleetDeliveryOrderForBBGNView;
use App\Models\Logistic\View\WorkerCostView;
use App\Models\System\AudittrailsLogView;

final class ModelForQuery
{
    const LIST = [
        'im_warehouses' => Warehouse::class,
        'im_inventories' => Inventory::class,
        'hr_departments' => Department::class,
        'materialized_departments_view' => DepartmentView::class,
        'materialized_log_view' => AudittrailsLogView::class,
        'view_fleet_delivery_order_for_bbnn' => FleetDeliveryOrderForBBGNView::class,
        'view_account_invoice_for_bbnn' => AccountInvoiceForBBGNView::class,
        'materialized_res_users_view' => UserView::class,
        'fleet_delivery_notes_view' => DeliveryNoteView::class,
        'phieu_len_hang_view' => LenHangView::class,
        'phieu_xuong_hang_view' => XuongHangView::class,
        'worker_cost_view' => WorkerCostView::class,
        'yeu_cau_xuat_kho_theo_hang_hoa' => YeuCauXuatKhoTheoHangHoa::class,
        'yeu_cau_nhap_kho_hang_hoa' => YeuCauNhapKhoHangHoa::class,
        'yeu_cau_xuat_kho_theo_ccdc' => YeuCauXuatKhoTheoCcdc::class,
    ];
    const FORM = [
        'hr_departments' => Department::class,
        'norms_salary_worker_territories' => NormSalaryWorkerTerritory::class,
    ];
}
