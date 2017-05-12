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

use FQT\DBCoreManagerBundle\Core\EntityInfo;
use FQT\DBCoreManagerBundle\Core\Action;
use FQT\DBCoreManagerBundle\Core\Execution as CoreExecution;

use DB\ManagerBundle\Core\View;

class Execution
{
    /**
     * @var EntityInfo
     */
    public $entityInfo;

    /**
     * Array of View
     * @var array
     */
    public $views;

    /**
     * @var View
     */
    public $mainView;

    /**
     * @var null|array;
     */
    private $redirection = null;

    /**
     * @var null|array;
     */
    private $flash = null;

    public function __construct(CoreExecution $execution)
    {
        $this->entityInfo = $execution->entityInfo;
        $this->mainView = self::coreExecutionToView($execution);
        $this->views = array();
    }

    public function pushMainView(ViewMetaData $viewMeta) {
        $this->mainView->setView($viewMeta);
        $this->pushView($this->mainView);
    }

    public function pushView(View $view) {
        if (!$view->isViewable())
            throw new \Exception("Impossible to push view '" . $view->getID() . "'. It's not viewable.");
        $this->views[] = $view;
    }

    public function isDisplayable() {
        return !empty($this->views);
    }

    public static function coreExecutionToView(CoreExecution $execution) {
        return new View($execution->action, null, $execution->data);
    }

    public function computeData() {
        $this->flash = array();
        $this->redirection = null;
        /** @var View $view */
        foreach ($this->views as $view) {
            if ($view->getData() != null) {
                array_merge($this->flash, $view->getData()->getFlash());
                $red = $view->getData()->getRedirection($this->entityInfo->name);
                if ($red != null)
                    $this->redirection = $red;
            }
        }
    }

    public function getFlash(bool $force = false) {
        if ($this->flash == null || $force)
            $this->computeData();
        return $this->flash;
    }

    public function getRedirection(bool $force = false) {
        if ($this->redirection == null || $force)
            $this->computeData();
        return $this->redirection;
    }

    public function getContainer() {
        return $this->mainView->getViewMeta()->getContainer();
    }
}