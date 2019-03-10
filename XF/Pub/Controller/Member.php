<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Pub\Controller;

use CMTV\UsernameChange\Entity\UsernameChange;
use CMTV\UsernameChange\XF\Entity\User;
use XF\Mvc\ParameterBag;

use CMTV\UsernameChange\Constants as C;

class Member extends XFCP_Member
{
    public function actionUsernameChanges(ParameterBag $params)
    {
        /** @var User $user */
        $user = $this->assertViewableUser($params->user_id);

        /** @var User $visitor */
        $visitor = \XF::visitor();

        if (!$visitor->canViewUsernameChangesHistory($user, $error))
        {
            return $this->error($error);
        }

        if ($editView = $this->filter('edit', 'bool'))
        {
            if (!$visitor->canEditUsernameChangesHistory($user))
            {
                return $this->noPermission();
            }
        }

        $usernameChanges = $user->getUsernameChanges();

        $viewParams = [
            'user' => $user,
            'usernameChanges' => $usernameChanges,
            'editView' => $editView
        ];

        return $this->view(
            C::mvc('UsernameChange\List'),
            C::template('member_username_changes'),
            $viewParams
        );
    }

    public function actionUsernameChangesClear(ParameterBag $params)
    {
        /** @var User $user */
        $user = $this->assertViewableUser($params->user_id);

        /** @var User $visitor */
        $visitor = \XF::visitor();

        if (!$visitor->canEditUsernameChangesHistory($user))
        {
            return $this->noPermission();
        }

        if ($this->isPost())
        {
            $usernameChanges = $user->getUsernameChanges();

            /** @var UsernameChange $usernameChange */
            foreach ($usernameChanges as $usernameChange)
            {
                $usernameChange->delete();
            }

            $user->set(C::dbColumn('username_changes'), 0);
            $user->save();

            return $this->redirectPermanently($this->buildLink('members/username-changes', $user));
        }
        else
        {
            $viewParams = [
                'user' => $user,
                'total' => $user->cmtv_uc_username_changes
            ];

            return $this->view(
                C::mvc('UsernameChange\List\Clear'),
                C::template('history_clear'),
                $viewParams
            );
        }
    }

    public function actionUsernameChangesEdit(ParameterBag $params)
    {
        /** @var User $user */
        $user = $this->assertViewableUser($params->user_id);

        /** @var User $visitor */
        $visitor = \XF::visitor();

        if (!$visitor->canEditUsernameChangesHistory($user))
        {
            return $this->noPermission();
        }

        $toDeleteIds = $this->filter('selected', 'array');

        if (count($toDeleteIds) === 0)
        {
            return $this->error(\XF::phrase(C::phrase('please_select_at_least_one_username_change_to_delete')));
        }

        $usernameChanges = $this->finder(C::mvc('UsernameChange'))->whereIds($toDeleteIds)->fetch();

        /** @var UsernameChange $usernameChange */
        foreach ($usernameChanges as $usernameChange)
        {
            $usernameChange->delete();
        }

        $user->set(C::dbColumn('username_changes'), $user->cmtv_uc_username_changes - count($toDeleteIds));
        $user->save();

        return $this->redirectPermanently($this->buildLink('members/username-changes', $user));
    }
}