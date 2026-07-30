<?php

use App\Models\Apps\SystemSettingModel;

if (!function_exists('app_setting')) {
    function app_setting(string $key, $default = null)
    {
        static $settings = null;

        if ($settings === null) {
            $settings = new SystemSettingModel();
        }

        try {
            return $settings->getValue($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

