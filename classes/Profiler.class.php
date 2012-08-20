<?php
/** 
 * Profiler Class
 * Provides lapped/phased time monitoring of processes.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Profiler extends Base
{
  /**
   * A collection of processes that have been monitored.
   * @var array
   */
  private $processes = array();
  
  /**
   * A profile of memory usage (if logged)
   * @var array
   */
  private $memory = array();
  
  /**
   * Add a process and start recording it.
   * @param string $name The name of the process.
   * @param string $description A description of the process.
   * @return boolean
   */
  public function start($name)
  {
    $this->processes[$name] = array(
      'startTime' => microtime(true),
      'stopTime' => -1.00
    );
    
    return true;
  }

  /**
   * Stop a process
   * @param string $name The name of the process to stop.
   * @return boolean
   */
  public function stop($name)
  {
    if(array_key_exists($name, $this->processes))
    {
      $this->processes[$name]['stopTime'] = microtime(true);
    }
    
    return true;
  }

  /**
   * Get the current time elapsed by a process
   * @param string $name The name of the process
   * @return float
   */
  public function getElapsed($name)
  {
    if(array_key_exists($name, $this->processes) && 
       $this->processes[$name]['stopTime'] != -1.00)
    {
      return $this->processes[$name]['stopTime'] - $this->processes[$name]['startTime'];
    }
    else
    {
      return 0.00;
    }
  }
  
  /**
   * Create a profile log
   * Optionally write it to disk.
   * @param string $path Optionally The full path to the location of the file if desired.
   * @param boolean $html Optionally write HTML as opposed to text only.
   * @return string
   */
  public function createLog($path = '', $html = false)
  {
    // Generate a log
    $log =  "\n";
    $log .= 'Profiling results on ' . Tools::longDate() . "\n" . ($html ? '<br />' : '');
    $log .= 'Generating URL: ' . urldecode($_SERVER['REQUEST_URI']) . "\n\n" . ($html ? '<br /><br />' : '');
    
    // Calculate total
    $totalTime = 0.00;
    foreach($this->processes as $name => $process)
    {
      $totalTime += $this->getElapsed($name);
    }
    
    // Print report
    foreach($this->processes as $name => $process)
    {
      $log .= $name . "\t\t" . number_format($this->getElapsed($name), 3) . ' sec' . "\n" . ($html ? '<br />' : '');
    }
    
    // Check if there is a memory log
    if(!empty($this->memory))
    {
      // Memory log is present
      $log .= print_r($this->memory, true);
    }
    
    // Write log to file
    if($path)
    {
      file_put_contents($path, $log);
    }
    
    return $log;
  }
  
  /**
   * Profile memory
   * N.B. Use of this method is not advisable in a production environment.
   * @return boolean
   */
  public function profileMemory()
  {
    // Register a timed/tick method
    register_tick_function(array(& $this, 'recordMemoryUsage'));
    
    return true;
  }
  
  /**
   * Record the usage of memory right now
   * Usually called as a registered tick method.
   * @return boolean
   */
  public function recordMemoryUsage()
  {
    $this->memory[(string)microtime(true)] = memory_get_usage();
    
    return true;
  }

}
?>