<?php
/**
 * Admin Module : Template
 *
 * ** This module should never be loaded!**
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

<h1>Template Module</h1>
<p>
  You shouldn't be able to get to this module via any links!
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