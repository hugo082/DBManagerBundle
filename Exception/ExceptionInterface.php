<?php

namespace DB\ManagerBundle\Exception;

interface ExceptionInterface
{
    public function getStatusCode();
    public function getMessage();
    public function getHeaders();
    public function getTitle();
}
