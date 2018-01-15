<?php

interface AnyWay_Interface_IStateProvider
{

    public function __construct($stateId = null, $waitForLock = false);

    public function setState($state);

    public function getState();

    public function deleteState();

    public function getStateId();
}