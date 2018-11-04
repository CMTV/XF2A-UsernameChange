<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\Pub\Controller;

use CMTV\UsernameChange\Repository\UsernameChange;
use CMTV\UsernameChange\XF\Entity\User;
use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class UsernameChanges extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        $user = $this->assertUserExists($params->user_id);

        /** @var User $visitor */
        $visitor = \XF::visitor();

        $canView = $visitor->canViewUsernameChanges($error);

        if (!$canView)
        {
            return $this->error($error);
        }

        if ($editView = $this->filter('edit', 'bool'))
        {
            if(!$this->canEdit($visitor, $user))
            {
                return $this->noPermission();
            }
        }

        $usernameChangeRepo = $this->getUsernameChangeRepo();

        $usernameChanges = $usernameChangeRepo->getUsernameChanges($user, 'DESC');
        $usernameChanges = $usernameChanges->fetch();

        $viewParams = [
            'user' => $user,
            'usernameChanges' => $usernameChanges,
            'editView' => $this->filter('edit', 'bool')
        ];

        return $this->view('CMTV\UsernameChange:UsernameChanges\List', 'CMTV_UC_member_username_changes', $viewParams);
    }

    public function actionClear(ParameterBag $params)
    {
        $user = $this->assertUserExists($params->user_id);

        if ($this->isPost())
        {
            $usernameChanges = $user->getUsernameChanges();

            foreach ($usernameChanges as $usernameChange)
            {
                $usernameChange->delete();
            }

            $user->set('CMTV_UC_username_changes', 0);
            $user->save();

            return $this->redirect($this->buildLink('username-changes', $user));
        }
        else
        {
            $viewParams = [
                'user' => $user
            ];

            return $this->view('CMTV\UsernameChange:UsernameChanges\Clear', 'CMTV_UC_history_clear', $viewParams);
        }
    }

    public function actionEdit(ParameterBag $params)
    {
        $this->assertPostOnly();

        $user = $this->assertUserExists($params->user_id);
        $visitor = \XF::visitor();

        if (!$this->canEdit($visitor, $user))
        {
            return $this->noPermission();
        }

        $toDeleteIds = $this->filter('selected', 'array');

        if (count($toDeleteIds) === 0)
        {
            return $this->error(\XF::phrase('CMTV_UC_please_select_at_least_one_username_change_to_delete'));
        }

        $usernameChanges = $this->finder('CMTV\UsernameChange:UsernameChange')->whereIds($toDeleteIds)->fetch();

        foreach ($usernameChanges as $usernameChange)
        {
            $usernameChange->delete();
        }

        $user->set('CMTV_UC_username_changes', $user->CMTV_UC_username_changes - count($toDeleteIds));
        $user->save();

        return $this->redirect($this->buildLink('username-changes', $user));
    }

    /**
     * @param User $visitor
     * @param User $user
     *
     * @return bool
     */
    protected function canEdit(User $visitor, User $user)
    {
        if ($visitor->canDeleteAnyUsernameChangeHistory())
        {
            return true;
        }

        if ($visitor->user_id === $user->user_id && $visitor->canDeleteOwnUsernameChangeHistory())
        {
            return true;
        }

        return false;
    }

    /**
     * @param string $id
     * @param array|string|null $with
     * @param null|string $phraseKey
     *
     * @return User
     */
    protected function assertUserExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists('XF:User', $id, $with, $phraseKey);
    }

    /**
     * @return UsernameChange
     */
    protected function getUsernameChangeRepo()
    {
        return $this->repository('CMTV\UsernameChange:UsernameChange');
    }
}