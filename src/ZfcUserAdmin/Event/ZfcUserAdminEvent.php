<?php
namespace ZfcUserAdmin\Event;

use Zend\EventManager\Event;
use Zend\View\Renderer\PhpRenderer;
use ZfcUser\Entity\UserInterface;

class ZfcUserAdminEvent extends  Event
{
    static $IDENTIFIERS = array('ZfcUserAdmin');
    static $EVENT_RENDER_BUTTON = 'ZfcUserAdmin.renderButton';
    
    /**
     * 
     * @return UserInterface
     */
    public function getUser(){
        return $this->getParam('user');
    }
    
    /**
     * 
     * @return PhpRenderer
     */
    public function getPhpRenderer(){
        return $this->getParam('phpRenderer');
    }
}

?>