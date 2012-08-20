<?php
/** 
 * Memcache Dummy Class
 * Provides an alternative to a failed/missing memcache extension
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class MemcacheDummy extends Base
{
  /**
   * Magic Getter/Setter generation proxy.
   * Avoids failed memcache calls.
   * @param string $name The name of the called method.
   * @param array $arguments Any arguments passed to the called method.
   * @return mixed
   */
  public function __call($name, $arguments)
  {
    return null;
  }
}
?>