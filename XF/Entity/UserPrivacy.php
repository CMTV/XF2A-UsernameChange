<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Entity;

use XF\Mvc\Entity\Structure;

use CMTV\UsernameChange\Constants as C;

/**
 * COLUMNS
 * @property string cmtv_uc_allow_view_username_changes
 */
class UserPrivacy extends XFCP_UserPrivacy
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns[C::dbColumn('allow_view_username_changes')] = [
            'type' => self::STR,
            'default' => 'everyone',
            'allowedValues' => [
                'everyone',
                'members',
                'followed',
                'none'
            ],
            'verify' => 'verifyPrivacyChoice'
        ];

        return $structure;
    }
}