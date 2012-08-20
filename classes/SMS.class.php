<?php
/** 
 * SMS API Class
 * Provides access to the Mediaburst SMS gateway.
 *
 * This class uses the Mediaburst SMS Gateway API.
 * The login details must be set in the configuration hive.
 *
 * There is a charge for this service, fixed at £0.05 per message.
 * If no credit is remaining, the send operation will fail.
 * 
 * This class requires libcurl / cURL to operate.
 *
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class SMS extends Base
{  
  /**
   * Is the service enabled?
   * @var boolean
   */
  protected $enabled = true;

  /**
   * The username for the Mediaburst API
   * @var string
   */
  protected $apiUsername = '';
  
  /**
   * The password for the Mediaburst API
   * @var string
   */
  protected $apiPassword = '';

  /**
   * The Originator string to use for messages
   * @var string
   */
  protected $apiOrigin = '';
  
  /**
   * Init (Psuedo-constructor) method
   * @return boolean
   */
  public function init()
  {
    // Set the credentials to use for the API
    $this->apiUsername = $this->parent->config->get('com.b2bfront.sms.username', true);
    $this->apiPassword = $this->parent->config->get('com.b2bfront.sms.password', true);
    $this->apiOrigin = $this->parent->config->get('com.b2bfront.sms.origin', true);
    $this->enable = $this->parent->config->get('com.b2bfront.sms.enable', true);
    
    // Log updates
    $this->parent->log('SMS', 'Logging in to API as ' . $this->apiUsername . ' with ' . 
                       $this->apiPassword);
    
    return true;
  }
  
  /**
   * Send an SMS message to a single SMS endpoint.
   * The SMS must be < 160 characters.
   * The MSISDN must be a fully qualified number with country code already prefixed.
   * @param string $message The message to send.
   * @param string $MSISDN The number to which the message will be sent.
   * @return boolean
   */
  public function send($message, $MSISDN)
  { 
    // Log
    $this->parent->log('SMS', 'Requested to send SMS to ' . $MSISDN);
  
    // Is SMS enabled?
    if(!$this->enabled)
    {
      $this->parent->log('SMS', 'SMS functionality is disabled.  Not sending.');
      return false;
    }
  
    // Restricted send time?
    if($this->parent->config->get('com.b2bfront.sms.no-late-messages', true))
    {
      // Do not send late messages
      if(date('G') < 7)
      {
        // Do not send, queue
        $this->enqueue($message, $MSISDN);
        $this->parent->log('SMS', 'Delivery not allowed at this time - queueing.');
        
        // Still constitutes a successful return value
        return true;
      }
    }
  
    // Cast the number to a string
    $MSISDN = (string)$MSISDN;
  
    // Check the message length
    if(strlen($message) > 160 || strlen(trim($message)) == 0)
    {
      // Message is too long or empty
      $this->parent->log('SMS', 'SMS content too long/empty.  Not sending.');
      return false;
    }
    else
    {
      $this->parent->log('SMS', 'SMS length OK');
    }
    
    // Remove any '+' character from the endpoint
    $MSISDN = str_replace('+', '', $MSISDN);
    
    // Check the endpoint number length
    if(strlen($MSISDN) < 1)
    {
      // Number is not valid
      $this->parent->log('SMS', 'MSISDN length too short');
      return false;
    }
    
    // Check the number is valid
    for($char = 0; $char < strlen($MSISDN); $char ++)
    {
      if(!is_numeric($MSISDN[$char]))
      {
        // Non-numeric number
        $this->parent->log('SMS', 'MSISDN contains invalid characters');
        return false;
      }
    }
    
    // Initiate the sending procedure
    $arguments = array(
      'Username' => $this->apiUsername,
      'Password' => $this->apiPassword,
      'From' => $this->apiOrigin,
      'Content' => $message,
      'To' => $MSISDN
    );
    
    // Build a query string
    $queryString = '?' . Tools::queryString($arguments);
    
    // Build the URL
    $URL = 'http://sms.message-platform.com/http/send.aspx' . $queryString;
    
    // Make a cURL request
    $result = $this->httpRequest($URL);
    
    if(!$result)
    {
      // No cURL or HTTP request failed before being dispatched
      $this->parent->log('SMS', 'Failed to dispatch SMS to ' . $MSISDN);
      $this->parent->log('SMS', 'Continued: Reason for failure: ' . trim($URL));
      return false;
    }
    else
    {
      $this->parent->log('SMS', 'SMS was dispatched to ' . $MSISDN);
      $this->parent->stats->increment('com.b2bfront.stats.system.sms-messages', 1);
    }
    
    return true;
  }
  
  /**
   * Enqueue a message for sending later
   * @param string $message The message text.
   * @param string $MSISDN The MSISDN (Mobile Number) to send the SMS to.
   * @return boolean
   */
  private function enqueue($message, $MSISDN)
  {
    $this->parent->log('SMS', 'Queueing SMS message for: ' . $MSISDN);
    
    // Store the message
    $this->parent->db->insert('bf_sms_queue', array(
                               'msisdn' => $MSISDN,
                               'content' => $message
                             ))
                     ->execute();
  
    return true;
  }
  
  /**
   * Find and attempt to deliver all queued messages.
   * @return boolean
   */
  public function dequeueAll()
  {
    // Get all queued SMS
    $queuedSMS = $this->parent->db->query();
    $queuedSMS->select('*', 'bf_sms_queue')
              ->execute();
          
    // Remove all queued messages
    $this->parent->db->delete('bf_sms_queue')
                     ->execute();
              
    // Try to send each
    while($SMS = $queuedSMS->next())
    {
      $this->send($SMS->msisdn, $SMS->content);
    }
    
    return true;
  }
  
  /**
   * Make a cURL HTTP request to the specified URL and return the result
   * @param string $URL The URL to which the request should be made.
   * @return boolean
   */
  private function httpRequest($URL)
  {
    // Is cURL available?
    if(!function_exists('curl_init'))
    {
      $this->parent->log('SMS', 'SMS: cURL missing - compile PHP with libcurl.');
      return false;
    }
  
    // Create a new cURL resource
    $curlObject = curl_init();
    
    // Set URL and other appropriate options
    curl_setopt($curlObject, CURLOPT_URL, $URL);
    curl_setopt($curlObject, CURLOPT_HEADER, 0);
    curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, 1);
    
    // Make the request
    $result = curl_exec($curlObject);

    // Close the resource
    curl_close($curlObject);
    
    return $result;
  }
}
?>