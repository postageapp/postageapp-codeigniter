[PostageApp](http://postageapp.com) for CodeIgniter
===================================================

This is the gem used to integrate CodeIgniter apps with PostageApp service.
Personalized, mass email sending can be offloaded to PostageApp via JSON based API.
All documented API functions have been added. See the API Documentation for more information.

### [API Documentation](http://help.postageapp.com/faqs/api) &bull; [PostageApp FAQs](http://help.postageapp.com/faqs) &bull; [PostageApp Help Portal](http://help.postageapp.com)

Installation
------------
 - Copy both `system/application/config/postageapp.php` and `system/application/libraries/PostageApp.php`
 - Edit `config/postageapp.php` to include your PostageApp Project API key.

Email Usage
-----------
PostageApp for CodeIgniter works very similarly to built-in Email class. Here's a simple example:

    $this->load->library('postageapp');
    
    $this->postageapp->from('sender@test.test');
    $this->postageapp->to('recipient@test.test');
    $this->postageapp->subject('Example Email');
    $this->postageapp->message('Example Message');
    $this->postageapp->attach('/path/to/a/file.ext');
    
    $this->postageapp->template('test-template');
    $this->postageapp->variables(array('variable' => 'value'));
    
    $this->postageapp->send(); # returns JSON response from the server

If you wish to send both html and plain text parts call message function like this:
    
    $this->postageapp->message(array(
      'text/html'   => 'html content',
      'text/plain'  => 'text content'
    ));
    
You can set headers all in one go:

    $this->postageapp->headers(array(
      'subject' => 'Example Subject',
      'from'    => 'sender@example.com'
    ));
    
Recipients can be specified in a number of ways. Here's how you define a list of them with variables attached:

    $this->postageapp->to(array(
      'recipient1@example.com' => array('variable1' => 'value',
                                        'variable2' => 'value'),
      'recipient2@example.com' => array('variable1' => 'value',
                                        'variable2' => 'value')
    ));
    
For more information about formatting of recipients, templates and variables please see [documentation](http://help.postageapp.com/faqs)
    
### Recipient Override
To override the recipient insert your email address in `config/postageapp.php`:

    $config['recipient_override'] = 'you@example.com';

Other Usage
-----------

### Get Account Info
Provides information about the account.
	
	$this->postageapp->get_account_info();
	
### Get Message Receipt
Confirm that message with a particular UID exists
	
	$this->postageapp->get_message_receipt('Example UID');

### Get Message Transmissions
To get data on individual recipients' delivery and open status, you can pass a particular message UID and receive a JSON encoded set of data for each recipient within that message.
	
	$this->postageapp->get_message_transmissions('Example UID');
	
### Get Messages
Gets a list of all message UIDs within your project, for subsequent use in collection statistics or open rates.
	
	$this->postageapp->get_messages();
	
### Get Method List
Get a list of all available api methods.
	
	$this->postageapp->get_method_list();
	
### Get Metrics
Gets data on aggregate delivery and open status for a project, broken down by current hour, current day, current week, current month with the previous of each as a comparable.
	
	$this->postageapp->get_metrics();
	
### Get Project Info
Provides information about the project. e.g. urls, transmissions, users.
	
	$this->postageapp->get_project_info();
