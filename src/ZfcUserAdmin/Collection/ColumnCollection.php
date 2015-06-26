<?php
namespace ZfcUserAdmin\Collection;

use ZfcDatagrid\Column\AbstractColumn;
use PhpCommonUtil\Collection\PriorityFifoDataCollection;

class ColumnCollection extends PriorityFifoDataCollection
{
    static $ID_COLUMN_ID = 'idColumn';
    static $ID_COLUMN_USERNAME = 'usernameColumn';
    static $ID_COLUMN_EMAIL = 'deleteBtn';
    
    private $buttons = array();
    
    /**
     * @param integer $buttonId
     * @return AbstractColumn
     */
    public function get($id){
        return parent::getData($id);
    }
    
    public function put($id, $button, $priority = 1){
        parent::put($id, $button, $priority);  
        return $this;
    }
    
}

?>