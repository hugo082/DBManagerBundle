<?php

namespace DB\ManagerBundle\Checker;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use DB\ManagerBundle\DependencyInjection\Configuration;

use DB\ManagerBundle\Exception\NotFoundException;
use DB\ManagerBundle\Exception\NotAllowedException;

class EntityManager
{
    private $context;
    private $token;
    private $settings;
    private $entities;

    public function __construct(AuthorizationChecker $context, TokenStorage $token, array $settings, array $entities)
    {
        $this->context = $context;
        $this->token = $token;
        $this->settings = $settings;
        $this->entities = $entities;
    }

    public function getEntity(string $name, string $action) {
        if (!isset($this->entities[$name]))
            throw new NotFoundException($name);

        $eInfo = $this->entities[$name];
        if (!$eInfo['permissions'][$action])
            throw new NotAllowedException($eInfo);
        if (!$this->entityAccess($eInfo, $action))
            throw new NotAllowedException($eInfo);

        $eInfo['displayElements'] = $this->getDisplayPermissions($eInfo, $action);
        return $eInfo;
    }

    /**
     * Return all entities where current user can execute action or access information
     * @return array
     */
    public function getEntities() {
        $tmp = $this->entities;
        foreach ($tmp as $key => $e) {
            if (!$this->entityAccess($e))
                unset($tmp[$key]);
        }
        return $tmp;
    }

    /**
     * Compute current displayable elements for entity / user / action
     * WARNING : don't check $eInfo['permissions'][$action]
     * @param array $entity
     * @param string $action
     * @return array
     */
    private function getDisplayPermissions(array $entity, string $action) {
        $formAction = ($action == Configuration::PERM_LIST) ? Configuration::PERM_ADD : $action;
        return array(
            Configuration::DISP_ELEM_FORM => array(
                'full' => $full = $this->settings[$action][Configuration::DISP_ELEM_FORM] && $this->entityAccess($entity, $formAction),
                Configuration::DISP_ELEM_ADDLINK => !$full and $entity['permissions'][$formAction] && $formAction != $action && $this->entityAccess($entity, $formAction)
            ),
            Configuration::DISP_ELEM_LIST => array(
                'full' => $full = $this->settings[$action][Configuration::DISP_ELEM_LIST] and $this->entityAccess($entity, Configuration::PERM_LIST),
                Configuration::DISP_ELEM_EDITLINK => $full and $entity['permissions'][Configuration::PERM_EDIT] and $this->entityAccess($entity, Configuration::PERM_EDIT),
                Configuration::DISP_ELEM_REMOVELINK => $full and $entity['permissions'][Configuration::PERM_REMOVE] and $this->entityAccess($entity, Configuration::PERM_REMOVE)
            )
        );
    }

    /**
     * Check if current user have access to $entity
     * @param $entity
     * @param $action
     * @return bool
     */
    private function entityAccess($entity, $action = NULL) {
        if ($entity['access_details'] != NULL) {
            if ($action != NULL)
                return $this->grantedRoles($entity['access_details'][$action]);
            foreach (Configuration::PERMISSIONS as $perm) {
                if ($this->grantedRoles($entity['access_details'][$perm]))
                    return true;
            }
            return false;
        } elseif ($entity['access'] != NULL)
            return $this->grantedRoles($entity['access']);
        foreach (Configuration::PERMISSIONS as $perm) {
            if ($entity['permissions'][$perm])
                return true;
        }
        return false;
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
     * Get settings
     * @return array
     */
    public function getSettings(){
        return $this->settings;
    }
}
