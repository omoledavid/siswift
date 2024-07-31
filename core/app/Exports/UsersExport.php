<?php

namespace App\Exports;


use App\Models\User;
use Vitorccs\LaravelCsv\Concerns\Exportables\Exportable;
use Vitorccs\LaravelCsv\Concerns\Exportables\FromCollection;
use Vitorccs\LaravelCsv\Concerns\Exportables\FromQuery;
use Vitorccs\LaravelCsv\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return ['ID', 'Firstname', 'Lastname', 'Email'];
    }

    public function collection()
    {
        return User::all(['id', 'firstname', 'lastname', 'email']);
    }
}
