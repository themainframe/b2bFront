<?php
/** 
 * Interface: IModel
 * Generalises MVC Model classes
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
interface IModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute();
}  
?>