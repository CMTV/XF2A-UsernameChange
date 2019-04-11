<?php
/**
 * Username Change xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\UsernameChange\XF\Admin\Controller;

use XF\Mvc\Reply\Error;

use CMTV\UsernameChange\Constants as C;

class Permission extends XFCP_Permission
{
    public function actionAnalyze()
    {
        $view = parent::actionAnalyze();

        // Doing nothing if error happened
        if ($view instanceof Error) {
            return $view;
        }

        // Ensure there is an analysis array
        if ($analysis = $view->getParam('analysis'))
        {
            // This doesn't work if the analysis is on a node. Doesn't make sense either.
            if($view->getParam('contentType')) {
                return $view;
            }

            // Getting an array of values for different groups (+ user value for specific users)
            $intermediates = $analysis[C::ADDON_ID_SHORT]['changeFrequency']['intermediates'];

            if($intermediates === null) {
                return $view;
            }

            // Getting the first value
            $lowestValue = array_shift($intermediates)->value;

            // Iterating values
            foreach ($intermediates as $intermediate)
            {
                // A zero (0) value is equivalent to "No" and should not be counted (use "Unlimited" option instead)!
                if ($intermediate->value !== 0)
                {
                    // If current value is lower than $lowestValue, use it
                    $lowestValue = min($lowestValue, $intermediate->value);
                }
            }

            // Setting final value to $lowestValue
            $analysis[C::ADDON_ID_SHORT]['changeFrequency']['final'] = $lowestValue;

            $view->setParam('analysis', $analysis);
        }

        return $view;
    }
}