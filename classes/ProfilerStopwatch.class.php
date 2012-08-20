<?php
/** 
 * Profiler Stopwatch Class
 * Provides time monitoring facilities.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class ProfilerStopwatch extends Base
{
  /**
   * The time in seconds that the clock was running
   * @var float
   */
  private $elapsed = 0.0;
  
  /**
   * The time the clock was started
   * @var float
   */
  private $startTime = 0.0;
  
  /**
   * The time the clock was stopped
   * @var float
   */
  private $stopTime = 0.0;
  
  /**
   * Start the clock
   * @return boolean
   */
  public function start()
  {
    $this->startTime = microtime(true);
    
    return true;
  }

  /**
   * Stop the clock
   * @return boolean
   */
  public function stop()
  {
    $this->stopTime = microtime(true);
    
    return true;
  }
  
  /**
   * Reset the clock
   * @return boolean
   */
  public function reset()
  {
    $this->startTime = 0.0;
    $this->stopTime = 0.0;
    
    return true;
  }
  
  /**
   * Get the current value of the clock in seconds
   * @return float
   */
  public function getElapsed()
  {
    return $this->stopTime - $this->startTime;
  }

}
?>