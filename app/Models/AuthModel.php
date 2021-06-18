<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthModel extends Model
{

    protected $table = 'customer';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'id', 'name', 'email','password','status', 'created_at', 'modified_at', 'first_name', 'last_name', 'profile_picture', 'gender', 'age', 'height', 'weight', 'diet_plan_list', 'dietary_goals', 'ethical_rating', 'diet_restriction'
    ];
    protected $createdField = 'created_at';
    protected $updatedField = 'modified_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;

}
