<?php

namespace App\Imports;

use App\Jobs\ImportAttendanceJob;
use App\Models\ImportAttendances;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Events\BeforeImport;
use App\Common\CommonConst;

class AttendanceImport implements  ToCollection,WithChunkReading,WithHeadingRow,ShouldQueue, SkipsOnError, SkipsOnFailure, SkipsEmptyRows,WithEvents
{
    use SkipsErrors, SkipsFailures, Importable,RemembersRowNumber,RemembersChunkOffset;
    protected int $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function collection(Collection $rows)
    {
        ImportAttendanceJob::dispatch($rows,$this->id);
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $totalRows = $event->getReader()->getTotalRows() ;
                $totalRowsData=$totalRows['Worksheet'] -1;
               DB::table('imported_attendances')
                   ->where('id', $this->id)

                   ->update([
                        'total'=>$totalRowsData
                    ]);
            }
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return CommonConst::BATCH_SIZE_ATTENDANCE;
    }

    public function chunkSize(): int
    {
        return CommonConst::CHUNK_SIZE_ATTENDANCE;
    }
}





