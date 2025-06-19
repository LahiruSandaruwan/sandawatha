<?php

namespace App\helpers;

trait UuidTrait {
    
    /**
     * Generate a UUID for new records
     */
    protected function generateUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Find record by UUID instead of ID
     */
    public function findByUuid($uuid) {
        $sql = "SELECT * FROM {$this->table} WHERE uuid = :uuid LIMIT 1";
        return $this->fetchOne($sql, [':uuid' => $uuid]);
    }
    
    /**
     * Override create method to automatically generate UUID
     */
    public function create($data) {
        if (!isset($data['uuid'])) {
            $data['uuid'] = $this->generateUuid();
        }
        return parent::create($data);
    }
    
    /**
     * Get user ID from UUID (for internal operations)
     */
    public function getIdFromUuid($uuid) {
        $record = $this->findByUuid($uuid);
        return $record ? $record['id'] : null;
    }
    
    /**
     * Get UUID from ID (for public URLs)
     */
    public function getUuidFromId($id) {
        $sql = "SELECT uuid FROM {$this->table} WHERE id = :id LIMIT 1";
        $result = $this->fetchOne($sql, [':id' => $id]);
        return $result ? $result['uuid'] : null;
    }
    
    /**
     * Update record by UUID
     */
    public function updateByUuid($uuid, $data) {
        $id = $this->getIdFromUuid($uuid);
        if (!$id) {
            return false;
        }
        return $this->update($id, $data);
    }
    
    /**
     * Delete record by UUID
     */
    public function deleteByUuid($uuid) {
        $sql = "DELETE FROM {$this->table} WHERE uuid = :uuid";
        return $this->execute($sql, [':uuid' => $uuid]);
    }
    
    /**
     * Check if UUID exists
     */
    public function uuidExists($uuid) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE uuid = :uuid";
        $result = $this->fetchOne($sql, [':uuid' => $uuid]);
        return $result['count'] > 0;
    }
} 