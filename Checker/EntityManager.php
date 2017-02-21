<?php

namespace DB\ManagerBundle\Checker;

use Doctrine\ORM\EntityManager as ORM;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

use DB\ManagerBundle\Exception\NotFoundException;
use DB\ManagerBundle\Exception\NotAllowedException;

class EntityManager
{
    private $em;
    private $context;
    private $token;
    private $subrepo;

    public function __construct(ORM $em, AuthorizationChecker $context, TokenStorage $token)
    {
        $this->em = $em;
        $this->context = $context;
        $this->token = $token;
        //$this->subrepo = $em->getRepository('AppBundle:SubBook');
    }

    public function getEntity($array, $name) {
        if (!isset($array[$name]))
            throw new NotFoundException($name);
        return $array[$name];
    }

    public function edit($einfo) {
        if (!$einfo['permission']['edit'])
            throw new NotAllowedException($einfo);
    }

    public function remove($einfo) {
        if (!$einfo['permission']['remove'])
            throw new NotAllowedException($einfo);
    }

    private function getUser()
    {
        return $this->token->getToken()->getUser();
    }
}
