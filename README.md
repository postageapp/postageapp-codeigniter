[PostageApp](http://postageapp.com) for CodeIgniter
===================================================

This is the gem used to integrate CodeIgniter apps with PostageApp service.
Personalized, mass email sending can be offloaded to PostageApp via JSON based API.

### [API Documentation](http://help.postageapp.com/faqs/api) &bull; [PostageApp FAQs](http://help.postageapp.com/faqs) &bull; [PostageApp Help Portal](http://help.postageapp.com)

Installation
------------
 - Copy both `system/application/config/postageapp.php` and `system/application/libraries/PostageApp.php`
 - Edit `config/postageapp.php` to include your PostageApp Project API key.

Usage
-----
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
