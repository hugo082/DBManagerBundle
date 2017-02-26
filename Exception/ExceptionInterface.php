<?php

/*
 * This file is part of the DBManagerBundle package.
 *
 * (c) FOUQUET <https://github.com/hugo082/DBManagerBundle>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hugo Fouquet <hugo.fouquet@epita.fr>
 */

namespace DB\ManagerBundle\Exception;

interface ExceptionInterface
{
    public function getStatusCode(string $env);
    public function getMessage();
    public function getDevMessage();
    public function getHeaders(string $env);
    public function getTitle(string $env);
}
