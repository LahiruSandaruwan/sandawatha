<?php

namespace App\models;

use Exception;
use PDOException;

class UserRoleModel extends BaseModel {
    protected $table = 'user_roles';
    
    protected function getAllowedColumns() {
        return [
            'user_id',
            'role_id',
            'assigned_by',
            'assigned_at',
            'is_active',
            'created_at',
            'updated_at'
        ];
    }
} 