<?php
/**
 * Username Change xF2 addon by CMTV
 * You can do whatever you want with this code
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Admin\Controller;

class Permission extends XFCP_Permission
{
    public function actionAnalyze()
    {
        $view = parent::actionAnalyze();

        if ($view instanceof \XF\Mvc\Reply\Error)
        {
            return $view;
        }

        if ($analysis = $view->getParam('analysis'))
        {
            // Finding the lowest value

            $intermediates = $analysis['CMTV_UC']['changeFrequency']['intermediates'];

            $days = array_shift($intermediates)->value;

            foreach ($intermediates as $intermediate)
            {
                if ($intermediate->value !== 0)
                {
                    $days = min($days, $intermediate->value);
                }
            }

            $analysis['CMTV_UC']['changeFrequency']['final'] = $days;

            $view->setParam('analysis', $analysis);
        }

        return $view;
    }
}