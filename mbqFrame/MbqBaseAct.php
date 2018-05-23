<?php

defined('MBQ_IN_IT') or exit;

/**
 * action base class
 */
Abstract Class MbqBaseAct {
    
    public $data;   /* data need return.reference to MbqMain::$data */
    public $supportLevels;  /* the levels can be supported,default is level 3 */
    public $currLevel;  /* current supported level degree,default is level 3 */
    
    public function __construct() {
        $this->data = & MbqMain::$data;
        $this->supportLevels = array(3);
        $this->currLevel = 3;
    }
    function getInputParam($index, $default = NULL)
    {
        if(is_array(MbqMain::$input))
        {
            if(isset(MbqMain::$input[$index]))
            {
                return MbqMain::$input[$index];
            }
        }
        else
        {
            if(isset(MbqMain::$input->$index))
            {
                return MbqMain::$input->$index;
            }
        }
        return $default;
    }
    function getValue($obj, $index, $default = NULL)
    {
        if(is_array($obj))
        {
            if(isset($obj[$index]))
            {
                return $obj[$index];
            }
        }
        else if(is_object($obj))
        {
            if(isset($obj->$index))
            {
                return $obj->$index;
            }
        }
        return $default;
    }
    function getAllInputParams()
    {
        if(is_array(MbqMain::$input))
        {
            return MbqMain::$input;
        }
        else
        {
            $inputResult = array();
            foreach(MbqMain::$input as $key => $value)
            {
                $inputResult[$key] = $value;
            }
            return $inputResult;
        }
    }
    /**
     * getInput() method
     *
     * The getInput() method examines values in the request in xmlrpc or json format and pass these to the main action method.
     */
    abstract protected function getInput();
    
    /**
     * Handle the main implementation of the action. This method can be overrided in MbqActCreateMessage
     */
    abstract protected function actionImplement($in);
  
}