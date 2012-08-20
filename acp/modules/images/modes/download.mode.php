<?php
/**
 * Module: Images
 * Mode: Download All
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

// Get the estimated size
$getSize = $BF->db->query();
$getSize->text('SELECT SUM(`size_bytes`) AS total FROM `bf_images`;')
        ->execute();

$totalRow = $getSize->next();
$bigTotal = floatval($totalRow->total) * 1.5;
$mbTotal = $bigTotal/1024/1024;

// Get free bytes
if(disk_free_space('/') < $bigTotal)
{
?>

<h1>Download All Images</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>Not enough storage</h3>
    <p>
      There is not enough available storage space to generate the bundle.<br />
      <br />
      
      <strong>Required:</strong> <?php print number_format($mbTotal, 2); ?> MB<br />
      <strong>Available:</strong> <?php print number_format(disk_free_space('/') / 1024 / 1024, 2); ?> MB
    </p>
    
  </div>
</div>

<?php
}
elseif(file_exists('/tmp/imbundle/')) 
{
?>

<h1>Download All Images</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>Already in progress</h3>
    <p>
      A bundle build is already in progress.<br />
      Please wait for the current build to complete before starting another.<br />
    </p>
    
  </div>
</div>

<?php

}
else
{
?>

<h1>Download All Images</h1>
<br />



<div class="panel">
  <div class="contents">
    <h3>About Downloading all Images</h3>
    <p>
      This feature allows you to download a zipped bundle of all images organised into folders.<br />
      The directory structure of the bundle will be organised as Category->SKU->Images.<br /><br />
      
      A downloadable bundle including all images will be approximately<strong>
      <?php print number_format((floatval($totalRow->total) * 0.5) / 1024 / 1024, 0); ?> MB</strong> in size.<br />
      Additionally, approximately <strong><?php print number_format($mbTotal, 0); ?> MB</strong> working space on the server will be required during the build.
      <br /><br />
      
      Choose the categories you wish to include below.
    </p>
    
  </div>
</div>

<form action="./?act=images&mode=download_do" method="post">
<br />

<?php

  // Query for categories
  $categories = $BF->db->query();
  $categories->select('*', 'bf_categories');

  // Create a data table view
  $downloads = new DataTable('images_download', $BF, $categories);
  $downloads->setOption('alternateRows');
  $downloads->setOption('defaultOrder', array('name', 'asc'));
  $downloads->addColumns(array(
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
                            'dataName' => 'name',
                            'niceName' => 'Name'
                          )
                        ));

  // Render & output content
  print $downloads->render();


?>


<br />

<div class="panel">
  <div class="title">Options</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr class="last">
          <td class="key">
            <strong>Include Descriptions</strong><br />
            Also include a .html file of the description for each SKU.<br />
          </td>
          <td class="value">
            <input name="f_description_files" id="f_description_files" type="checkbox" value="1" />
          </td>
        </tr>
        
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to start building the download.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Build Image Bundle" />
    <br class="clear" />
  </div>
</div>

</form>

<?php
}
?>