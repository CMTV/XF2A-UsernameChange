<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Pub\Controller;

use CMTV\UsernameChange\Entity\UsernameChange;
use CMTV\UsernameChange\XF\Entity\User;
use XF\Entity\UserPrivacy;
use XF\Repository\NewsFeed;

class Account extends XFCP_Account
{
    public function actionAccountDetails()
    {
        $view = parent::actionAccountDetails();

        if ($this->isPost())
        {
            return $view;
        }

        /** @var User $visitor */
        $visitor = \XF::visitor();

        if ($visitor->isTimeLimitExpired())
        {
            if ($visitor->canChangeUsernameFreely())
            {
                $explain = \XF::phrase('CMTV_UC_username_unlimited_explain');
            }
            else
            {
                $explain = \XF::phrase('CMTV_UC_username_days_explain', ['days' => $visitor->getUsernameChangeFrequency()]);
            }
        }
        else
        {
            $usernameChangeRepo = $this->getUsernameChangeRepo();
            $nextChange = $usernameChangeRepo->getNextUsernameChangeDateTime($visitor);

            $explain = \XF::phrase('CMTV_UC_username_disabled_explain', ['nextChange' => $nextChange]);
        }

        $view->setParam('CMTV_UC_explain', $explain);

        return $view;
    }

    protected function accountDetailsSaveProcess(\XF\Entity\User $visitor)
    {
        $form = parent::accountDetailsSaveProcess($visitor);

        /** @var \CMTV\UsernameChange\XF\Entity\User $visitor */
        $visitor = $visitor;

        if ($visitor->canChangeUsername() && $visitor->isTimeLimitExpired())
        {
            $input = $this->filter(['user' => ['username' => 'str']]);

            if ($input['user']['username'] !== $visitor->username)
            {
                /** @var UsernameChange $usernameChange */
                $usernameChange = $this->em()->create('CMTV\UsernameChange:UsernameChange');

                $usernameChange->bulkSet([
                    'user_id' => $visitor->user_id,
                    'old_username' => $visitor->username,
                    'change_date' => time()
                ]);

                $usernameChange->preSave();

                if (!$usernameChange->preSave())
                {
                    return $this->error($usernameChange->getErrors());
                }

                $usernameChange->save();

                $form->basicEntitySave($visitor, $input['user']);

                $visitor->fastUpdate('CMTV_UC_username_changes', $visitor->CMTV_UC_username_changes + 1);
                $visitor->fastUpdate('CMTV_UC_username_change_date', time());
            }
        }

        return $form;
    }

    protected function savePrivacyProcess(\XF\Entity\User $visitor)
    {
        $form = parent::savePrivacyProcess($visitor);

        $privacy = $this->filter('privacy', 'array');

        /** @var UserPrivacy $userPrivacy */
        $userPrivacy = $visitor->getRelationOrDefault('Privacy');
        $userPrivacy->set('CMTV_UC_allow_view_username_changes', $privacy['CMTV_UC_allow_view_username_changes']);
        $userPrivacy->save();

        return $form;
    }

    /**
     * @return \CMTV\UsernameChange\Repository\UsernameChange
     */
    protected function getUsernameChangeRepo()
    {
        return $this->repository('CMTV\UsernameChange:UsernameChange');
    }
}