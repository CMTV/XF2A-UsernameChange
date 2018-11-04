<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\ChangeLog;

class User extends XFCP_User
{
    protected function getLabelMap()
    {
        $labelMap = parent::getLabelMap();

        $labelMap['CMTV_UC_allow_view_username_changes'] = 'CMTV_UC_view_your_username_changes';
        $labelMap['CMTV_UC_username_changes'] = 'CMTV_UC_username_changes_in_history';

        return $labelMap;
    }

    protected function getFormatterMap()
    {
        $formatterMap = parent::getFormatterMap();

        $formatterMap['CMTV_UC_allow_view_username_changes'] = 'formatPrivacyValue';

        return $formatterMap;
    }
}