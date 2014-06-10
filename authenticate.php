<?php

$url =			'https://getpocket.com/v3/oauth/request';
$consumer_key =	urldecode($_GET['consumer_key']);
$callback_uri =	urldecode($_GET['callback_uri']);

echo $url.'<br/>';
echo $consumer_key.'<br/>';
echo $callback_uri.'<br/>';

$data = array(
	'consumer_key' => $consumer_key,
	'redirect_uri' => $callback_uri
);
$query = http_build_query($data);
$options = array(
	'http' => array(
		'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
				"Content-Length: ".strlen($query)."\r\n".
				"User-Agent:MyAgent/1.0\r\n",
		'method'  => 'POST',
		'content' => $query
	)
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
// our $result contains our request token
$code = explode('=',$result);
$request_token = $code[1];

// now we need to redirect the user to pocket
header("Location: https://getpocket.com/auth/authorize?request_token=$request_token&redirect_uri=$callback_uri?request_token=$request_token");
