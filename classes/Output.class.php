<?php
/** 
 * Output Class
 * Collects output and provides an entry point for cache control.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Output extends Base
{
  /**
   * The output buffer
   * @var string
   */
  private $buffer = "";
  
  /**
   * Get the current buffer size in bytes.
   * @return integer
   */
  public function getSize()
  {
    return strlen($this->buffer);
  }
  
  /**
   * Add to the output buffer
   * @param string $text The text to add to the buffer.
   * @return boolean
   */
  public function buffer($text)
  {
    // Add to the buffer
    $this->buffer .= $text;
    
    return true;
  }
  
  /**
   * Send output to the browser and clear the output buffer
   * @return boolean
   */
  public function flush()
  {
    // Write output
    print $this->buffer;
    
    // Clear the buffer
    $this->buffer = "";
    
    return true;
  }
}

?>