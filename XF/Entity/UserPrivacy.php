<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Entity;


use XF\Mvc\Entity\Structure;

class UserPrivacy extends XFCP_UserPrivacy
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['CMTV_UC_allow_view_username_changes'] = [
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