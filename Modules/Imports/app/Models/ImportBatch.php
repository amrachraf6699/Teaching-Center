<?php

namespace Modules\Imports\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    protected $fillable = ['user_id', 'type', 'file_name', 'status', 'total_rows', 'imported_rows', 'failed_rows', 'errors'];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
