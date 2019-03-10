<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange;

class Constants
{
    const ADDON_ID = 'CMTV\UsernameChange';
    const ADDON_ID_ESC = 'CMTV_UsernameChange';
    const ADDON_ID_SHORT = 'CMTV_UC';

    //

    public static function mvc(string $content): string
    {
        return self::ADDON_ID . ':' . $content;
    }

    public static function pre(string $content): string
    {
        return self::ADDON_ID_SHORT . '_' . $content;
    }

    public static function option(string $id): string
    {
        return self::pre($id);
    }

    public static function phrase(string $phraseName): string
    {
        return self::pre($phraseName);
    }

    public static function template(string $templateName): string
    {
        return self::pre($templateName);
    }

    public static function dbTable(string $tableName): string
    {
        return 'xf_' . utf8_strtolower(self::ADDON_ID_SHORT) . '_' . $tableName;
    }

    public static function dbColumn(string $columnName): string
    {
        return utf8_strtolower(self::ADDON_ID_SHORT) . '_' . $columnName;
    }
}