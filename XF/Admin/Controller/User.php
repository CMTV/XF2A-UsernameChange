<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Admin\Controller;

use CMTV\UsernameChange\Constants as C;

class User extends XFCP_User
{
    protected function userSaveProcess(\XF\Entity\User $user)
    {
        $form = parent::userSaveProcess($user);

        if ($user->isInsert())
        {
            return $form;
        }

        /** @var \CMTV\UsernameChange\XF\Entity\User $user */
        $newUsername = $this->request->get('user')['username'];

        if ($newUsername !== $user->username)
        {
            // Only update essential columns. No need to change username. It is done in the parent method
            $form->basicEntitySave($user, [
                C::dbColumn('username_changes') => $user->get(C::dbColumn('username_changes')) + 1,
                C::dbColumn('username_change_date') => time()
            ]);

            // Creating username change record

            /** @var \CMTV\UsernameChange\Entity\UsernameChange $usernameChange */
            $usernameChange = \XF::em()->create(C::mvc('UsernameChange'));

            $form->basicEntitySave($usernameChange, [
                'user_id' => $user->user_id,
                'old_username' => $user->username,
                'change_date' => time(),
                'from_acp' => true
            ]);
        }

        return $form;
    }
}