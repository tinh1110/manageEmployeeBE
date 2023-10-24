<?php

namespace App\Imports;

use App\Jobs\ImportUserJob;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, SkipsOnError, SkipsOnFailure, WithValidation, SkipsEmptyRows
{

    use SkipsErrors, SkipsFailures, Importable;
    
    /**
    * @param Collection $rows
    *
    * @return void
    */
    public function collection(Collection $rows)
    {
        ImportUserJob::dispatch($rows);
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            '*.name' => 'required|string',
            '*.email' => 'required|string|email:rfc,dns|unique:users,email',
            '*.password' => 'required|min:6',
            '*.address' => 'nullable',
            '*.phone_number' => 'nullable|regex:/(0)[0-9]{9}/',
            '*.dob' => 'nullable|before:today',
            '*.details' => 'nullable',
            '*.gender' => 'required|in:1,2,3',
            '*.role_id' => 'required|in:1',
            '*.status' => 'required|in:0,1',
        ];
    }

}
