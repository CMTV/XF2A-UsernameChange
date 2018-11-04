<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\Repository;

use CMTV\UsernameChange\XF\Entity\User;
use XF\Mvc\Entity\Repository;

class UsernameChange extends Repository
{
    /**
     * @param User $user
     *
     * @return null|\CMTV\UsernameChange\Entity\UsernameChange
     */
    public function getLastUsernameChange(User $user)
    {
        $finder = $this->finder('CMTV\UsernameChange:UsernameChange');
        $finder->where('user_id', '=', $user->user_id);
        $finder->order('change_date', 'DESC');

        /** @var \CMTV\UsernameChange\Entity\UsernameChange|null $lastUsernameChange */
        $lastUsernameChange = $finder->fetchOne();

        return $lastUsernameChange;
    }

    /**
     * @param User $user
     * @param string $direction
     * @param int $limit
     *
     * @return \XF\Mvc\Entity\Finder
     */
    public function getUsernameChanges(User $user, string $direction = 'ASC', int $limit = -1)
    {
        $finder = $this->finder('CMTV\UsernameChange:UsernameChange');
        $finder->where('user_id', '=', $user->user_id);
        $finder->order('change_date', $direction);

        if ($limit !== -1)
        {
            $finder->limit($limit);
        }

        return $finder;
    }

    /**
     * @param User $user
     *
     * @return int
     */
    public function getNextUsernameChangeTimestamp(User $user)
    {
        if ($user->canChangeUsernameFreely())
        {
            return time();
        }

        return $user->CMTV_UC_username_change_date + $user->getUsernameChangeFrequency() * 86400;
    }

    /**
     * @param User $user
     *
     * @return string
     */
    public function getNextUsernameChangeDateTime(User $user)
    {
        return \XF::language()->dateTime($this->getNextUsernameChangeTimestamp($user));
    }

    /**
     * @param int|User $userId
     *
     * @return int
     */
    public function getUsernameChangeCount($userId)
    {
        if ($userId instanceof \XF\Entity\User)
        {
            $userId = $userId->user_id;
        }

        $query = "SELECT COUNT(*) FROM `xf_cmtv_uc_username_change` WHERE `user_id` = ?";

        return $this->db()->fetchOne($query, $userId);
    }
}