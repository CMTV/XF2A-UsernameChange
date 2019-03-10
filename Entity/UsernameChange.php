<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\Entity;

use CMTV\UsernameChange\XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

use CMTV\UsernameChange\Constants as C;

/**
 * COLUMNS
 * @property int change_id
 * @property int user_id
 * @property string old_username
 * @property int change_date
 * @property bool from_acp
 *
 * GETTERS
 * @property string new_username
 *
 * RELATIONS
 * @property \XF\Entity\User User
 */
class UsernameChange extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table =         C::dbTable('username_change');
        $structure->shortName =     C::mvc('UsernameChange');
        $structure->primaryKey =    'change_id';
        $structure->contentType =   'username_change';

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
            ],
            'from_acp' => [
                'type' => self::BOOL,
                'default' => false
            ]
        ];

        $structure->getters = [
            'new_username' => [
                'getter' => true,
                'cache' => true
            ]
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

    public function getNewUsername()
    {
        $finder = $this->finder(C::mvc('UsernameChange'))
            ->where('change_id', '>', $this->change_id)
            ->where('user_id', $this->user_id)
            ->limit(1);

        $newUsername = ($nextUC = $finder->fetchOne()) ? $nextUC->old_username : $this->User->username;

        return $newUsername;
    }

    public function canView()
    {
        /** @var User $visitor */
        $visitor = \XF::visitor();

        return $visitor->canViewUsernameChangesHistory($this->User);
    }

    public function isVisible()
    {
        return true;
    }
}