<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\Entity;

use CMTV\UsernameChange\XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int change_id
 * @property int user_id
 * @property string old_username
 * @property int change_date
 *
 * RELATIONS
 * @property \XF\Entity\User User
 */
class UsernameChange extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_cmtv_uc_username_change';
        $structure->shortName = 'CMTV\UsernameChange:UsernameChange';
        $structure->primaryKey = 'change_id';
        $structure->contentType = 'username_change';

        $structure->columns = [
            'change_id' => [
                'type' => self::UINT,
                'autoIncrement' => true
            ],
            'user_id' => [
                'type' => self::UINT
            ],
            'old_username' => [
                'type' => self::STR,
                'maxLength' => 50
            ],
            'change_date' => [
                'type' => self::UINT,
                'default' => 0
            ]
        ];

        $structure->getters = [
            'new_username' => true
        ];

        $structure->relations = [
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ]
        ];

        $structure->behaviors = [
            'XF:NewsFeedPublishable' => [
                'userIdField' => 'user_id',
                'dateField' => 'change_date'
            ]
        ];

        return $structure;
    }

    /**
     * @return string
     */
    public function getNewUsername()
    {
        $finder = $this->finder('CMTV\UsernameChange:UsernameChange');
        $finder
            ->where('change_date', '>', $this->change_date)
            ->where('user_id', $this->user_id)
            ->limit(1);

        /** @var UsernameChange|null $nextChange */
        if ($nextChange = $finder->fetchOne())
        {
            $newUsername = $nextChange->old_username;
        }
        else
        {
            $newUsername = $this->User->username;
        }

        return $newUsername;
    }

    public function canView()
    {
        /** @var User $visitor */
        $visitor = \XF::visitor();

        return $visitor->canViewUsernameChanges();
    }
}