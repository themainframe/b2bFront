<?php
/**
 * Admin Module : Error
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

?>

<h1>Operation Error</h1>
<br />
<p>
  There was a problem with the previous operation.<br />
  This has been reported to Transcend support desk.<br /><br />
  
  Please try the operation again.<br />
  <br />
  <a href="javascript: history.back();" title="Back">Back</a>
</p>

<script type="text/javascript">

  
  /** 
   * This block removes the submenu.
   * It can be commented out if not required for a specific module.
   */
  $(function()
  {
    $("div.sub_bar").hide();
  });
  
  
</script>