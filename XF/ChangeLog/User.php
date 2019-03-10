<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\ChangeLog;

use CMTV\UsernameChange\Constants as C;

class User extends XFCP_User
{
    protected function getLabelMap()
    {
        $labelMap = parent::getLabelMap();

        $labelMap[C::dbColumn('allow_view_username_changes')] = 'CMTV_UC_view_your_username_changes';
        $labelMap[C::dbColumn('username_changes')] = 'CMTV_UC_username_changes_in_history';

        return $labelMap;
    }

    protected function getFormatterMap()
    {
        $formatterMap = parent::getFormatterMap();

        $formatterMap[C::dbColumn('allow_view_username_changes')] = 'formatPrivacyValue';

        return $formatterMap;
    }
}