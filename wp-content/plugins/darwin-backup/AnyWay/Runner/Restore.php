<?php

class AnyWay_Runner_Restore extends AnyWay_Runner_Base
{
    protected function _getStateProvider($sid = null, $waitForLock = null)
    {
        return new AnyWay_StateProvider_FileSystem($sid, $waitForLock);
    }

    public function init($settings = array())
    {
        $plan = AnyWay_Planner_Restore::buildInitialPlan($settings);

        $this->getStateProvider()->setState(array(
            'db_host' => $settings['db_host'],
            'db_name' => $settings['db_name'],
            'db_user' => $settings['db_user'],
            'db_password' => $settings['db_password'],
            'root' => $settings['root'],
            'queueManager' => array(
                'queue' => $plan
            )
        ));
        return $this->getStateProvider()->getStateId();
    }
}
