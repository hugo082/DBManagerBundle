<?php

namespace DB\ManagerBundle\Exception;

class NotAllowedException extends \Exception implements ExceptionInterface
{
    private $statusCode = 323;
    private $headers;
    private $title;

    public function __construct($einfo)
    {
        $this->headers = array();
        $this->title = "This action is not allowed";
        $message = "You can't execute this action on " . $einfo['name'];
        parent::__construct($message, 0, null);
    }
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    public function getHeaders()
    {
        return $this->headers;
    }
    public function getTitle()
    {
        return $this->title;
    }
}
