<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DynamicTableExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents, WithBackgroundColor
{
    protected $sheet;
    protected $title;
    protected $subtitle;
    protected $columnFormats;
    protected $data;
    protected $headings;
    protected $cb_mapping_data;
    protected $length;
    use Exportable;
    public function backgroundColor()
    {
        return 'ffffff';
    }
    public function title(): string
    {
        return $this->sheet;
    }
    public function __construct($sheet, $data, $headings, $cb_mapping_data, $title = '')
    {
        $this->sheet = $sheet;
        $this->title = mb_strtoupper($title);
        $this->subtitle = ['Phiên bản'];
        $this->data = $data;
        $this->headings = $headings;
        $this->cb_mapping_data = $cb_mapping_data;
    }
    public function collection()
    {
        $this->length = count($this->data);
        ++$this->length;
        return collect($this->data);
    }
    public function headings(): array
    {
        return array_map(function ($header) {
            return $header['text'] ?? $header;
        }, $this->headings);
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $last_column = Coordinate::stringFromColumnIndex(count($this->headings));
                $before_last_column = Coordinate::stringFromColumnIndex(count($this->headings) - 1);
                $event->sheet->insertNewRowBefore(1, 5);
                $last_row = $this->length + 5 + 1;
                $event->sheet->getDelegate()->getStyle("A1:Z{$last_row}")->getAlignment()->setWrapText(true);
                $cellRange = 'A6:' . getexcelcolumnname(count($this->headings) - 1) . '6'; // All headers
                $cellRest = 'A7:' . getexcelcolumnname(count($this->headings) - 1) . ($this->length + 5);

                $event->sheet->getRowDimension(5)->setRowHeight(25);
                $nameRangeA1 = 'A1:' . $last_column . '1';
                $nameRange = 'A2:' . $last_column . '2';
                // $event->sheet->mergeCells(sprintf('A1:%s1', $last_column));
                $event->sheet->setCellValue('B2', 'Công ty cổ phần Môi trường Thuận Thành');
                $event->sheet->setCellValue('B3', 'Ngọc Khám, Gia Đông, Thuận Thành');
                $event->sheet->mergeCells('B2:E2');
                $event->sheet->mergeCells('B3:E3');

                $titleRange = 'A5:' . $last_column . '5';
                $event->sheet->mergeCells(sprintf('A5:%s5', $last_column));
                $event->sheet->setCellValue('A5', $this->title);

                // Style header
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000'],
                        ],
                    ],
                    'font' => [
                        'name' => 'Times New Roman',
                        'size' => 12,
                        'bold' => true,
                        'color' => ['argb' => '000'],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                $styleHeader = [
                    'font' => [
                        'name' => 'Times New Roman',
                        'size' => 12,
                        'bold' => true,
                        'color' => ['argb' => '000'],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                $styleSubHeader = [
                    'font' => [
                        'name' => 'Times New Roman',
                        'size' => 12,
                        'color' => ['argb' => '000'],
                        'italic' => true,
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                #Style name
                $styleName = $styleHeader;
                $styleName['font']['italic'] = true;
                $styleName['alignment']['horizontal'] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;

                #Style title
                $styleTitle = $styleHeader;
                $styleTitle['font']['size'] = 14;
                $styleTitle['alignment']['horizontal'] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;

                $styleArray['fill'] = [
                    'color' => ['rgb' => '0E9F6E'],
                ];
                $styleArray['font']['color'] = ['rgb' => 'fff'];
                $event->sheet->getRowDimension(1)->setRowHeight(25);
                $event->sheet->getRowDimension(1)->setRowHeight(25);
                $event->sheet->getRowDimension(6)->setRowHeight(35);
                if (!empty($this->title)) {
                    $event->sheet->getRowDimension(5)->setRowHeight(35);
                }

                if (!empty($footerRange)) {
                    $event->sheet->getRowDimension($last_row)->setRowHeight(25);
                    $styleFooter = $styleArray;
                    $styleFooter['alignment']['horizontal'] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
                    $event->sheet->getDelegate()->getStyle($footerRange)->applyFromArray($styleFooter);
                }
                $event->sheet->getDelegate()->getStyle($nameRange)->applyFromArray($styleName);
                $event->sheet->getDelegate()->getStyle($nameRangeA1)->applyFromArray($styleName);
                $event->sheet->getDelegate()->getStyle($titleRange)->applyFromArray($styleTitle);
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
                for ($i = 3; $i <= 4; $i++) {
                    $event->sheet->getDelegate()->getStyle("A{$i}:{$last_column}{$i}")->applyFromArray($styleSubHeader);
                }
                $styleSubtitle
                    = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000'],
                            ],
                        ],
                        'font' => [
                            'name' => 'Times New Roman',
                            'italic' => true,
                            'color' => ['argb' => '000'],
                        ],
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        ],
                    ];
                // $event->sheet->getDelegate()->getStyle($subtitleRange)->applyFromArray($styleSubtitle);
                $event->sheet->getDelegate()->getColumnDimension("A")->setAutoSize(false);
                $event->sheet->getDelegate()->getColumnDimension("A")->setWidth(22);

                $styleCell = $styleSubtitle;
                $styleCell['alignment']['horizontal'] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
                $styleCell['alignment']['vertical'] = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
                $styleCell['font']['color'] = ['rgb' => 'C00000'];
                $styleCell['font']['bold'] = true;
                if (is_a($this->data, 'Illuminate\Database\Eloquent\Collection')) {
                    $event->sheet->getDelegate()->getStyle("{$last_column}4")->applyFromArray($styleCell);
                } else {
                    $event->sheet->getDelegate()->getStyle("B5")->applyFromArray($styleHeader);
                }

                //style rest
                $styleArrayRest = [
                    'font' => [
                        'name' => 'Times New Roman',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];
                $event->sheet->getDelegate()->getStyle($cellRest)->applyFromArray($styleArrayRest);
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo');
                $drawing->setPath(public_path('/logo/logo_tt.jpg'));
                $drawing->setCoordinates('A2');
                $drawing->setHeight(40);
                $drawing->setWorksheet($event->sheet->getDelegate());
            },
        ];
    }
    public function map($data): array
    {
        $cb = $this->cb_mapping_data;
        return $cb($data);
    }
}
