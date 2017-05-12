<?php

/*
 * This file is part of the FQTDBCoreManagerBundle package.
 *
 * (c) FOUQUET <https://github.com/hugo082/DBManagerBundle>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hugo Fouquet <hugo.fouquet@epita.fr>
 */

namespace DB\ManagerBundle\Core;

use FQT\DBCoreManagerBundle\Core\Action;
use FQT\DBCoreManagerBundle\Core\Data;
use FQT\DBCoreManagerBundle\Core\EntityInfo;


class Link
{
    /**
     * @var EntityInfo
     */
    private $entityInfo;

    /**
     * @var Action
     */
    private $action;

    public function __construct(Action $action, EntityInfo $entityInfo)
    {
        $this->action = $action;
        $this->entityInfo = $entityInfo;
    }

    public function isDisplayable() {
        return true;
    }

    public function getID() {
        return "link_" . $this->action->id;
    }

    public function getName(){
        return $this->action->fullName;
    }

    public function getParameter() {
        return array(
            "name" => $this->entityInfo->name,
            "actionID" => $this->action->id,
            "id" => null
        );
    }
}