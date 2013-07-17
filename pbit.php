<?php
function stream_fwrite($fp, $string) {
    for ($written = 0; $written < strlen($string); $written += $fwrite) {
        $fwrite = fwrite($fp, substr($string, $written));
        if ($fwrite === false) {
            return $written;
        }
    }
    return $written;
}

$connection = array(
	'ip' => '10bit.danopia.net',
	'port' => 10817
);
function init_socket(){
	global $connection;
	$remote_addr = "tcp://{$connection['ip']}:{$connection['port']}";
	$socket = stream_socket_client($remote_addr, $errno, $errstr, 30);
	if (!$socket) throw "Couldn't create socket: $errstr\n";
	stream_context_set_option($socket, 'ssl', 'verify_peer', false);
	stream_context_set_option($socket, 'ssl', 'allow_self_signed', true);
	stream_set_blocking ($socket, true);
	stream_socket_enable_crypto ($socket, true, STREAM_CRYPTO_METHOD_SSLv3_CLIENT);
	stream_set_blocking ($socket, false);
	return $socket;
}
function decode_message($raw_message){
	return json_decode($raw_message,true);
}
function send_command($socket,$contents_array){
	$encoded = json_encode($contents_array);
	$write = fwrite($socket,$encoded . "\n");
	if($write !== 0){
		return true;
	} else {
		return false;
	}
}
function on_welcome($socket){
	$login = array(
		'op' => 'auth',
		'ex' => array(
			'method' => 'password',
			'username' => 'Bot Nick',
			'password' => 'Bot Pass'
			)
		);
	if(send_command($socket,$login)){
		return true;
	} else {
		die("\nConnection Failure\n");
	}
}
function send_message($socket,$room,$message){
	$message_structure = array(
				'op' => 'act',
				'rm' => $room,
				'ex' => array(
					'message' => strval($message)
				)
			);
	return send_command($socket,$message_structure);
}
function main_loop(){
	$socket = init_socket();
	while (!feof($socket)){
		while (($message = fgets($socket)) !== false) {
			$decoded_message = decode_message($message);
			if(isset($decoded_message['op'])){
				switch($decoded_message['op']){
					case 'welcome':
						on_welcome($socket);
					break;
					case 'join':
						send_message($socket,'48557f95','HE HE');
					break;
					case 'act':
						send_message($socket,'48557f95','HO HO');
					break;					
					default:
						var_dump($message);
					break;
				}
			}
		}

	}
}
main_loop();
