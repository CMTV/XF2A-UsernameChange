<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange;

class Listener
{
    public static function criteriaUser($rule, array $data, \XF\Entity\User $user, &$returnValue)
    {
        switch ($rule)
        {
            case 'CMTV_UC_change_count':
                $returnValue = $user->CMTV_UC_username_changes && $user->CMTV_UC_username_changes >= $data['changes'];
                break;
            case 'CMTV_UC_changes_maximum':
                $returnValue = $user->CMTV_UC_username_changes <= $data['changes'];
                break;
        }
    }
}