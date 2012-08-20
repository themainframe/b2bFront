<?php
/** 
 * Business Object Model
 * Order Class
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class BOMOrder extends BOMObject
{
  /**
   * Load an order from the database with an ID
   * @param integer $orderID The ID to initialise this object with.
   * @param BFClass* $parent The parent object.
   * @return BOMOrder|boolean
   */
  public function __construct($orderID, & $parent)
  {
    // Superclass constructor
    parent::__construct($parent);
    
    // Initialisation possible?
    if(!$orderID)
    {
      return false;
    }
    
    // Load the order
    $orderRow = $parent->db->getRow('bf_orders', $orderID);
    
    // Missing
    if(!$orderRow)
    {
      $this->parent->log('BOM', 'No such order: ' . $orderID);
      return false;
    }
    
    // Set each value
    foreach($orderRow as $key => $value)
    {
      $this->{$key} = $value;
    }
  }
  
  /**
   * Load order notes
   * @return array
   */
  protected function loadNotes()
  {
    // Collect notes
    $notes = $this->parent->db->query();
    $notes->select('*', 'bf_order_notes') 
          ->where('`order_id` = \'{1}\' AND `staff_only` = \'0\'', $this->id)
          ->order('timestamp', 'asc') // Order of note addition
          ->execute();
          
    // Build arrays
    $orderNotes = array();
    
    while($note = $notes->next())
    {
      $orderNotes[$note->id] = array(
        'from_staff' => ($note->author_is_staff == 1),
        'date' => Tools::longDate($note->timestamp),
        'content' => $note->content,
        'author' => $note->author_name,
        'visible' => !($note->staff_only == 1)
      );
    }
    
    return $orderNotes;
  }
}
?>