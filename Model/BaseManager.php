<?php

namespace DB\ManagerBundle\Model;

use DB\ManagerBundle\Model\ListingEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Storage agnostic manager full compatible object.
 *
 * @author Hugo Fouquet <hugo.fouquet@epita.fr>
 */
abstract class BaseManager implements ListingEntityInterface
{
    private $bloquedMethods = array('getVars', 'getProperties');

    /**
     * @return All properties values
     */
    public function getVars(){
        return $this->getProp(true);
    }

    /**
     * @return All properties names
     */
    public function getProperties(){
        return $this->getProp(false);
    }

    private function getProp(bool $execute) {
        $reflect = new \ReflectionClass($this);
        $methods = $reflect->getMethods(\ReflectionProperty::IS_PUBLIC);

        $tmp = array();
        foreach ($methods as $met) {
            $metName = $met->getName();
            if (0 === strpos($metName, 'get') && !in_array($metName, $this->bloquedMethods)) {
                if ($execute)
                    $tmp[] = $this->$metName();
                else
                    $tmp[] = str_replace('get', '', $metName);
            }
        }
        return $tmp;
    }
}

