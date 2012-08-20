<?php
/**
 * b2bFront Core Tools 
 *
 * Contains some useful helper functions that are used in various places.
 * Causes no output, safe to include anywhere. 
 * 
 * Quick-Include:     include BF_ROOT . 'tools.php';
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 2.3
 * @author Damien Walsh
 */
final class Tools
{
  /**
   * Compare two values, if matched, return one argument, if not, return the other
   * The comparisson is performed as a PHP_EQUAL_TO (==)
   * @param string $valueA The first value
   * @param string $valueB The second value
   * @param string $returnIfMatched The value to return if $valueA and $valueB are equal
   * @param string $returnIfNotMatched optional The value to return if $valueA and $valueB are not equal
   * @return string
   */
  public static function conditional($valueA, $valueB, $returnIfMatched, $returnIfNotMatched = '')
  {
    if($valueA == $valueB)
    {
      return $returnIfMatched;
    }
    else
    {
      return $returnIfNotMatched;
    }
  }
  
  /**
   * Shortcut for file exists with BF_ROOT assumed
   * @param string $fileName The name of the file to touch for existence.
   * @return boolean True if the file exists, false if not.
   */
  public static function exists($fileName)
  {
    return (file_exists(BF_ROOT . '/' . $fileName) || file_exists($fileName));
  }
  
  /**
   * Remove path traversals from a string
   * @param string $input The string to make safe
   * @return string
   */
  public static function removePaths($input)
  {
    return preg_replace("/[^a-zA-Z0-9_\s]/", '', $input);
  }
  
  /**
   * Remove non path-valid characters
   * @param string $input The string to make safe
   * @return string
   */
  public static function removeNonPath($input)
  {
    return preg_replace("/[^a-zA-Z0-9_-]/", '', $input);
  }

  /**
   * Remove non-XSS-Safe characters
   * @param string $input The string to make safe
   * @return string
   */
  public static function safe($input)
  {
    return strip_tags(preg_replace("/[^a-zA-Z@0-9_\.\-\s]/", '', $input));
  }
  
  /**
   * Swap two variables
   * @param mixed $valueA The first value
   * @param mixed $valueB The second value
   * @return boolean
   */
  public static function swap(& $valueA, & $valueB)
  {
    $swapSpace = $valueA;
    $valueA = $valueB;
    $valueB = $swapSpace;
    
    return true;
  }
  
  /**
   * Generic index accessor method
   * @param array $array The array
   * @param integer $index The index to access
   * @param mixed $outOfBounds Optionally the value to return if the index is OOB.
   * @return mixed
   */
  public static function valueAt($array, $index, $outOfBounds = '')
  {
    if(array_key_exists($index, $array))
    {
      return $array[$index];
    }
    else
    {
      return $outOfBounds;
    }
  }
  
  /**
   * Array -> CSV
   * @param array $array The array
   * @param string $deliminator Optionally the deliminator, the default is a comma (',')
   * @return string
   */
  public static function CSV($array, $deliminator = ',')
  {
    return implode($deliminator, $array);
  }
  
  /**
   * CSV -> Array
   * @param array $string The string to convert to an array
   * @param string $deliminator Optionally the deliminator, the default is a comma (',')
   * @return string
   */
  public static function unCSV($string, $deliminator = ',')
  {
    return explode($deliminator, $string);
  }
  
  /**
   * Remove empty entries from $array and return the resulting array
   * @param array $array The array to filter
   * @return array
   */
  public static function removeEmptyEntries($array)
  {
    // array_filter() with no callback removes == FALSE values.
    return array_filter($array);
  }
  
  /**
   * Convert a boolean value to a space-padded checkbox check state.
   * @param boolean $value The boolean value
   * @return string
   */
  public static function booleanToCheckState($value)
  {
    return ($value ? ' checked="checked"' : '');
  }
  
  /**
   * Return all the files in a directory as an array of strings
   * Return an empty array on error
   * @param string $path The path of the directory to list.
   * @return array
   */
  public static function listDirectory($path)
  {
    // Try to open the directory handle
    $handle = @opendir($path);
    $fileArray = array();
    
    // Failed?
    if(!$handle)
    {
      // Try with the path joined to BF_ROOT
      $path = BF_ROOT . '/' . $path;
      $handle = @opendir($path);
    }

    // Finally failed?
    if(!$handle)
    {
      return false;
    }
    
    // Read into an array
    while(($file = readdir($handle)) !== false)
    {
      if(strlen($file) > 0 && $file[0] == '.')
      {
        continue;
      }
      
      $fileArray[] = $file;
    }
    
    // Alphabetical
    sort($fileArray);
    
    return $fileArray;
  }
  
  /**
   * Build a query string from an associative array
   * @param array $array An associative array.
   * @return string
   */
  public static function queryString($array)
  {
    // Build the query string
    $queryString = '';
    
    foreach($array as $key => $value)
    {
      $queryString .= urlencode($key) . '=' . urlencode($value) . '&';
    }
    
    // Remove trailing &
    $queryString = substr($queryString, 0, strlen($querystring) - 1);
    
    return $queryString;
  }
  
  /**
   * Form a URL to navigate to based on the current one
   * Accepts an associative array of values to add or substitute in the query string.
   * @param array $queryString Optionally values to add or substitute in the query string.
   * @return string
   */
  public static function getModifiedURL($queryString = array())
  {
    // Get the base path
    $urlParts = explode('?', $_SERVER["REQUEST_URI"]);
    $url = $urlParts[0] . '?';
    
    // Form an array to make changes to
    $GETValues = array();
    foreach($_GET as $key => $value)
    {
      if($value == '' || $key == '' || strpos($key, 'x_') === 0)
      {
        continue;
      }
      
      $GETValues[$key] = $value;
    }
    
    // Remove any empty additional values
    foreach($queryString as $key => $value)
    {
      if($key == '' || $value == '')
      {
        unset($queryString[$key]);
        unset($GETValues[$key]);
      }
    }
    
    // Make changes
    $mergedGETvalues = array_merge($GETValues, $queryString);
    
    // Create a query string
    $url .= Tools::queryString($mergedGETvalues);
    
    return $url;
  }
  
  /**
   * Generate hidden inputs to forward query string values.
   * Fields can be excluded from the effects of this method by prefixing their names with x_
   * @param array $queryString Optionally values to add or substitute in the query string.
   * @return string
   */
  public static function getQueryStringInputs($queryString = array())
  {
    // Get the base path
    $urlParts = explode('?', $_SERVER["REQUEST_URI"]);
    $url = $urlParts[0] . '?';
    
    // Form an array to make changes to
    $GETValues = array();
    foreach($_GET as $key => $value)
    {
      if($value == '' || $key == '' || strpos($key, 'x_') === 0)
      {
        continue;
      }
      
      $GETValues[$key] = $value;
    }
    
    // Remove any empty additional values
    foreach($queryString as $key => $value)
    {
      if($key == '' || $value == '')
      {
        unset($queryString[$key]);
        unset($GETValues[$key]);
      }
    }
    
    // Make changes
    $mergedGETvalues = array_merge($GETValues, $queryString);
    
    // Build HTML
    $output = "\n";
    foreach($mergedGETvalues as $key => $value)
    {
      $output .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
    }
    
    return $output;
  }
  
  /**
   * Replace tokens {1}..{2}... in a string with values
   * Method takes multiple arguments
   * @param string $string The string containing tokens to replace with values
   * @return string
   */
  public static function replaceTokens($string)
  {
    // Get arguments
    $input = func_get_args();
    
    for($index = 1; $index < count($input); $index ++)
    {
      $string = str_replace('{' . $index . '}', $input[$index], $string);
    }
  
    return $string;
  }
  
  /**
   * Replace tokens {1}..{2}... in a string with values
   * Similar to above, but accepts an additional parameter as an array
   * @param string $string The string containing tokens to replace with values
   * @param array $replacements An associative array of keys => values to replace.
   * @return string
   */
  public static function replaceTokensArray($string, $replacements)
  {
    foreach($replacements as $key => $value)
    {
      $string = str_replace('{' . $key . '}', $value, $string);
    }
  
    return $string;
  }
  
  /**
   * Format a UNIX timestamp as a long b2bFront date
   * @param integer $date Optionally the date to format. Now if not provided.
   * @return string
   */
  public static function longDate($date = -1)
  {
    if($date == -1)
    {
      // Use now as the date
      $date = time();
    }
    
    return date('F j, Y, g:i a', $date);
  }
  
  /**
   * Format a UNIX timestamp as a short b2bFront date
   * @param integer $date Optionally the date to format. Now if not provided.
   * @return string
   */
  public static function shortDate($date = -1)
  {
    if($date == -1)
    {
      // Use now as the date
      $date = time();
    }
    
    return date('M j, Y, g:i a', $date);
  }
  
  /**
   * Format a dirty price value to a clean one.
   * @param integer $value The dirty price value to format.
   * @return string
   */
  public static function cleanPrice($value)
  {
    $matches = array();
    preg_match('/([0-9]+[.]+[0-9]{2})/', $value, $matches);
    
    // Check result
    if(count($matches) < 2)
    {
      return false;
    }
    
    return $matches[1];
  }  

  /**
   * Integer to positive
   * @param integer $value The value to turn positive.
   * @return string
   */
  public static function intPositive($value)
  {
    if($value < 0)
    {
      $value = - $value;
    }
    
    return $value;
  }
  
  /**
   * Format a value as a price
   * Excludes signs / currency symbols
   * @param mixed $value The value to format
   * @return string
   */
  public static function price($value)
  {
    return number_format($value, 2, '.', '');
  }
  
  /**
   * Get the name and extension of a path as an associative array
   * Array: 'name' => string, 'ext' => string 
   * @param mixed $path The path to parse
   * @return array
   */
  public static function fileNameAndExt($path)
  {
    // Obtain the basename
    $basicName = basename($path);
    
    // Position of the .
    $dotPosition = strrpos($basicName, '.');
    
    // Does it exist?
    if($dotPosition === false)
    {
      return $basicName;
    }
    
    // Get parts
    $name = substr($basicName, 0, $dotPosition);
    $ext = substr($basicName, $dotPosition + 1);
    
    // Return as array
    return array('name' => $name, 'ext' => $ext);
  }
  
  /**
   * Clean a path, removing multiple forward-slashes
   * NB. Do not use on URLs - protocol delimiters ("//") will be removed!
   * @param string $path The path to clean.
   * @return string
   */
  public static function cleanPath($path)
  {
    do {
      
      $lastPath = $path;
      $path = str_replace('//', '/', $path);
    
    } while($lastPath != $path);
    
    return $path;
  }
  
  /**
   * Translate an image URL to a thumbnail URL
   * @param string $url The image URL.
   * @param string $suffix Optionally the thumbnail size suffix. Default 'thm'.
   * @return string
   */
  public static function getImageThumbnail($url, $suffix = 'thm')
  {
    // Get the position of the last .
    $dotPosition = strrpos($url, '.');
    
    // Get the parts
    $name = substr($url, 0, $dotPosition);
    $ext = substr($url, $dotPosition + 1);
    
    // Recompile the name with the thumbnail suffix
    return $name . '-' . $suffix . '.' . $ext;
  } 
  
  /**
   * Turn a `full` path into a relative path
   * Return false if the path is not a representation of a path within the software.
   * @param string $fullPath The full path
   * @return string|boolean
   */
  public static function relativePath($fullPath)
  {
    // Clean path first
    $relativePath = self::cleanPath($fullPath);
    
    // Replace root out
    $relativePath = str_replace(BF_ROOT, '/', $relativePath);
    
    // Check for path integrity
    if(self::exists($relativePath))
    {
      return self::cleanPath($relativePath);
    }
    
    return false;
  }
  
  /**
   * Remove the -PAR tag from a Parent Virtual SKU
   * @param string $virtualSKU The Parent Virtual SKU to remove -PAR from
   * @return string
   */
  public static function removeParentTag($virtualSKU)
  {
    // Check if -PAR is present
    if(substr($virtualSKU, strlen($virtualSKU) - 4, 4) == '-PAR')
    {
      return substr($virtualSKU, strlen($virtalSKU), -4);
    }
    
    // Otherwise, no change required
    return $virtualSKU;
  }
  
  /** 
   * Normalise a group of statistics to a given upper bound
   * @param array $statistics An array of integers representing the statistics
   * @param integer $maximum Optionally the upper bound to use, default 100
   * @return array
   */
  public static function upperBound($statistics, $maximum = 100)
  {
    // Find the highest value
    $highestValue = 0;
    foreach($statistics as $value)
    {
      if($value > $highestValue)
      {
        $highestValue = $value;
      }
    }
    
    // Scale this to the maximum
    $scaleFactor = $maximum / $highestValue;
    
    // Multiply all, generating a new scaled array
    $scaledArray = array();
    foreach($statistics as $value)
    {
      $scaledArray[] = intval($value * $scaleFactor);
    }
    
    return $scaledArray;    
  }
  
  /**
   * Pluralise a word
   * Returns 's' if the value is not 1.
   * @param integer $value The value to test.
   * @return string
   */
  public static function plural($value)
  {
    return ($value != 1 ? 's' : '');
  }
  
  /** 
   * Truncate a string if required
   * @param string $value The string to truncate.
   * @param integer $length Optionally the length to cut to, default 50.
   * @return string
   */
  public static function truncate($value, $length = 50)
  {
    if(strlen($value) < $length)
    {
      return $value;
    }
    else
    {
      return substr($value, 0, $length) . '...';
    }
  }
  
  /** 
   * Return lorem-ipsum filler text.
   * @param boolean $html Optionally allow HTML to be returned. Default false.
   * @return string
   */
  public static function loremIpsum($html = false)
  {
    if(self::exists(BF_ROOT . '/share/text/lorem_ipsum.txt'))
    {
      $lipsumText = file_get_contents(BF_ROOT . '/share/text/lorem_ipsum.txt');
    
      if($html)
      {
        // Replace line breaks
        return str_replace("\n", '<br />' . "\n", $lipsumText);
      }
      else
      {
        // No HTML
        return $lipsumText;
      }
    }
    
    return 'No lorem_ipsum.txt file found.';
  }
  
  /**
   * Detect if a path is a "full" path (as opposed to a "relative" path)
   * within the software.
   * @param string $path The path to examine.
   * @return boolean
   */
  public static function isFullPath($path)
  {
    return (strpos($path, BF_ROOT) !== -1);
  }
  
  /** 
   * Get text from file
   * @param string $fileName The name of the file to load.
   * @return string|boolean
   */
  public static function getText($fileName)
  {
    if(self::exists($fileName))
    {
      // Full path?
      if(self::isFullPath($fileName))
      {
        return file_get_contents($fileName);
      }
      else
      {
        return file_get_contents(BF_ROOT . '/' . $fileName);
      }
    }
    
    return false;
  }
  
  /**
   * Obtain a free private path in the specified directory
   * @param string $path The path to search for a free name
   * @param string $ext The file extension to use without '.' prefix.
   * @param string $base Optionally a base for the random path.
   * @param boolean $useBaseOnly Optionally allow use of just the base, default false.
   * @return string 
   */
  public static function randomPath($path, $extension, $base = '', $useBaseOnly = false)
  {
    // Base path available? If so, use it.
    if($base != '' && $useBaseOnly)
    {
      // Build a path with no random component
      $basePath = $path . '/' . $base . '.' . $extension;
      
      // Check availability
      if(!self::exists($basePath))
      {
        // Use base path - no randomness needed
        return $basePath;
      }
    }
    
    // Create a seed
    $randomSeed = rand(0, 999999);
    
    while(self::exists($path . '/' . ($base ? $base . '-' : '') . 
            substr(md5($randomSeed), 0, ($base ? 4 : 10)) . '.' . $extension)
         )
    {
      $randomSeed = rand(0, 999999);
    }
    
    return $path . '/' . ($base ? $base . '-' : '') . 
           substr(md5($randomSeed), 0, ($base ? 4 : 10)) . '.' . $extension;
  }

  /**
   * Examine a string to see if it contains a specified substring
   * @param string $needle The string to search for.
   * @param string $haystack The string to search in.
   * @return boolean 
   */
  public static function contains($needle, $haystack)
  {
    return (strpos($haystack, $needle) !== false);
  }
  
  /**
   * Linearise an associative array into an array of associative arrays
   * @param array $array An associative array.
   * @param string $keyName Optionally a name for the key.  Default 'id'.
   * @param string $valueName Optionally a name for the value. Default 'value'.
   * @return array
   */
  public static function lineariseArray($array, $keyName = 'id', $valueName = 'value')
  {
    // Build a new array of associative arrays
    $resultArray = array();
    
    foreach($array as $key => $value)
    {
      $resultArray[] = array(
        $keyName => $key,
        $valueName => $value
      );
    }
    
    return $resultArray;
  }
  
  /**
   * Cause the current process to enter nonblocking mode.
   * @return boolean
   */
  public static function nonBlockingMode()
  {
    // Clean output buffer
    ob_end_flush(); 
    header('Connection: close'); 
    ignore_user_abort(true); 
    
    // Start buffer
    ob_start(); 
    header('Content-Length: 0'); 
    ob_end_flush(); 
    flush();

    
    session_write_close();
    
    return true;
  }
  
  /**
   * Make links in a block of text clickable.
   * @author Wordpress
   * @param string $ret The text to search for URLs
   * @return string
   */
  public function makeClickable($ret)
  {
    $ret = ' ' . $ret;
  	// in testing, using arrays here was found to be faster
    $ret = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', array('Tools', 'makeURLClickableCB'), $ret);
    $ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', array('Tools', 'makeWebClickableCB'), $ret);
   
    // this one is not in an array because we need it to run last, for cleanup of accidental links within links
    $ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
    $ret = trim($ret);
    return $ret;
  }

  /**
   * Helper method for Tools::makeClickable (1)
   * @param array $matches
   * @author Wordpress
   * @return array
   */
  private function makeURLClickableCB($matches)
  {
    $ret = '';
    $url = $matches[2];
   
    if ( empty($url) )
      return $matches[0];
    // removed trailing [.,;:] from URL
    if ( in_array(substr($url, -1), array('.', ',', ';', ':')) === true ) {
      $ret = substr($url, -1);
      $url = substr($url, 0, strlen($url)-1);
    }
    return $matches[1] . "<a href=\"$url\" rel=\"nofollow\">$url</a>" . $ret;
  }

  /**
   * Helper method for Tools::make_clickable (2)
   * @param array $matches
   * @author Wordpress
   * @return array
   */
  private function makeWebClickableCB($matches)
  {
    $ret = '';
    $dest = $matches[2];
    $dest = 'http://' . $dest;
    
    if ( empty($dest) )
      return $matches[0];
    // removed trailing [,;:] from URL
    if ( in_array(substr($dest, -1), array('.', ',', ';', ':')) === true ) {
      $ret = substr($dest, -1);
      $dest = substr($dest, 0, strlen($dest)-1);
    }
    return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\">$dest</a>" . $ret;
  }
  
  /**
   * Callback
   * Helper method for Tools::make_clickable (3)
   * @param array $matches
   * @author Wordpress
   * @return array
   */
  function _make_email_clickable_cb($matches)
  {
    $email = $matches[2] . '@' . $matches[3];
    return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
  }
}
?>