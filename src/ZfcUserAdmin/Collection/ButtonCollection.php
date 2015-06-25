<?php
namespace ZfcUserAdmin\Collection;
use ZfcDatagrid\Column\Action\Button;

class ButtonCollection extends AbstractPriorityDataCollection
{
    static $ID_EDIT_BTN = 'editBtn';
    static $ID_DELETE_BTN = 'deleteBtn';
    
    private $buttons = array();
    
    /**
     * @param integer $buttonId
     * @return Button|NULL
     */
    public function get($id){
        return parent::getData($id);
    }
    
    public function put($id, Button $button, $priority = 1){
        parent::put($id, $button, $priority);  
        return $this;
    }
    
}

?>