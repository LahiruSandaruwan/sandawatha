<?php
require_once 'BaseModel.php';
require_once __DIR__ . '/../helpers/UuidTrait.php';

class RoleModel extends BaseModel {
    use UuidTrait;
    
    protected $table = 'roles';
    
    protected function getAllowedColumns() {
        return ['uuid', 'name', 'slug', 'description', 'permissions', 'is_active', 'created_at', 'updated_at'];
    }
    
    /**
     * Get role by slug
     */
    public function findBySlug($slug) {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * Get all active roles
     */
    public function getActiveRoles() {
        return $this->findAllBy('is_active', 1);
    }
    
    /**
     * Get user roles
     */
    public function getUserRoles($userId) {
        $sql = "SELECT r.* FROM {$this->table} r
                INNER JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id AND ur.is_active = 1 AND r.is_active = 1";
        
        return $this->fetchAll($sql, [':user_id' => $userId]);
    }
    
    /**
     * Assign role to user
     */
    public function assignToUser($userId, $roleId, $assignedBy = null) {
        require_once 'UserRoleModel.php';
        $userRoleModel = new UserRoleModel();
        
        return $userRoleModel->create([
            'user_id' => $userId,
            'role_id' => $roleId,
            'assigned_by' => $assignedBy,
            'assigned_at' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ]);
    }
    
    /**
     * Remove role from user
     */
    public function removeFromUser($userId, $roleId) {
        require_once 'UserRoleModel.php';
        $userRoleModel = new UserRoleModel();
        
        $sql = "UPDATE user_roles SET is_active = 0 
                WHERE user_id = :user_id AND role_id = :role_id";
        
        return $userRoleModel->execute($sql, [
            ':user_id' => $userId,
            ':role_id' => $roleId
        ]);
    }
    
    /**
     * Check if user has role
     */
    public function userHasRole($userId, $roleSlug) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} r
                INNER JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id AND r.slug = :role_slug 
                AND ur.is_active = 1 AND r.is_active = 1";
        
        $result = $this->fetchOne($sql, [
            ':user_id' => $userId,
            ':role_slug' => $roleSlug
        ]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Check if user has permission
     */
    public function userHasPermission($userId, $permission) {
        $sql = "SELECT r.permissions FROM {$this->table} r
                INNER JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id AND ur.is_active = 1 AND r.is_active = 1";
        
        $roles = $this->fetchAll($sql, [':user_id' => $userId]);
        
        foreach ($roles as $role) {
            $permissions = json_decode($role['permissions'], true);
            if (in_array('*', $permissions) || in_array($permission, $permissions)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get user permissions
     */
    public function getUserPermissions($userId) {
        $sql = "SELECT r.permissions FROM {$this->table} r
                INNER JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id AND ur.is_active = 1 AND r.is_active = 1";
        
        $roles = $this->fetchAll($sql, [':user_id' => $userId]);
        $permissions = [];
        
        foreach ($roles as $role) {
            $rolePermissions = json_decode($role['permissions'], true);
            $permissions = array_merge($permissions, $rolePermissions);
        }
        
        return array_unique($permissions);
    }
    
    /**
     * Create role with permissions
     */
    public function createRole($name, $slug, $description, $permissions, $isActive = true) {
        return $this->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'permissions' => json_encode($permissions),
            'is_active' => $isActive ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Update role permissions
     */
    public function updatePermissions($roleId, $permissions) {
        return $this->update($roleId, [
            'permissions' => json_encode($permissions),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
} 