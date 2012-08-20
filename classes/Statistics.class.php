<?php
/** 
 * Statistics Class
 * Provides statistical information about the site usage.
 *
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Statistics extends Base
{  
  /**
   * Increment the statistics value by 1 or an optionally specified value
   * @param string $domain The statistics domain to operate on.
   * @param float $increment Optionally a value to use instead of 1.
   * @return boolean
   */
  public function increment($domain, $increment = 1.00)
  {
    // Safe increment value
    $safeIncrement = $this->db->san($increment);
    
    // Perform DB changes in a seperate Query instance
    // to avoid losing queries in progress by external code.
    $statChanges = $this->db->query();
  
    // Perform the increment
    $statChanges->text('UPDATE bf_statistics SET value = value + ' . $safeIncrement)
                ->where('name = \'{1}\'', $domain)
                ->limit(1)
                ->execute();
                
    // Finished
    $statChanges = null;
    
    return true;
  }
}
?>