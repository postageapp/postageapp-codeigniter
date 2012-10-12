<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('POSTAGEAPP_VERSION', '1.0.0');

/**
 * PostageApp Class
 *
 * Permits email to be sent via PostageApp service
 *
 * @package PostageApp
 * @author Oleg Khabarov, The Working Group Inc.
 * @link http://postageapp.com
 */
class PostageApp {
  
  var $api_key            = '';
  var $secure             = TRUE;
  var $host               = 'api.postageapp.com';
  var $recipient_override = '';
  var $template           = '';
  var $variables          = array();
  var $_arguments         = array();
  
  /**
   * Constructor - Sets PostageApp Preferences
   *
   * The constructor can be passed an array of config values
   */
  function PostageApp($config = array()){
    $this->initialize($config);
    log_message('debug', 'PostageApp Class Initialized');
  }
  
  /**
   * Initialize preferences
   *
   * @access  public
   * @param   array
   * @return  void
   */
  function initialize($config = array()){
    $this->clear();
    if(count($config) > 0){
      foreach($config as $key => $val){
        if(isset($this->$key)){
          $this->$key = $val;
        }
      }
    }
  }
  
  /**
   * Setting Defaults
   *
   * @access  public
   * @return  void
   */
  function clear(){
    $this->api_key            = '';
    $this->secure             = TRUE;
    $this->host               = 'api.postageapp.com';
    $this->recipient_override = '';
    $this->template           = '';
    $this->variables          = array();
    $this->_arguments         = array();
  }
  
  /**
   * Setting arbitrary message headers. You may set from, subject, etc here
   *
   * @access  public
   * @return  void
   */
  function headers($headers = array()){
    $this->_arguments['headers'] = $headers;
  }
  
  /**
   * Setting Subject Header
   *
   * @access  public
   * @return  void
   */
  function subject($subject){
    $this->_arguments['headers']['subject'] = $subject;
  }
  
  /**
   * Setting From header
   *
   * @access  public
   * @return  void
   */
  function from($from){
    $this->_arguments['headers']['from'] = $from;
  }
  
  /**
   * Setting Recipients. Accepted formats for $to are (see API docs):
   *   -> 'recipient@example.com'
   *   -> 'John Doe <recipient@example.com>'
   *   -> 'recipient1@example.com, recipient2@example.com'
   *   -> array('recipient1@example.com', 'recipient2@example.com')
   *   -> array('recipient1@example.com' => array('variable1' => 'value',
   *                                              'variable2' => 'value'),
   *            'recipient2@example.com' => array('variable1' => 'value',
   *                                              'variable2' => 'value'))
   * @access  public
   * @return  void
   */
  function to($to){
    $this->_arguments['recipients'] = $to;
  }
  
  /**
   * Setting message body. If you need to send both html and text set $content to:
   *   array(
   *    'text/html'   => 'HTML Content,
   *    'text/plain'  => 'Plain Text Content
   *   )
   *
   * @access  public
   * @return  void
   */
  function message($content){
    $this->_arguments['content'] = $content;
  }
  
  
  /**
   * Appending attachments to the message
   *
   * @access  public
   * @return  void
   */
  function attach($filename){
    $handle = fopen($filename, 'rb');
    $file_content = fread($handle, filesize($filename));
    fclose($handle);
    
    $this->_arguments['attachments'][basename($filename)] = array(
      'content_type'  => mime_content_type($filename),
      'content'       => chunk_split(base64_encode($file_content), 60, "\n")
    );
  }
  
  /**
   * Setting PostageApp project template
   *
   * @access  public
   * @return  void
   */
  function template($template){
    $this->_arguments['template'] = $template;
  }
  
  /**
   * Setting  message variables
   *
   * @access  public
   * @return  void
   */
  function variables($variables = array()){
    $this->_arguments['variables'] = $variables;
  }
  
  /**
   * Content that gets sent in the API call
   *
   * @access  public
   * @return  array
   */
  function payload(){
    $message = array(
      'api_key'   => $this->api_key,
      'uid'       => sha1(time() . json_encode($this->_arguments)),
      'arguments' => $this->_arguments
    );
    
    // applying recipient override
    if($this->recipient_override != ''){
      $message['arguments']['recipient_override'] = $this->recipient_override;
    }
    
    return $message;
  }
  
  /**
   * Send Email message via PostageApp
   *
   * @access  public
   * @return  object
   */
  function send(){
    $protocol = $this->secure ? 'https' : 'http';
    $ch = curl_init($protocol.'://'.$this->host.'/v.1.0/send_message.json');
    curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($this->payload()));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'User-Agent: PostageApp CodeIgniter '.POSTAGEAPP_VERSION . ' (CI '.CI_VERSION.', PHP '.phpversion().')'
    ));   
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output);
  }
}

/* End of file PostageApp.php */
/* Location: ./system/application/libraries/PostageApp.php */