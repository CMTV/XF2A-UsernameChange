<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\Job;

use CMTV\UsernameChange\Repository\UsernameChange;
use XF\Job\AbstractRebuildJob;

class UsernameChangesCount extends AbstractRebuildJob
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
        /** @var UsernameChange $usernameChangeRepo */
        $usernameChangeRepo = $this->app->repository('CMTV\UsernameChange:UsernameChange');

        $count = $usernameChangeRepo->getUsernameChangeCount($id);

        $this->app->db()->update('xf_user', ['CMTV_UC_username_changes' => $count], 'user_id = ?', $id);
    }

    protected function getStatusType()
    {
        return \XF::phrase('CMTV_UC_username_change_count_rebuild');
    }
}