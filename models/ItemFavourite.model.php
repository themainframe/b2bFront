<?php
/** 
 * Model: Item Favouritising
 * Adds an item to the current users favourites.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class ItemFavourite extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
  
    // Get the Item ID
    $itemID = $this->parent->inInteger('id');
    
    // Create the favourite
    try
    {
      $this->parent->db->insert('bf_user_favourites', array(
                                  'user_id' => $this->parent->security->UID,
                                  'item_id' => $itemID
                               ))
                       ->execute();
                       
      
    }
    catch(Exception $exception)
    {
      // Ignore duplicate favourite
    }
    
    // Redirect back to the item
    $this->parent->go('./?option=item&message=1&id=' . $itemID);
  
    return true;
  }
}  
?>