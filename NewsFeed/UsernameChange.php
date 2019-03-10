<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\NewsFeed;

use XF\Entity\NewsFeed;
use XF\Mvc\Entity\Entity;
use XF\NewsFeed\AbstractHandler;

class UsernameChange extends AbstractHandler
{
    public function getTemplateData($action, NewsFeed $newsFeed, Entity $content = null)
    {
        return parent::getTemplateData($action, $newsFeed, $content);
    }
}