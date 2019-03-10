<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\Job;

use XF\Entity\User;
use XF\Job\AbstractRebuildJob;

use CMTV\UsernameChange\Constants as C;

class UsernameChange extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit("SELECT `user_id` FROM `xf_user` WHERE `user_id` > ? ORDER BY `user_id`", $batch),
            $start
        );
    }

    protected function rebuildById($id)
    {
        /** @var \CMTV\UsernameChange\Repository\UsernameChange $ucRepo */
        $ucRepo = $this->app->repository(C::mvc('UsernameChange'));

        /** @var User $user */
        $user = $this->app->finder('XF:User')->whereId($id)->fetchOne();

        if (!$user)
        {
            return;
        }

        $usernameChanges = $ucRepo->getUsernameChanges($user);

        $count = count($usernameChanges);
        $lastChangeDate = ($count > 0) ? $usernameChanges->first()->change_date : 0;

        $this->app->db()->update('xf_user', [
            C::dbColumn('username_changes') => $count,
            C::dbColumn('username_change_date') => $lastChangeDate
        ], 'user_id = ?', $id);
    }

    protected function getStatusType()
    {
        return \XF::phrase('users');
    }
}