<?php
/** 
 * Model: Unsubscribe
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Unsubscribe extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
    
    // Action?
    if($this->parent->inInteger('do') == 1)
    {
      // Do unsubscribe
      $unsubscribe = $this->parent->db->query();
      $unsubscribe->update('bf_users', array(
                            'include_in_bulk_mailings' => 0
                          ))
                  ->where('`email` = \'{1}\'', $this->parent->in('email'))
                  ->execute();
      
      if($unsubscribe->affected == 1)
      {
        $this->addValue('done', '1');
      }            
      
    }
      
    return true;
  }
}  
?>