<?php
/** 
 * Email Class
 * Provides services for constructing and dispatching mail to
 * to one or more recipients.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Email extends Base
{
  /**
   * The collection of recipients
   * Array of EmailRecipient instances
   * @var array
   */
  public $recipients = array();
  
  /** 
   * The mail text
   * @var string
   */
  public $text = '';
  
  /** 
   * Any additional headers, as an associative array, to be attached
   * @var array
   */
  private $headers = array();
  
  /**
   * An associative array of mail merge tokens and values.
   * @var array
   */
  public $values = array();

  /**
   * The subject line of the message.
   * @var string
   */
  public $subject = '';
  
  /**
   * The address from which messages will be sent
   * @var string
   */
  public $from = 'b2bfront@localhost';
  
  /**
   * The name from which messages will be sent
   * @var string
   */
  public $fromName = 'b2bfront';
  
  /**
   * Load defaults
   * @return boolean
   */
  public function init()
  {
    $this->from = $this->parent->config->get('com.b2bfront.mail.from-address', true);
    $this->fromName = $this->parent->config->get('com.b2bfront.mail.from', true);
    
    // Make headers
    $this->createHeaders();
    
    return true;
  }
  
  /**
   * Automatically create headers
   * NB: Replaces any existing headers.
   * @return boolean
   */
  public function createHeaders()
  {
    $this->headers = array(
      'From' => '"' . $this->fromName . '" <' . $this->from . '>',
      'Content-type' => 'text/html; charset=iso-8859-1',
      'MIME-Version' => '1.0'
    );
    
    return true;
  }
  
  /**
   * Render headers as text
   * @return string
   */
  private function renderHeaders()
  {
    $headerText = '';
    $headerCount = count($this->headers) - 1;
    $current = 0;
    
    // Build string
    foreach($this->headers as $key => $value)
    {
      $headerText .= $key . ': ' . $value . ($headerCount == $current ? "\r" : '') . "\n";
      $current ++;
    }
    
    return $headerText;
  }
  
  /**
   * Assign an array of values as mail merge tokens
   * $values should be an associative array of string => mixed.
   * @param array $values The values to use as mail merge tokens.
   * @return boolean
   */
  public function assign($values)
  {
    // Add each value
    foreach($values as $key => $value)
    {
      $this->values[$key] = $value;
    }
    
    return true;
  }
  
  /**
   * Set the subject of the message
   * The subject line may contain mail merge tokens.
   * @param string $subject The subject of the message.
   * @return boolean
   */
  public function setSubject($subject)
  {
    $this->subject = $subject;
    
    return true;
  }
  
  /** 
   * Adjust the contents of a string to remove and convert any mail merge tokens
   * @param string $value The string to adjust.
   * @param array $additionalTokens Optionally an associative array of additional tokens.
   * @return string
   */
  public function replaceTokens($value, $additionalTokens = array())
  {
    // Copy value
    $newValue = $value;
  
    // First, replace global values
    foreach($this->values as $key => $value)
    {
      $newValue = str_replace('{' . $key . '}', $value, $newValue);
    }
    
    // Now replace additional values
    foreach($additionalTokens as $key => $value)
    {
      $newValue = str_replace('{' . $key . '}', $value, $newValue);
    }
    
    return $newValue;
  }
  
  /**
   * Load template text from file
   * @param string $fileName The file to load.
   * @return boolean
   */
  public function loadFromFile($fileName)
  {
    if(Tools::exists($fileName))
    {
      $this->text = Tools::getText($fileName);
      return true;
    }
    
    return false;
  }
  
  /**
   * Send mail
   * @return boolean
   */
  public function send()
  {
    // Count mail
    $mailSent = 0;

    // Send the mail
    foreach($this->recipients as $recipient)
    {
      mail($recipient->address, $this->replaceTokens($this->subject), 
        $this->replaceTokens($this->text, $recipient->mergeValues), $this->renderHeaders());
    
      $mailSent ++;
    }
    
    // Increment stats
    $this->parent->stats->increment('com.b2bfront.stats.website.emails-sent', $mailSent);

    return true;
  } 
  
  /**
   * Send mail and sleep in between.
   * @param integer $wait Optionally a period to wait between emails, Default 1 second.
   * @return boolean
   */
  public function sendSleep($wait = 1)
  {
    // Send the mail
    foreach($this->recipients as $recipient)
    {
      mail($recipient->address, $this->replaceTokens($this->subject), 
        $this->replaceTokens($this->text, $recipient->mergeValues), $this->renderHeaders());
      
      // Increment stats
      $this->parent->stats->increment('com.b2bfront.stats.website.emails-sent', 1);

      sleep($wait);
    }

    return true;
  } 
  
  /**
   * Get the mail text without sending
   * @return string
   */
  public function getText()
  {
    return $this->replaceTokens($this->text);
  } 
  
  /**
   * Add a recipient to the email
   * @param string $emailAddress The email address of the recipient
   * @param array $values Optionally an associative array of mail merge tokens and values.
   * @return boolean
   */
  public function addRecipient($emailAddress, $values = array())
  {
    $this->recipients[] = new EmailRecipient($emailAddress, $values);
    
    return true;
  }

}
?>