<?php
namespace App\Excel\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;

class ChecklistStructureExport implements FromView, WithEvents, WithTitle, ShouldAutoSize
{

    
    use Exportable;
    
    private $data;

    public function __construct($data) 
    {
        $this->data = $data;
    }


    public function view(): View
    {
        return view('exports.structure_checklist', [
            'data' => $this->data
        ]);
    }

    public function registerEvents(): array
    {
        

        return [
            AfterSheet::class => function(AfterSheet $event) {

                $rows = $event->sheet->getHighestRow();                
                                                              
                // Add styles for sheet
                $event->sheet->getParent()->getDefaultStyle()->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 10
                    ]
                ]);   

                // Add styles for header
                $headersRange = "A1:".$event->sheet->getDelegate()->getHighestColumn()."1";  

                $event->sheet->getDelegate()->getStyle($headersRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);   

                // Row Height
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(16);
                
                // Center Columns
                $event->sheet->getDelegate()->getStyle("B2:B".($rows+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle("D2:D".($rows+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle("F2:F".($rows+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // AutoFilter
                $dataRange = $event->sheet->getDelegate()->calculateWorksheetDimension();                                
                $event->sheet->getDelegate()->setAutoFilter($dataRange);

                // FreezePanes
                $event->sheet->getDelegate()->freezePane("A2");                

            },
        ];
    }      

    public function title(): string
    {
        return 'Datos';
    }
}