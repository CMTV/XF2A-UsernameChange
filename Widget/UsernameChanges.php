<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\Widget;

use CMTV\UsernameChange\Entity\UsernameChange;
use CMTV\UsernameChange\XF\Entity\User;
use XF\Widget\AbstractWidget;

use CMTV\UsernameChange\Constants as C;

class UsernameChanges extends AbstractWidget
{
    protected $defaultOptions = [
        'limit' => 5,
        'style' => 'full'
    ];

    public function render()
    {
        /** @var User $visitor */
        $visitor = \XF::visitor();

        if (!$visitor->canViewUsernameChangesHistory())
        {
            return '';
        }

        $options = $this->options;
        $limit = $options['limit'];

        $usernameChanges = $this->finder(C::mvc('UsernameChange'))
            ->with('User')
            ->order('change_date', 'DESC')
            ->limit(max($limit * 2, 10))
            ->fetch();

        /** @var UsernameChange $usernameChange */
        foreach ($usernameChanges as $changeId => $usernameChange)
        {
            if (!$usernameChange->canView() || $visitor->isIgnoring($usernameChange->user_id))
            {
                unset($usernameChanges[$changeId]);
            }
        }

        $usernameChanges = $usernameChanges->slice(0, $limit, true);

        $viewParams = [
            'title' => $this->getTitle() ?: \XF::phrase(C::phrase('widget_default_title')),
            'usernameChanges' => $usernameChanges,
            'style' => $options['style']
        ];

        return $this->renderer(C::template('widget_new_username_changes'), $viewParams);
    }

    public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
    {
        $options = $request->filter([
            'limit' => 'uint',
            'style' => 'str'
        ]);

        if ($options['limit'] < 1)
        {
            $options['limit'] = 1;
        }

        return true;
    }
}