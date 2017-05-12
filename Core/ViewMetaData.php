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
use DB\ManagerBundle\DependencyInjection\Configuration as Conf;


class ViewMetaData
{
    private $action;
    /**
     * @var null|string
     */
    private $defaultView = null;

    /**
     * @var null|string
     */
    private $customView = null;

    /**
     * @var null|string
     */
    private $view = null;

    /**
     * @var null|array
     */
    private $container = null;

    public function __construct(array $data)
    {
        $this->action = $data["action"];
        $this->container = $data["container"];
        $this->defaultView = $data["default_view"];
        $this->customView = $data["custom_view"];
    }

    private function computeView() {
        if ($this->defaultView != null) {
            if (!key_exists($this->defaultView, Conf::DEF_VIEWS))
                throw new \Exception("No default view for key " . $this->defaultView);
            return Conf::DEF_VIEWS[$this->defaultView];
        }
        if ($this->customView  != null && is_string($this->customView))
            return $this->customView;
        throw new \Exception("You must specify a view for action " . $this->action);
    }

    public function getView(bool $force = false) {
        if ($this->view == null || $force)
            $this->view = $this->computeView();
        return $this->view;
    }

    public function getContainer() {
        if ($this->container == null)
            return array();
        return $this->container;
    }

    public static function createWithDefaultView(string $actionID, string $defView) {
        return new ViewMetaData(array(
            "action" => $actionID,
            "custom_view" => null,
            "default_view" => $defView,
            "container" => null
        ));
    }
}