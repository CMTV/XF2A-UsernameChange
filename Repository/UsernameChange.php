<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\Repository;

use XF\Entity\PermissionEntry;
use XF\Entity\User;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

use CMTV\UsernameChange\Constants as C;

class UsernameChange extends Repository
{
    public function getUsernameChanges(User $user, int $limit = -1, string $direction = 'DESC')
    {
        $finder = $this->finder(C::mvc('UsernameChange'));
        $finder->where('user_id', $user->user_id);
        $finder->order('change_date', $direction);

        if ($limit >= 0)
        {
            $finder->limit($limit);
        }

        return $finder->fetch();
    }

    public function getNextChangeTime(User $user)
    {
        // Getting min int permission value

        $userGroups =   $user->secondary_group_ids;
        $userGroups[] = $user->user_group_id;

        /** @var Finder $permissionFinder */
        $permissionFinder = $this->finder('XF:PermissionEntry');

        $permissionFinder
            ->where('permission_group_id', C::ADDON_ID_SHORT)
            ->where('permission_id', 'changeFrequency')
            ->whereOr([
                ['user_group_id', $userGroups],
                ['user_id', $user->user_id]
            ]);

        $permissions = $permissionFinder->fetch();

        $lowestValue = $permissions->first()->permission_value_int;

        /** @var PermissionEntry $permission */
        foreach ($permissions as $permission)
        {
            $lowestValue = min($lowestValue, $permission->permission_value_int);
        }

        // --

        $changeFrequency = $lowestValue;

        if ($changeFrequency === -1)
        {
            return time();
        }

        return $user->getValue(C::dbColumn('username_change_date')) + $changeFrequency * 86400;
    }
}