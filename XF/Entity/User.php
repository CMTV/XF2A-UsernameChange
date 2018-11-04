<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Entity;

use CMTV\UsernameChange\Repository\UsernameChange;
use XF\Entity\PermissionEntry;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int CMTV_UC_username_changes
 * @property int CMTV_UC_username_change_date
 *
 * GETTERS
 * @property \CMTV\UsernameChange\Entity\UsernameChange[] recent_username_changes
 * @property \CMTV\UsernameChange\Entity\UsernameChange[] username_changes
 */
class User extends XFCP_User
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['CMTV_UC_username_changes'] = [
            'type' => self::UINT,
            'default' => 0
        ];
        $structure->columns['CMTV_UC_username_change_date'] = [
            'type' => self::UINT,
            'default' => 0
        ];

        $structure->getters['recent_username_changes'] = true;
        $structure->getters['username_changes'] = true;

        return $structure;
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $db = $this->db();
        $userId = $this->user_id;

        $db->delete('xf_cmtv_uc_username_change', 'user_id = ?', $userId);
    }

    public function getRecentUsernameChanges()
    {
        $usernameChangeRepo = $this->getUsernameChangeRepo();

        $recentLimit = $this->app()->options()->CMTV_UC_recentUsernameChanges;

        $usernameChanges = $usernameChangeRepo->getUsernameChanges($this, 'DESC', $recentLimit)->fetch();

        return $usernameChanges;
    }

    public function getUsernameChanges()
    {
        $usernameChangeRepo = $this->getUsernameChangeRepo();

        $usernameChanges = $usernameChangeRepo->getUsernameChanges($this, 'DESC')->fetch();

        return $usernameChanges;
    }

    public function canChangeUsername()
    {
        return $this->user_id && $this->hasPermission('CMTV_UC', 'changeUsername');
    }

    public function canViewUsernameChanges(&$error = null)
    {
        $visitor = \XF::visitor();

        if ($visitor->user_id == $this->user_id)
        {
            return true;
        }

        if (!$this->hasPermission('CMTV_UC', 'viewUsernameChanges'))
        {
            $error = \XF::phraseDeferred('do_not_have_permission');
            return false;
        }

        if (!$this->isPrivacyCheckMet('CMTV_UC_allow_view_username_changes', $visitor))
        {
            $error = \XF::phraseDeferred('CMTV_UC_member_limits_viewing_username_changes');
            return false;
        }

        return true;
    }

    public function canManageUsernameChangePrivacy()
    {
        return $this->user_id && $this->hasPermission('CMTV_UC', 'manageHistoryPrivacy');
    }

    public function canChangeUsernameFreely()
    {
        $changeFrequency = $this->hasPermission('CMTV_UC', 'changeFrequency');

        return $changeFrequency === -1;
    }

    public function isTimeLimitExpired()
    {
        if ($this->canChangeUsernameFreely())
        {
            return true;
        }

        return time() >= ($this->CMTV_UC_username_change_date + $this->getUsernameChangeFrequency() * 86400);
    }

    public function canDeleteOwnUsernameChangeHistory()
    {
        return $this->user_id && $this->hasPermission('CMTV_UC', 'deleteOwnHistory');
    }

    public function canDeleteAnyUsernameChangeHistory()
    {
        return $this->user_id && $this->hasPermission('CMTV_UC', 'deleteAnyHistory');
    }

    public function hasUsernameChangeHistory()
    {
        if ($this->user_id)
        {
            $usernameChangeRepo = $this->getUsernameChangeRepo();
            $lastUsernameChange = $usernameChangeRepo->getLastUsernameChange($this);

            return boolval($lastUsernameChange);
        }

        return false;
    }

    /**
     * @return int
     */
    public function getUsernameChangeFrequency()
    {
        $userGroups = $this->secondary_group_ids;
        $userGroups[] = $this->user_group_id;

        /** @var Finder $permissionFinder */
        $permissionFinder = $this->finder('XF:PermissionEntry');
        $permissionFinder
            ->where('permission_group_id', 'CMTV_UC')
            ->where('permission_id', 'changeFrequency')
            ->whereOr([
                ['user_group_id', $userGroups],
                ['user_id', $this->user_id]
            ]);

        $permissions = $permissionFinder->fetch();

        $days = $permissions->first()->permission_value_int;

        /** @var PermissionEntry $permission */
        foreach ($permissions as $permission)
        {
            $days = min($days, $permission->permission_value_int);
        }

        if ($days === -1)
        {
            $days = 0;
        }

        return $days;
    }

    /**
     * @return UsernameChange
     */
    protected function getUsernameChangeRepo()
    {
        return $this->repository('CMTV\UsernameChange:UsernameChange');
    }
}