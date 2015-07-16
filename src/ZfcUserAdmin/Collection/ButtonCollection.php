<?php
namespace ZfcUserAdmin\Collection;
use ZfcDatagrid\Column\Action\Button;
use PhpCommonUtil\Collection\PriorityFifoDataCollection;

class ButtonCollection extends PriorityFifoDataCollection
{
    static $ID_EDIT_BTN = 'editBtn';
    static $ID_DELETE_BTN = 'deleteBtn';
    
    private $buttons = array();
    
    /**
     * @param integer $buttonId
     * @return Button|NULL
     */
    public function get($id){
        return parent::get($id);
    }
    
    public function put($id, $button, $priority = 1){
        parent::put($id, $button, $priority);  
        return $this;
    }
    
}

?>