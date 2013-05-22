<?php

class NMPBatch {
	// api url
	const API_URL = 'http://api.notificationmessaging.com/NMSXML';	
	
	/**
	 * Debug? True/false
	 *
	 * @var bool
	 */
	private $_debug;
	
	/**
	 * Messages array, for sending out batches of messages through 1 request
	 *
	 * @var array
	 */
	private $_messages;


	/**
	 * Add message to message array
	 * 
	 * @param string $message
	 */
    public function addMessage (NMPMessage $message) {
        $this->_messages[] = $message;
    }

	/**
	 * Set debug
	 * 
	 * @param int $debug
	 */
    public function setDebug ($debug) {
        $this->_debug = (bool)$debug;
    }

	/**
	 * Get debug
	 * 
	 * @return bool
	 */
    public function getDebug () {
        return $this->_debug;
    }

	/**
	 * Returns all messages
	 * 
	 * @return array
	 */
    private function getMessages() {

    	$output = '';
    	
    	foreach($this->_messages as $val) {
    		$output .= '<sendrequest>';
			$output .= '<dyn>';
				
				$kpv = $val->returnDynamicValues();
				if(count($kpv) > 0) {
					foreach($kpv as $k=>$v) {
						$output .= '
					<entry>
						<key>' .$k. '</key>
						<value>' .$v. '</value>
					</entry>';
					}
				}
				
				$output .= '
				</dyn>
				<content>
					<entry>
						<key>1</key>
						<value>
							<![CDATA[' .$val->getMailHtml(). ']]>
						</value>
					</entry>
					<entry>
						<key>2</key>
						<value>
							<![CDATA[' .$val->getMailText(). ']]>
						</value>
					</entry>
				</content>
				
				<notificationId>' .$val->getNotificationId(). '</notificationId>
				<email>' .$val->getEmailRecipient(). '</email>
				<encrypt>' .$val->getEncryptToken(). '</encrypt>
				<random>' .$val->getRandomToken(). '</random>
				<senddate>' .$val->getEmailTime(). '</senddate>
				<synchrotype>' .$val->getSyncType(). '</synchrotype>
				<uidkey>' .$val->getSyncKey(). '</uidkey>
			</sendrequest>';
    	}
        return $output;
    }
    

	/**
	 * Send to Emailvision API
	 * 
	 * @return bool
	 */
	public function send() {			
		// build final xml
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<MultiSendRequest>
		' .$this->getMessages(). '
		</MultiSendRequest>';
		
		// init curl and send xml to API
		$curl = curl_init(self::API_URL);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);
		curl_close($curl);
		
		// if debug mode is true, send input + output, else return booleans
		if($this->getDebug()) {
			return array('output' => $curl_response, 'input' => $this);
		}
		else {
			return ($curl_response != 1) ? true : false;
		}
	}
}