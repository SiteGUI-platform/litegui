<?php
namespace SiteGUI\Notification;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\SMTP;
use \PHPMailer\PHPMailer\Exception;

class Smtpserver {
	protected $settings;
	protected $mailer;

	public function __construct($config){
		$this->settings = $config;

		$this->mailer = new PHPMailer(true);
	    //Server settings
	    //$this->mailer->SMTPDebug = SMTP::DEBUG_SERVER; 
		$this->mailer->CharSet 	  = 'UTF-8';
		$this->mailer->XMailer 	  = 'SGMailer';
		$this->mailer->isHTML(true); //Set email format to HTML
	    $this->mailer->isSMTP();                          //Send using SMTP
	    $this->mailer->SMTPAuth   = true;                 //Enable SMTP authentication
	    $this->mailer->Host       = $this->settings['app']['host'];         //Set the SMTP server to send through
	    $this->mailer->Username   = $this->settings['app']['username'];     //SMTP username
	    $this->mailer->Password   = $this->settings['app']['password'];     //SMTP password
	    $this->mailer->Port       = $this->settings['app']['port']??465;    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
	    if ('tls' == ($this->settings['app']['secure']??null) ){
		    $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;//Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
	    } elseif ('ssl' == ($this->settings['app']['secure']??null) ){
	    	$this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
	    }
	}

	public static function config($property = '') {
	    $config['app_configs'] = [
	        'host' => [
	            'label' => 'SMTP Host',
	            'type' => 'text',
	            'size' => '6',
	            'value' => '',
	            'description' => 'Enter the SMTP Hostname (SMTP only)',
	        ],
	        'username' => [
	            'label' => 'Username',
	            'type' => 'text',
	            'size' => '6',
	            'value' => '',
	            'description' => 'Enter the Username',
	        ],
	        // a password field type allows for masked text input
	        'password' => [
	            'label' => 'Password',
	            'type' => 'password',
	            'size' => '6',
	            'value' => '',
	            'description' => 'Enter the Password',
	        ],            
            'secure' => [
                'label' => 'SSL Type',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'ssl' => 'SSL',
                    'tls' => 'TLS',
                ],
                'value' => 'ssl',
                'description' => 'SMTP only',
            ],
	        'port' => [
	            'label' => 'SMTP Port',
	            'type' => 'text',
	            'size' => '6',
	            'value' => '465',
	            'description' => 'Enter the SMTP Port (SMTP only)',
	        ],
	    ];
    	return ($property)? $config[ $property ] : $config;		
    }

    //$batch: array(to_name, to_mail, to_bcc, subject (including subject2), body)
	public function email($from_name, $from_mail, $to_name, $to_mail, $to_bcc, $subject, $body, $attachments = null) {
		try {
			if (!empty($this->settings['app']['host']) AND 
				!empty($this->settings['app']['username']) AND 
				!empty($this->settings['app']['password']) 
			){
	    		$this->mailer->ClearAddresses(); //clear previous batch
	    		$this->mailer->ClearBCCs();
			    $this->mailer->setFrom($from_mail, $from_name);

	    		foreach ($to_mail??[] as $address) {
	    			$this->mailer->addAddress($address, $to_name??''); //Add a recipient, Name is optional
	    		}
	    		foreach ($to_bcc??[] as $address) {
	    			$this->mailer->addBcc($address); 
	    		}
		    	$this->mailer->Subject = $subject;
			    $this->mailer->Body    = $body;
			    $this->mailer->AltBody = $this->mailer->html2text(nl2br($this->mailer->Body));
			    if ( $attachments ){
			    	if ( !is_array($attachments) ){
			    		$attachments = [ $attachments ];
			    	}	
		    		foreach( $attachments AS $attachment){
		    			$this->mailer->addAttachment($attachment);
		    		}
			    }
			    
			    $this->mailer->send();

				$response['status'] = 'success';   
			} else {  
				$response['message'] = "SMTP Server is not properly configured";
		    	$response['status'] = 'error';
		    }	
		} catch (Exception $e) {
		    $response['message'] = "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}";
		    $response['status'] = 'error';
		}
		return $response;
	}
}	