<?php

namespace Modules\Imports\Support;

use Maatwebsite\Excel\Concerns\FromArray;

class TemplateExport implements FromArray
{
    public function __construct(private readonly array $headers) {}

    public function array(): array
    {
        return [$this->headers];
    }
}
