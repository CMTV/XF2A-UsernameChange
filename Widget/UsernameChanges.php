<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\Widget;

use CMTV\UsernameChange\XF\Entity\User;
use XF\Mvc\Entity\Finder;
use XF\Widget\AbstractWidget;

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

        if (!$visitor->canViewUsernameChanges())
        {
            return '';
        }

        $options = $this->options;
        $limit = $options['limit'];

        /** @var Finder $usernameChangeFinder */
        $usernameChangeFinder = $this->finder('CMTV\UsernameChange:UsernameChange');
        $usernameChangeFinder
            ->with('User')
            ->order('change_date', 'DESC')
            ->limit(max($limit * 2, 10));

        /**
         * @var int $changeId
         * @var \CMTV\UsernameChange\Entity\UsernameChange $usernameChange
         */
        foreach ($usernameChanges = $usernameChangeFinder->fetch() as $changeId => $usernameChange)
        {
            if (!$usernameChange->canView() || $visitor->isIgnoring($usernameChange->user_id))
            {
                unset($usernameChanges[$changeId]);
            }
        }

        $usernameChanges = $usernameChanges->slice(0, $limit, true);

        $viewParams = [
            'title' => $this->getTitle() ?: \XF::phrase('CMTV_UC_widget_default_title'),
            'usernameChanges' => $usernameChanges,
            'style' => $options['style']
        ];

        return $this->renderer('CMTV_UC_widget_new_username_changes', $viewParams);
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