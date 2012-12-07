<?php
/**
 * Dashboard Issue Creator
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass();
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

function createIssue($title, $body, $label)
{
	// ------------------------------------------------------
	// Validation
	// ------------------------------------------------------
	if($label != 'acp_urgent')
	{
		// General
		$label = 'acp_general';
	}

	// ------------------------------------------------------
	// Create the request
	// ------------------------------------------------------
	$input = array(
		'title' => $title,
		'body' => $body,
		'assignee' => 'themainframe',
		'labels' => array($label)
	);
	
	print_r($input);

	// ------------------------------------------------------
	// Make the request
	// ------------------------------------------------------
	$url = 'https://api.github.com/repos/themainframe/b2bFront/issues';
	$curl = curl_init();

	// Set options
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($input));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HEADER, 1);

	// SSL
	curl_setopt($curl, CURLOPT_SSLVERSION,3); 
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); 

	// HTTP Basic Auth - ugh
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($curl, CURLOPT_USERPWD, "avocetsports:warehouse32"); 

	// Execute
	$result = curl_exec($curl);

	// Close
	curl_close($curl);
}

// --------------------------------------------------
// Create the issue
// --------------------------------------------------

// Get title
$title = $_POST['title'];
$body = $_POST['body'];
$label = $_POST['label'];

// Validate
if(empty($title))
{
  exit();
}

// Dump data
print_r($_POST);

// Create
createIssue($title, $body, $label);

