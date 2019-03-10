<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Entity;

use CMTV\UsernameChange\Repository\UsernameChange;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Structure;

use CMTV\UsernameChange\Constants as C;

/**
 * COLUMNS
 * @property int cmtv_uc_username_changes
 * @property int cmtv_uc_username_change_date
 *
 * GETTERS
 * @property ArrayCollection[Entity] recent_username_changes
 * @property ArrayCollection[Entity] username_changes
 * @property int next_username_change_time
 */
class User extends XFCP_User
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns[C::dbColumn('username_changes')] = [
            'type' => self::UINT,
            'default' => 0
        ];

        $structure->columns[C::dbColumn('username_change_date')] = [
            'type' => self::UINT,
            'default' => 0,
            'changeLog' => false
        ];

        $structure->getters['recent_username_changes'] = true;
        $structure->getters['username_changes'] = true;
        $structure->getters['next_username_change_time'] = true;

        return $structure;
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $db = $this->db();
        $userId = $this->user_id;

        $db->delete(C::dbTable('username_change'), 'user_id = ?', $userId);
    }

    public function getRecentUsernameChanges()
    {
        $recentNum = \XF::options()->offsetGet(C::option('recentUsernameChanges'));

        return $this->getUsernameChangeRepo()->getUsernameChanges($this, $recentNum);
    }

    public function getUsernameChanges()
    {
        return $this->getUsernameChangeRepo()->getUsernameChanges($this);
    }

    public function getNextUsernameChangeTime()
    {
        return $this->getUsernameChangeRepo()->getNextChangeTime($this);
    }

    public function canChangeUsername()
    {
        return $this->user_id && $this->hasPermission(C::ADDON_ID_SHORT, 'changeUsername');
    }

    public function canManageUsernameChangeHistoryPrivacy()
    {
        return $this->user_id && $this->hasPermission(C::ADDON_ID_SHORT, 'manageHistoryPrivacy');
    }

    public function canChangeUsernameNow()
    {
        if (!$this->user_id)
        {
            return false;
        }

        return time() >= $this->getUsernameChangeRepo()->getNextChangeTime($this);
    }

    public function canEditUsernameChangesHistory(User $user)
    {
        if (!$user->user_id)
        {
            return false;
        }

        if ($this->user_id === $user->user_id)
        {
            return $this->hasPermission(C::ADDON_ID_SHORT, 'editHistory');
        }

        return $this->hasPermission(C::ADDON_ID_SHORT, 'editAnyHistory');
    }

    public function canViewUsernameChangesHistory(User $user = null, &$error = null)
    {
        if (func_num_args() === 0)
        {
            return $this->user_id && $this->hasPermission(C::ADDON_ID_SHORT, 'viewHistory');
        }

        if (!$user->user_id)
        {
            return false;
        }

        if ($this->user_id === $user->user_id)
        {
            return true;
        }

        if (!$this->hasPermission(C::ADDON_ID_SHORT, 'viewHistory'))
        {
            $error = \XF::phraseDeferred('do_not_have_permission');
            return false;
        }

        if (!$user->isPrivacyCheckMet(C::dbColumn('allow_view_username_changes'), $this))
        {
            $error = \XF::phraseDeferred(C::phrase('member_limits_viewing_username_changes'));
            return false;
        }

        return true;
    }

    /**
     * @return UsernameChange
     */
    protected function getUsernameChangeRepo()
    {
        return $this->repository(C::mvc('UsernameChange'));
    }
}