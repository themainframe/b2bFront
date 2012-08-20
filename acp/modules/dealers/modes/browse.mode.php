<?php
/**
 * Module: Dealers
 * Mode: Browse
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}

?>

<h1>Browse Dealers</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Dealers</h3>
    <p>
      Dealers are the users of the b2b ordering platform.<br />
      They allow the system to store individuals preferences and orders so that they can be loaded each time they visit the site.
    </p>

    <br />
    <span class="button">
      <a href="./?act=dealers&mode=add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Dealer...
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div> 

<br />

<script type="text/javascript">
  
  $(function() {
    
    $('.more').each(function(i, r) {
      $(r).createMenu({
      
        content : $('#menu').html(),
        flyOut : true,
        'id': $(this).attr('row')

      });
    });
    
  });
  
</script>

<div class="ghost" id="menu">
  <ul>
    <li>
      <a style="background-image:url(/acp/static/icon/key.png);"
       class="menu_a" target="_blank" 
       href="./?act=dealers&mode=browse_login_as_do&letter=<?php print $BF->in('letter'); ?>&id={id}">
        Log In As...
      </a>
    </li>
    <li>
      <a style="background-image:url(/acp/static/icon/money-coin.png);" target="_blank" 
       class="menu_a" href="./?act=dealers&mode=browse_orders&id={id}&letter=<?php print $BF->in('letter'); ?>">
        Show Orders...
      </a>
    </li>    
    <li>
      <a style="background-image:url(/acp/static/icon/currency-pound.png);" target="_blank" 
       class="menu_a" href="./?act=dealers&mode=browse_overrides&id={id}">
        Pricing Overrides...
      </a>
    </li>
  </ul>
</div>

<?php
  
  // Generate an A-Z bar
  $range = array_merge(range('a', 'z'), array('#'));

  // Get current page
  $current = $BF->in('letter');
  
  // Check 
  if(!$current || !in_array($current, $range))
  {
    $current = 'a';
  }

  // Show a bar
  $output  = '';
  $output .= '<div class="pager">' . "\n";
  $output .= '  <table style="float: left">' . "\n";
  $output .= '    <tbody>' . "\n";
  $output .= '      <tr>' . "\n";

  foreach($range as $letter)
  {
    $output .= '        <td class="page' . ($current == $letter ? ' current' : '') . '">' . "\n";
    $output .= '          <a href="' . Tools::getModifiedURL(array('letter' => $letter)) . '" title="' . 
               strtoupper($letter) . '">' . strtoupper($letter) . '</a>' . "\n";
    $output .= '        </td>' . "\n";
  }

  $output .= '      </tr>' . "\n";
  $output .= '    </tbody>' . "\n";
  $output .= '  </table>' . "\n";
  $output .= '  <br class="clear" />' . "\n";
  $output .= '</div>' . "\n";
  $output .= '<br />' . "\n";
      
  // Render letter selection
  print $output;

  // # / Number search?
  if($current == '#')
  {
    $current = '[0-9]';
  }

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_users')
        ->where('`requires_review` = \'0\' AND (name REGEXP \'^{1}.*\' OR name REGEXP \'^{2}.*\')', $current, strtoupper($current));
  
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this dealer?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'browse_remove_do', 'letter' => $current)) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=dealers&mode=browse_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  $toolSet .= '<a class="tool notext more" row="{id}" href="#" title="More">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/control-270-g.png" class="notext" alt="More" />' . "\n";
  $toolSet .= '</a>' . "\n"; 
  
  // Create a data table view
  $dealers = new DataTable('dealers_browse', $BF, $query);
  $dealers->setOption('alternateRows');
  $dealers->setOption('showTopPager');
  $dealers->setOption('showDownloadOption');
  $dealers->addColumns(array(
                        array(
                          'niceName' => '',
                          'dataName' => 'id',
                          'css' => array(
                                     'width' => '10px'
                                   ),
                          'options' => array(
                                              'formatAsCheckbox' => true,
                                              'fixedOrder' => true
                                            )
                        ),
                        array(
                          'dataName' => 'account_code',
                          'css' => array(
                                     'width' => '90px'
                                   ),
                          'niceName' => 'Acc #'
                        ),
                        array(
                          'dataName' => 'name',
                          'niceName' => 'User Name'
                        ),
                        array(
                          'dataName' => 'description',
                          'niceName' => 'Description'
                        ),
                        array(
                          'dataName' => 'email',
                          'niceName' => 'Email',
                          'options' => array(
                                              'formatAsMailto' => true
                                            )
                        ),
                        array(
                          'niceName' => 'Options',
                          'css' => array(
                                     'width' => '170px'
                                   ),
                          'content' => $toolSet
                        )
                      ));

  // Render & output content
  print $dealers->render();
  
?>