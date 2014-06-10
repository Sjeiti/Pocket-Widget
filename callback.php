<?php
//require_once('config.php');
/* Now we have to convert our request token into an access token */
// we set up the callback_uri to include the request_token,
// so let's get that
require_once('../../../wp-load.php');
//

$request_token = $_GET['request_token'];

$url = 'https://getpocket.com/v3/oauth/authorize';
$data = array(
	'consumer_key' => get_option('pocketwidget_consumer_key'),
	'code' => $request_token
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

// our $result contains our access token
parse_str($result, $output);
if(isset($output['access_token'])){
	update_option('pocketwidget_access_token',$output['access_token']);
} else{
	echo "Something went wrong. :( ";
}
header('Location: '.get_admin_url().'options-general.php?page=pocketwidget');