<?php

class AnyWay_Tasks_EstimateBootstrap extends AnyWay_Tasks_Bootstrap
{
    public $id = 'estimate-bootstrap';

    public function __construct($options = array())
    {
        // do nothing
    }

    public function getState()
    {
        return array();
    }

    public function runPartial($deadline, $hardDeadline)
    {
        foreach ($this->classes as $name) {
            if (class_exists($name) || interface_exists($name)) {
                $class = new ReflectionClass($name);
                if ($filename = $class->getFileName()) {
                    $start = $class->getStartLine() - 2; // getStartLine() seems to start after the {, we want to include the signature
                    $end = $class->getEndLine();
                    $num = $end - $start;
                    // not perfect; if the class starts or ends on the same line as something else, this will be incorrect
                    $source = implode('', array_slice(file($filename), $start, $num));
                    $this->emit("estimate", strlen($source . "\n"));
                } else {
                    throw new Exception("File not found for class $name");
                }
            } else {
                throw new Exception("Class $name not found");
            }
        }
        //$this->emit('estimate-bootstrap:complete');
    }
}
