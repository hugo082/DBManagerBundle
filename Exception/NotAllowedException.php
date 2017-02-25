<?php

namespace DB\ManagerBundle\Exception;

class NotAllowedException extends \Exception implements ExceptionInterface
{
    private $statusCode = 323;
    private $headers;
    private $title;

    private $devTitle = NULL;
    private $devMessage = NULL;

    private $eInfo;

    public function __construct($eInfo)
    {
        $this->eInfo = $eInfo;
        $this->headers = array();
        $this->title = "Action not allowed";
        $message = "You can't execute this action on " . $eInfo['name'];

        $this->devTitle = "Impossible to execute this action on " . $eInfo['fullName'];
        $this->devMessage = "The roles of the current user may not be 
        sufficient or that the action is not allowed on this entity. <br>
        For more information, look the dumps above.<br>
        If all boolean on dumps are incoherent with this exception, check the result of actionMethod.";

        parent::__construct($message, 0, null);
    }

    public function getDevMessage()
    {
        if ($this->devMessage != NULL) {
            var_dump($this->eInfo['access']);
            var_dump($this->eInfo['access_details']);
            var_dump($this->eInfo['permissions']);
            return $this->devMessage;
        }
        return $this->message;
    }

    public function getStatusCode(string $env)
    {
        return $this->statusCode;
    }

    public function getHeaders(string $env)
    {
        return $this->headers;
    }

    public function getTitle(string $env)
    {
        if ($env != 'prod' and $this->devTitle != NULL)
            return $this->devTitle;
        return $this->title;
    }
}
