<?php


namespace App\Helper;


trait PlatformHelper
{
    /**
     * 获取访问平台
     * @return string
     */
    public static function get()
    {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($ua, 'android') !== false) {
            return 'android';
        }
        if (strpos($ua, 'iphone') !== false) {
            return 'ios';
        }
        if (strpos($ua, 'win') !== false) {
            return 'pc';
        }
        if (strpos($ua, 'mac os') !== false) {
            return 'macos';
        }
        return '';
    }
}
