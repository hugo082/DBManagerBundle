<?php

namespace DB\ManagerBundle\Exception;

class NotFoundException extends \Exception implements ExceptionInterface
{
    private $statusCode = 324;
    private $headers;
    private $title;

    private $devTitle = NULL;
    private $devMessage = NULL;

    public function __construct($name)
    {
        $this->headers = array();
        $this->title = "Entity not found";
        $message = "You would manage the entity named <b>". $name . "</b> but is not present on our database.";

        $this->devMessage = "Impossible to load entity with name : <b>". $name . "</b>. <br>
        It's possible that you don't have added this entity in DBM config or doesn't exist in your database.";

        parent::__construct($message, 0, null);
    }

    public function getDevMessage()
    {
        if ($this->devMessage != NULL)
            return $this->devMessage;
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
        if ($this->devTitle != NULL)
            return $this->devTitle;
        return $this->title;
    }
}
