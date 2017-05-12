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


class LinkMetaData
{
    private $action;

    /**
     * @var null|array
     */
    private $container = null;

    public function __construct(array $data)
    {
        $this->action = $data["action"];
        $this->container = $data["container"];
    }

    public function getContainer() {
        if ($this->container == null)
            return array();
        return $this->container;
    }
}