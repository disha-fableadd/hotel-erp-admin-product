<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignationModel extends Model
{
    use HasFactory;

    protected $table = 'designation';
    protected $primaryKey = 'id';

    protected $fillable = [
        'branch_id',
        'designation_name',
        'department_id',
    ];

    public $timestamps = true;

    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id', 'id');
    }

    public function getDesignationsWithDepartment()
    {
        return self::query()
            ->leftJoin('department', 'department.id', '=', 'designation.department_id')
            ->select('designation.*', 'department.department_name')
            ->orderByDesc('designation.created_at')
            ->get()
            ->map(static fn ($row) => (array) $row)
            ->all();
    }
}
