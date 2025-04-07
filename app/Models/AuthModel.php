<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthModel extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected string $table = 'customer';

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
        'id',
        'name',
        'email',
        'password',
        'status',
        'created_at',
        'modified_at',
        'first_name',
        'last_name',
        'profile_picture',
        'gender',
        'age',
        'height',
        'weight',
        'diet_plan_list',
        'dietary_goals',
        'ethical_rating',
        'diet_restriction',
    ];

    /**
     * The name of the created timestamp field.
     *
     * @var string
     */
    protected string $createdField = 'created_at';

    /**
     * The name of the updated timestamp field.
     *
     * @var string
     */
    protected string $updatedField = 'modified_at';

    /**
     * Validation rules for the model.
     *
     * @var array
     */
    protected array $validationRules = [];

    /**
     * Custom validation messages.
     *
     * @var array
     */
    protected array $validationMessages = [];

    /**
     * Whether to skip validation.
     *
     * @var bool
     */
    protected bool $skipValidation = false;
}
