<?php

namespace App\Exports\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Единое оформление листов экспорта: закреплённая шапка с брендовой заливкой,
 * автофильтр и тонкие границы по всей таблице.
 */
trait FormatsResultSheet
{
    /** @return array<class-string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");

                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2994A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]],
                ]);
            },
        ];
    }
}
