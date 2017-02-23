<?php

namespace DB\ManagerBundle\Checker;

use Doctrine\ORM\EntityManager as ORM;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use DB\ManagerBundle\DependencyInjection\Configuration;

use DB\ManagerBundle\Exception\NotFoundException;
use DB\ManagerBundle\Exception\NotAllowedException;

class EntityManager
{
    private $em;
    private $context;
    private $token;
    //private $subrepo;

    public function __construct(ORM $em, AuthorizationChecker $context, TokenStorage $token)
    {
        $this->em = $em;
        $this->context = $context;
        $this->token = $token;
        //$this->subrepo = $em->getRepository('AppBundle:SubBook');
    }

    public function getEntity($settings, $array, $name, $action) {
        if (!isset($array[$name]))
            throw new NotFoundException($name);

        $eInfo = $array[$name];
        if (!$eInfo['permissions'][$action])
            throw new NotAllowedException($eInfo);
        if (!$this->entityAccess($eInfo, $action))
            throw new NotAllowedException($eInfo);

        $eInfo['displayElements'] = $this->getDisplayPermissions($settings, $eInfo, $action);
        return $eInfo;
    }

    public function accessFilter($entities) {
        foreach ($entities as $key => $e) {
            if (!$this->entityAccess($e))
                unset($entities[$key]);
        }
        return $entities;
    }

    /**
     * Compute current displayable elements for entity / user / action
     * WARNING : don't check $eInfo['permissions'][$action]
     * @param array $settings
     * @param array $entity
     * @param string action
     * @return array
     */
    private function getDisplayPermissions(array $settings, array $entity, string $action) {
        $formAction = ($action == Configuration::PERM_LIST) ? Configuration::PERM_ADD : $action;
        return array(
            Configuration::DISP_ELEM_FORM => array(
                'full' => $settings[$action][Configuration::DISP_ELEM_FORM] && $this->entityAccess($entity, $formAction),
                Configuration::DISP_ELEM_ADDLINK => $entity['permissions'][$formAction] && $formAction != $action && $this->entityAccess($entity, $formAction)
            ),
            Configuration::DISP_ELEM_LIST => array(
                'full' => $settings[$action][Configuration::DISP_ELEM_LIST] and $this->entityAccess($entity, Configuration::PERM_LIST),
                Configuration::DISP_ELEM_EDITLINK => $entity['permissions'][Configuration::PERM_EDIT] and $this->entityAccess($entity, Configuration::PERM_EDIT),
                Configuration::DISP_ELEM_REMOVELINK => $entity['permissions'][Configuration::PERM_REMOVE] and $this->entityAccess($entity, Configuration::PERM_REMOVE)
            )
        );
    }

    /**
     * Check if current user have access to $entity
     * @param $entity
     * @return bool
     */
    private function entityAccess($entity, $action = NULL) {
        if ($entity['access_details'] != NULL) {
            return $this->grantedRoles($entity['access_details'][$action]);
        } elseif ($entity['access'] != NULL)
            return $this->grantedRoles($entity['access']);
        return true;
    }

    /**
     * Check if current user is granted at least one of $roles
     * @param array $roles
     * @return bool
     */
    private function grantedRoles(array $roles) {
        foreach ($roles as $role) {
            if ($this->context->isGranted($role))
                return true;
        }
        return false;
    }

    /**
     * Return the current user
     * @return User
     */
    private function getUser()
    {
        return $this->token->getToken()->getUser();
    }
}
