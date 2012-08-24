<?php

// activate full error reporting
//error_reporting(E_ALL & E_STRICT);

include_once("./classes/curl/class.curl.php") ;
include './classes/xmpphp-0.1rc2-r77/XMPPHP/XMPP.php';

#Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
$conn = new XMPPHP_XMPP('talk.google.com', 5222, 'tap1tclick1t0wn1t', 'tap1tclick1t0wn1t', 'xmpphp', 'gmail.com', $printlog=true, $loglevel=XMPPHP_Log::LEVEL_INFO);
$conn->autoSubscribe();

$vcard_request = array();

try {
    while(!$conn->isDisconnected()) {
    	$payloads = $conn->processUntil(array('message', 'presence', 'end_stream', 'session_start', 'vcard'));
    	foreach($payloads as $event) {
    		$pl = $event[1];
    		switch($event[0]) {
    			case 'message': 
    				print "---------------------------------------------------------------------------------\n";
    				print "Message from: {$pl['from']}\n";
    				if($pl['subject']) print "Subject: {$pl['subject']}\n";
    				print $pl['body'] . "\n";
                    $c = new curl("https://www.google.com/search?rlz=1C1LENN_enUS496US496&aq=f&sugexp=chrome,mod=16&sourceid=chrome&client=ubuntu&channel=cs&ie=UTF-8&q=".urlencode($pl['body']));
                    $c->setopt(CURLOPT_FOLLOWLOCATION, true) ;
                    $conn->connect();
                    $response_body = $c->exec() ;
                    $f = fopen("./output/output.html", "w"); 
                    fwrite($f, $response_body); 
                    fclose($f);
    				print "---------------------------------------------------------------------------------\n";
    				$conn->message($pl['from'], $body="Thanks for sending me \"{$pl['body']}\".", $type=$pl['type']);
					$cmd = explode(' ', $pl['body']);
    				if($cmd[0] == 'quit') $conn->disconnect();
    				if($cmd[0] == 'break') $conn->send("</end>");
    				if($cmd[0] == 'vcard') {
						if(!($cmd[1])) $cmd[1] = $conn->user . '@' . $conn->server;
						// take a note which user requested which vcard
						$vcard_request[$pl['from']] = $cmd[1];
						// request the vcard
						$conn->getVCard($cmd[1]);
					}
    			break;
    			case 'presence':
    				print "Presence: {$pl['from']} [{$pl['show']}] {$pl['status']}\n";
    			break;
    			case 'session_start':
    			    print "Session Start\n";
			    	$conn->getRoster();
    				$conn->presence($status="Cheese!");
    			break;
				case 'vcard':
					// check to see who requested this vcard
					$deliver = array_keys($vcard_request, $pl['from']);
					// work through the array to generate a message
					print_r($pl);
					$msg = '';
					foreach($pl as $key => $item) {
						$msg .= "$key: ";
						if(is_array($item)) {
							$msg .= "\n";
							foreach($item as $subkey => $subitem) {
								$msg .= "  $subkey: $subitem\n";
							}
						} else {
							$msg .= "$item\n";
						}
					}
					// deliver the vcard msg to everyone that requested that vcard
					foreach($deliver as $sendjid) {
						// remove the note on requests as we send out the message
						unset($vcard_request[$sendjid]);
    					$conn->message($sendjid, $msg, 'chat');
					}
				break;
    		}
    	}
    }
} catch(XMPPHP_Exception $e) {
    die($e->getMessage());
}
