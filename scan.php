<?php
require('auth.php');
header('Expires: 0');
header('Content-type: application/json');

$xml = file_get_contents(el_s3_getTemporaryLink(ACCESSKEY, SECRETKEY, S3BUCKET, '/'), false);

$xml = new SimpleXMLElement($xml);
    foreach ($xml->Contents as $Contents) {
	$f = (array) $Contents->Key;
	$name = $f[0];
	$size = (array) $Contents->Size;
	$files[] = array("name" => $name, "type" => "file", "path" => el_s3_getTemporaryLink(ACCESSKEY, SECRETKEY, S3BUCKET, $f[0]), "size" =>$size[0]);
	$c++;
}

$dir = 'S3 Browser';
$txt = json_encode(array("name" => $dir, "type" => "folder", "path" => $dir, "filenum" => $c, "items" => $files));
echo $txt;
