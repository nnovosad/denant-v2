<?php

class AnyWay_Runner_Verify extends AnyWay_Runner_Base
{
    protected function _getStateProvider($sid = null, $waitForLock = null)
    {
        return new AnyWay_StateProvider_FileSystem($sid, $waitForLock);
    }

    public function init($settings = array())
    {
        $plan = AnyWay_Planner_Verify::buildInitialPlan($settings);

        $this->getStateProvider()->setState(array(
            'db_host' => $settings['db_host'],
            'db_name' => $settings['db_name'],
            'db_user' => $settings['db_user'],
            'db_password' => $settings['db_password'],
            'queueManager' => array(
                'queue' => $plan
            )
        ));
        return $this->getStateProvider()->getStateId();
    }
}
