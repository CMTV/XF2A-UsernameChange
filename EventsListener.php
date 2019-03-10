<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange;

use XF\Entity\User;

use CMTV\UsernameChange\Constants as C;

class EventsListener
{
    public static function criteriaUser($rule, array $data, User $user, &$returnValue)
    {
        /** @var \CMTV\UsernameChange\XF\Entity\User $user */
        $user = $user;

        switch ($rule)
        {
            case C::pre('change_count'):
                $returnValue = $user->cmtv_uc_username_changes && $user->cmtv_uc_username_changes >= $data['changes'];
                break;
            case C::pre('changes_maximum'):
                $returnValue = $user->cmtv_uc_username_changes && $user->cmtv_uc_username_changes <= $data['changes'];
                break;
        }
    }
}