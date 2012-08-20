<?php
/** 
 * API Class
 * Provides a basic structure for API Access
 * Should be extended by Core APIs
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class API extends Base
{
  /**
   * A ref to the parent BFClass object.
   * @var BFClass*
   */
  public $parent = null;
  
  /**
   * A shortcut to the Database object
   * Should be defined when instanciated by the class load wrapper.
   * @var Database*
   */
  public $db = null;
}
?>