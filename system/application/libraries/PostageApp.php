<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('POSTAGEAPP_VERSION', '1.0.0');

/**
 * PostageApp Class
 *
 * Permits email to be sent via PostageApp service
 *
 * @package PostageApp
 * @author Oleg Khabarov, The Working Group Inc.
 * @Modified by Crankeye 10-22-2012
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
   * @url: http://help.postageapp.com/kb/api/send_message
   * @access:  public
   * @return  object
   */
  function send(){
	$function = 'send_message.json';
		
	return $this->_request($function,$this->payload());
  }
  
   /**
   * Get Account Info
   * @desc: Provides information about the account.
   * @url: http://help.postageapp.com/kb/api/get_account_info
   * @access:  public
   * @return:  object
   */
  function get_account_info(){
	$function 	= 'get_account_info.json';
	$payload 	= array('api_key' => $this->api_key);
		
	return $this->_request($function,$payload);
  }
  
  /**
   * Get Message Receipt
   * @desc: Confirm that message with a particular UID exists
   * @params:  $uid - unique email identifier of the message
   * @url: http://help.postageapp.com/kb/api/get_message_receipt
   * @access:  public
   * @return:  object
   */
  function get_message_receipt($uid){
	$function 	= 'get_message_receipt.json';
	$payload 	= array('api_key' => $this->api_key,'uid' => $uid);
		
	return $this->_request($function,$payload);
  }
  
  /**
   * Get Message Transmissions
   * @desc: To get data on individual recipients' delivery and open status, 
   * 		you can pass a particular message UID and receive a JSON encoded
   * 		set of data for each recipient within that message.
   * @params:  $uid - unique email identifier of the message
   * @url: http://help.postageapp.com/kb/api/get_message_transmissions
   * @access:  public
   * @return:  object
   */
  function get_message_transmissions($uid){
	$function 	= 'get_message_transmissions.json';
	$payload 	= array('api_key' => $this->api_key,'uid' => $uid);
		
	return $this->_request($function,$payload);
  }
  
  /**
   * Get Messages
   * @desc: Gets a list of all message UIDs within your project, for subsequent
   * use in collection statistics or open rates.
   * @url: http://help.postageapp.com/kb/api/get_messages
   * @access:  public
   * @return:  object
   */
  function get_messages(){
	$function 	= 'get_messages.json';
	$payload 	= array('api_key' => $this->api_key);
		
	return $this->_request($function,$payload);
  }
  
  /**
   * Get Method List
   * @desc: Get a list of all available api methods.
   * @url: http://help.postageapp.com/kb/api/get_method_list
   * @access:  public
   * @return:  object
   */
  function get_method_list(){
	$function 	= 'get_method_list.json';
	$payload 	= array('api_key' => $this->api_key);
		
	return $this->_request($function,$payload);
  }
  
  /**
   * Get Metrics
   * @desc: Gets data on aggregate delivery and open status for a project,
   * 		broken down by current hour, current day, current week, current 
   * 		month with the previous of each as a comparable.
   * @url: http://help.postageapp.com/kb/api/get_metrics
   * @access:  public
   * @return:  object
   */
  function get_metrics(){
	$function 	= 'get_metrics.json';
	$payload 	= array('api_key' => $this->api_key);
		
	return $this->_request($function,$payload);
  }
  
  /**
   * Get Project Info
   * @desc: Provides information about the project. e.g. urls, transmissions, users.
   * @url: http://help.postageapp.com/kb/api/get_project_info
   * @access:  public
   * @return:  object
   */
  function get_project_info(){
	$function 	= 'get_project_info.json';
	$payload 	= array('api_key' => $this->api_key);
		
	return $this->_request($function,$payload);
  }
  
   /**
   * Request
   * @desc: Make a request to the Code Igniter JSON API
   * @access:  private
   * @return:  object
   */
  function _request($function,$payload)
  {
	$protocol = $this->secure ? 'https' : 'http';
    $ch = curl_init($protocol.'://'.$this->host.'/v.1.0/'.$function);
    curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($payload));
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