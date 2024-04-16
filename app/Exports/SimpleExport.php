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

class SimpleExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents, WithBackgroundColor
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
    public function __construct($sheet, $data, $headings, $cb_mapping_data)
    {
        $this->sheet = $sheet;
        $this->data = $data;
        $this->headings = $headings;
        $this->cb_mapping_data = $cb_mapping_data;
        $this->length = count($this->data);
        ++$this->length;
    }
    public function collection()
    {
        return collect($this->data);
    }
    public function headings(): array
    {
        return array_map(function ($header) {
            return $header['text'] ?? $header;
        }, $this->headings);
    }
    // public function columnWidths(): array
    // {
    //     $widths = [];
    //     foreach ($this->headings as $index => $value) {
    //         $widths[Coordinate::stringFromColumnIndex($index + 1)] = 100;
    //     }
    //     return $widths;
    // }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $last_column = Coordinate::stringFromColumnIndex(count($this->headings));
                // $before_last_column = Coordinate::stringFromColumnIndex(count($this->headings) - 1);
                $row_header = 1;
                $row_start_data = $row_header + 1;
                $rangeHeader = "A$row_header:" . $last_column . "$row_header";

                $styleHeaders = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000'],
                        ],
                    ],
                    'font' => [
                        'name' => 'Times New Roman',
                        'size' => 14,
                        'bold' => true,
                        'color' => ['argb' => '000'],
                    ],
                    'fill' => [
                        'color' => ['rgb' => '0E9F6E'],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];
                $event->sheet->getDelegate()->getStyle($rangeHeader)->applyFromArray($styleHeaders);
                $event->sheet->getRowDimension($row_header)->setRowHeight(25);

                $rangeBody = "A$row_start_data:" . $last_column . ($this->length + 5);
                $styleBody = [
                    'font' => [
                        'name' => 'Times New Roman',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000'],
                        ],
                    ],
                ];
                $event->sheet->getDelegate()->getStyle($rangeBody)->applyFromArray($styleBody);
            },
        ];
    }
    public function map($data): array
    {
        $cb = $this->cb_mapping_data;
        return $cb($data);
    }
}
