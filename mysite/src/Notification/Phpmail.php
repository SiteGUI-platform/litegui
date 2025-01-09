<?php
namespace SiteGUI\Notification;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\SMTP;
use \PHPMailer\PHPMailer\Exception;

class Phpmail {
	protected $settings;
	protected $mailer;

	public function __construct($config){
		$this->settings = $config;

		$this->mailer = new PHPMailer(true);
		$this->mailer->CharSet = 'UTF-8';
		$this->mailer->XMailer = 'SGMailer';
		$this->mailer->isHTML(true); //Set email format to HTML
	}

	public static function config($property = '') {
	    $config['app_configs'] = [
	        'quota' => [
	            'label' => 'Email Limit per Month',
	            'visibility' => 'editable',
	            'type' => 'text',
	            'size' => '6',
	            'value' => '300',
	            'description' => 'Applies to PHP mail() only, use your own SMTP account for no limit',
	        ],
	        'sent' => [
	            'label' => 'Email Sent',
	            'visibility' => 'readonly',
	            'type' => 'text',
	            'size' => '6',
	            'value' => ' ',
	            'description' => '',
	        ],
	        'last_sent' => [
	            'label' => 'Last Sent',
	            'visibility' => 'readonly',
	            'type' => 'time',
	            'size' => '6',
	            'value' => ' ',
	            'description' => '',
	        ]           
	    ];
    	return ($property)? $config[ $property ] : $config;		
    }
    //$batch: array(to_name, to_mail, to_bcc, subject (including subject2), body)
	public function email($from_name, $from_mail, $to_name, $to_mail, $to_bcc, $subject, $body, $attachments = null) {
		if ( empty($this->settings['app']['quota']) ){
			$this->settings['app']['quota'] = 300; //limit to 300 email/month for non configured site
		}
		$month_start = new \DateTime();
		// Set the current date to the first day of the current month
		$month_start->modify('first day of this month');
		if (empty($this->settings['app']['sent']) || $this->settings['app']['last_sent'] < $month_start->getTimestamp() ){
			$this->settings['app']['sent'] = 0; 
		}

		try {
			if (!empty($this->settings['app']['sent']) AND 
				$this->settings['app']['sent'] >= $this->settings['app']['quota'] 
			){
				$response['message'] = "Mail quota reached. Please use a SMTP Channel to send more emails";
		    	$response['status'] = 'error';
			} else {  
	    		$this->mailer->ClearAddresses(); //clear previous batch
	    		$this->mailer->ClearBCCs();
			    $this->mailer->setFrom($from_mail, $from_name);

	    		foreach ($to_mail??[] as $address) {
	    			$this->mailer->addAddress($address, $to_name??''); //Add a recipient, Name is optional
	    			$this->settings['app']['sent']++; 	
	    		}
	    		foreach ($to_bcc??[] as $address) {
	    			$this->mailer->addBcc($address); 
	    			$this->settings['app']['sent']++; 	
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
             if ( !empty($this->settings['app']['sent']) ){ //update sent
				    $response['new_tokens']['sent'] = $this->settings['app']['sent'];
				    $response['new_tokens']['last_sent'] = time();
				 } 
				 $response['status'] = 'success';   
			}	
		} catch (Exception $e) {
		    $response['message'] = "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}";
		    $response['status'] = 'error';
		}
		return $response;
	}
}	
