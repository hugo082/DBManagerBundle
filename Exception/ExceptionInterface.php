<?php

namespace DB\ManagerBundle\Exception;

interface ExceptionInterface
{
    public function getStatusCode(string $env);
    public function getMessage();
    public function getDevMessage();
    public function getHeaders(string $env);
    public function getTitle(string $env);
}
