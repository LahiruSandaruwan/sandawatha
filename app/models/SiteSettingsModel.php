<?php
require_once 'BaseModel.php';

class SiteSettingsModel extends BaseModel {
    protected $table = 'site_settings';
    
    public function getSetting($key) {
        $result = $this->findBy('setting_key', $key);
        return $result ? $result['setting_value'] : null;
    }
    
    public function updateSetting($key, $value) {
        $existing = $this->findBy('setting_key', $key);
        
        if ($existing) {
            return $this->update($existing['id'], [
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            return $this->create([
                'setting_key' => $key,
                'setting_value' => $value,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    public function getAllSettings() {
        $results = $this->findAll();
        $settings = [];
        
        foreach ($results as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $settings;
    }
    
    public function deleteSetting($key) {
        $setting = $this->findBy('setting_key', $key);
        if ($setting) {
            return $this->delete($setting['id']);
        }
        return false;
    }
}
?>