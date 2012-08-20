<?php
/** 
 * Query Class
 * Handles an individual MySQL transaction
 *
 * Instanciated by the "Database" factory class only.
 *
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @factory Database
 * @version 1.0
 */
class Query extends Database
{
  /**
   * Acquire MySQL connection from factory class
   * @param resource $mysql The MySQL connection resource.
   * @param BFClass* $parent A referance to the parent object.
   * @return Query
   */
  public function __construct($mysql, $parent)
  {
    $this->mysql = $mysql;
    
    // Add parent too
    $this->parent =  $parent;
  }
}
?>