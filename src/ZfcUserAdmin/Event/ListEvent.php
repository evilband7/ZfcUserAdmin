<?php
namespace ZfcUserAdmin\Event;

use Zend\EventManager\Event;
use ZfcUserAdmin\Collection\ColumnCollection;
use ZfcUserAdmin\Collection\ButtonCollection;
use Doctrine\ORM\QueryBuilder;

class ListEvent extends  Event
{
    static $IDENTIFIERS = array(ListEvent::class);
    static $EVENT_NAME = 'ZfcUserAdmin.userList';
    
    /**
     * 
     * @return ColumnCollection
     */
    public function getColumnCollection(){
        return $this->getParam('columnCollection');
    }
    
    /**
     *
     * @return ButtonCollection
     */
    public function getButtonCollection(){
        return $this->getParam('buttonCollection');
    }
    
    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(){
        return $this->getParam('queryBuilder');
    }
}

?>