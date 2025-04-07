<?php
namespace App\Models;
use CodeIgniter\Model;

class EmployeeModel extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected string $table = 'users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * The fields that are allowed to be inserted or updated.
     *
     * @var array
     */
    protected array $allowedFields = [
        'name',
        'email',
        'created_at',
        'updated_at',
    ];
}