<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Pub\Controller;

use CMTV\UsernameChange\XF\Entity\User;

use CMTV\UsernameChange\Constants as C;

class Account extends XFCP_Account
{
    protected function accountDetailsSaveProcess(\XF\Entity\User $visitor)
    {
        $form = parent::accountDetailsSaveProcess($visitor);

        /** @var User $visitor */
        $visitor = $visitor;

        if ($visitor->canChangeUsername() && $visitor->canChangeUsernameNow())
        {
            $newUsername = $this->request->get('user.username');

            if ($newUsername !== $visitor->username)
            {
                // Applying new username and updating essential columns

                $form->basicEntitySave($visitor, [
                    'username' => $newUsername,
                    C::dbColumn('username_changes') => $visitor->get(C::dbColumn('username_changes')) + 1,
                    C::dbColumn('username_change_date') => time()
                ]);

                // Creating username change record

                /** @var \CMTV\UsernameChange\Entity\UsernameChange $usernameChange */
                $usernameChange = \XF::em()->create(C::mvc('UsernameChange'));

                $form->basicEntitySave($usernameChange, [
                    'user_id' => $visitor->user_id,
                    'old_username' => $visitor->username,
                    'change_date' => time()
                ]);
            }
        }

        return $form;
    }

    protected function savePrivacyProcess(\XF\Entity\User $visitor)
    {
        $form = parent::savePrivacyProcess($visitor);

        /** @var User $visitor */
        $visitor = $visitor;

        $name = C::dbColumn('allow_view_username_changes');
        $privacyValue = $this->request->get('privacy.' . $name);

        if ($privacyValue && $visitor->canManageUsernameChangeHistoryPrivacy())
        {
            $userPrivacy = $visitor->getRelationOrDefault('Privacy');

            $form->setupEntityInput($userPrivacy, [
                C::dbColumn('allow_view_username_changes') => $privacyValue
            ]);
        }

        return $form;
    }

    /**
     * @return \CMTV\UsernameChange\Repository\UsernameChange
     */
    protected function getUsernameChangeRepo()
    {
        return $this->repository(C::mvc('UsernameChange'));
    }
}