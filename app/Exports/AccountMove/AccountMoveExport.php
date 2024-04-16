<?php

namespace App\Exports\AccountMove;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AccountMoveExport implements WithEvents, WithStyles, WithColumnWidths, WithMapping, ShouldAutoSize
{
    protected $data;
    protected $sheet;
    protected $cb_mapping_data;
    protected $length;
    use Exportable;

    public function __construct($data)
    {
        $this->data = $data;
    }
    // public function collection()
    // {
    //     $this->length = count($this->data);
    //     ++$this->length;
    //     return collect($this->data);
    // }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G9999')->getFont()->setName('Times New Roman');
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            2 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            4 => [
                'font' => ['bold' => true, 'size' => 15],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            8 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            9 => ['font' => ['bold' => true, 'underline' => true, 'italic' => true]],
            20 => ['font' => ['bold' => true, 'italic' => true, 'underline' => true,]],
            21 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 17,
            'C' => 25,
            'D' => 15,
            'E' => 19,
            'F' => 18,
            'G' => 21,
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class   => function (AfterSheet $event) {
                $templatePath = \PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path(('\template\BBNT-template.xlsx')));
                $worksheet = $templatePath->getActiveSheet();
                $styleMerge = $worksheet->getMergeCells();
                $worksheet->getStyle('A1:G32');
                foreach ($styleMerge as $value) {
                    $event->sheet->mergeCells($value);
                }
                $sheet = $event->sheet->getDelegate();
                $day = "23";
                $month = "08";
                $year = "2023";
                $A8 = 'Hôm nay, ngày ' . $day . ' tháng ' . $month . ' năm ' . $year . '. Tại văn phòng Công ty Cổ phần Môi trường Thuận Thành';
                $customVatCustomer = 200154831321;
                $customVat = 200154831321;
                $A19 = 'Cùng thống nhất lập biên bản nghiệm thu khối lượng hàng hủy thực hiện trong tháng ' . $month . '/' . $year . ' theo hợp đồng số ? với khối lượng và kinh phí như sau:';
                $event->sheet->setCellValue('A5', 'HAHA');
                $event->sheet->setCellValue('A6', 'HEHE');
                $event->sheet->setCellValue('A8', $A8);
                $nameCustomer = "Văn Chiế";
                $event->sheet->setCellValue('C10', $nameCustomer);
                $chucVuCustomer = 'Giám đốc';
                $event->sheet->setCellValue('F10', $chucVuCustomer);
                $companyCustomer = "CÔNG TY TNHH NIHON PLAST VIỆT NAM";
                $event->sheet->setCellValue('C11', $companyCustomer);
                $addressCustomer = 'Lô số C4, 5, 6, Khu công nghiệp Thăng Long Vĩnh Phúc, Xã Thiện Kế, Huyện Bình Xuyên, Vĩnh Phúc';
                $event->sheet->setCellValue('C12', $addressCustomer);
                $event->sheet->setCellValue('C13', $customVatCustomer);
                $name = "Chiến văn";
                $event->sheet->setCellValue('C14', $name);
                $chucVu = 'Giám đốc';
                $event->sheet->setCellValue('F14', $chucVu);
                $company = "CÔNG TY TNHH Thuận Thành";
                $event->sheet->setCellValue('C15', $company);
                $address = 'Lô số C4, 5, 6, Khu công nghiệp Thăng Long Vĩnh Phúc, Xã Thiện Kế, Huyện Bình Xuyên, Vĩnh Phúc';
                $bank = "Khu phố Ngọc Khám, Phường Gia Đông, Thị xã Thuận Thành, Tỉnh Bắc Ninh, Việt Nam";
                $event->sheet->setCellValue('C16', $bank);
                $event->sheet->setCellValue('C17', $address);
                $event->sheet->setCellValue('C18', $customVat);
                $event->sheet->setCellValue('A19', $A19);
                $accountMoveLines = $this->data['accountMoveLines'];
                $startRow = 22;
                // $additionalRows = count($accountMoveLines) - 2;
                // foreach ($data as &$row) {
                //     array_unshift($row, ''); // Add an empty cell at the beginning of each row
                //     $row[0] = $startRow++; // Set the row number and increment for the next iteration
                // }

                foreach ($accountMoveLines as $index => $line) {
                    $row = $startRow + $index;
                    $event->sheet->mergeCells('B'. $row.':' .'C'. $row);
                    $event->sheet->setCellValue('A' . $row, $index + 1);
                    $event->sheet->setCellValue('B' . $row, $line['item_name']);
                    $event->sheet->setCellValue('D' . $row, $line['unit_name']);
                    $event->sheet->setCellValue('E' . $row, $line['quantity']);
                    $event->sheet->setCellValue('F' . $row, $line['production_unit_price']);
                    $event->sheet->setCellValue('G' . $row, $line['total']);
                }

                $sheet->fromArray($worksheet->toArray());
            }
        ];
    }
    public function map($data): array
    {
        $cb = $this->cb_mapping_data;
        $data['test'] = "HEHEHEHEHEHIHIHIHIEEE";
        return $cb($data);
    }
}
