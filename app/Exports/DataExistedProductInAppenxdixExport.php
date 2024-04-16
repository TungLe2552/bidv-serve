<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataExistedProductInAppenxdixExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    // protected $data;

    // public function __construct($data)
    // {
    //     $this->data = $data;
    // }
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }
    public function headings(): array
    {
        // Defining headings if necessary
        return [
            'Mã hàng hóa hoặc dịch vụ',
            'Hàng hóa hoặc dịch vụ',
            'Tên hàng hóa hoặc dịch vụ theo hợp đồng',
            'Đơn vị tính',
            'Đơn giá xử lý',
            'Đơn giá mua',
            'Phương pháp xử lý',
            'Hệ số',
            'Mô tả',
            'Trạng thái tồn tại',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G9999')->getFont()->setName('Times New Roman');

        $styles = [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'height' => 35,
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF97ED97'],
                ],
            ],
        ];

// Add border to cells with data
        $highestRow = count($this->data) + 1;
        $highestColumn = "J";
        $rangeWithData = 'A1:' . $highestColumn . $highestRow;

// Define style for borders
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

// Apply border style to cells with data
        $sheet->getStyle($rangeWithData)->applyFromArray($borderStyle);
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B:B')->getAlignment()->setWrapText(true);
        $sheet->getStyle('C:C')->getAlignment()->setWrapText(true);

        return $styles;

    }
    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 50,
            'C' => 50,
            'D' => 11,
            'E' => 11,
            'F' => 11,
            'G' => 26,
            'H' => 10,
            'I' => 30,
            'J' => 14,
        ];
    }
}
