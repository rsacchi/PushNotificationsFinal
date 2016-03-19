<?php

// Put your device token here (without spaces):
$deviceToken = '961966413cfb324e6c01b9a3aeb8cd9434ee80690389e785ff189bf512f3b5bc';

// Put your private key's passphrase here:
$passphrase = '';

$message = $argv[1];

if (!$message)
    exit('Erro: especifique uma mensagem.' . "\n");

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
$fp = stream_socket_client(
  'ssl://gateway.sandbox.push.apple.com:2195', $err,
  $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
  exit("Falha ao conectar: $err $errstr" . PHP_EOL);

echo 'Conectado à APNS' . PHP_EOL;

// Create the payload body
$body['aps'] = array(
  'alert' => $message,
  'sound' => 'default',
  'background_color' => '1',
);

// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
  echo 'A mensagem não pôde ser enviada' . PHP_EOL;
else
  echo 'Mensagem enviada com sucesso!' . PHP_EOL;

// Close the connection to the server
fclose($fp);
