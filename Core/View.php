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


class View
{
    /**
     * @var Action
     */
    private $action;

    /**
     * @var null|ViewMetaData
     */
    private $viewMeta = null;

    /**
     * @var Data
     */
    private $data;

    public function __construct(Action $action, ViewMetaData $viewMeta = null, Data $data) {
        $this->action = $action;
        $this->viewMeta = $viewMeta;
        $this->data = $data;
    }

    public function isViewable() {
        return $this->viewMeta != null && $this->viewMeta->getView() != null;
    }

    public function getID() {
        return "view_" . $this->action->id;
    }


    /**
     * @return null|ViewMetaData
     */
    public function getViewMeta() {
        return $this->viewMeta;
    }

    /**
     * @param ViewMetaData $viewMeta
     * @return $this
     */
    public function setView(ViewMetaData $viewMeta) {
        $this->viewMeta = $viewMeta;
        return $this;
    }

    /**
     * @return Action
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @return Data
     */
    public function getData() {
        return $this->data;
    }
}