<?php
/**
 * Orders
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Orders extends API
{
  /**
   * Return the number of held orders
   * @return integer
   */
  public function countHeld()
  {
    $held = $this->db->query();
    $held->select('1', 'bf_orders')
         ->where('processed = 0 AND held = 1')
         ->execute();
    
    return $held->count;
  }
  
  /**
   * Return the number of unprocessed orders
   * @return integer
   */
  public function countUnprocessed()
  {
    $unprocessed = $this->db->query();
    $unprocessed->select('1', 'bf_orders')
                ->where('processed = 0 AND held = 0')
                ->execute();
    
    return $unprocessed->count;
  }
}
?>