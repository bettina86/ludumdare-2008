<?php

// http://upskill.co.in/content/how-convert-simplexml-object-array-php
function xml2array($xml) {
	$arr = array();

	foreach ($xml->children() as $r) {
		$t = array();

		if (count($r->children()) == 0) {
			$arr[$r->getName()] = strval($r);
		}
		else {
			$arr[$r->getName()][] = xml2array($r);
		} 
	}
	return $arr;
}


echo "XML Grab\n";
{
	$url = "http://steamcommunity.com/groups/ludum/memberslistxml/?xml=1";
	$xml = simplexml_load_file($url);
	$arr = xml2array($xml);
	
	print_r($arr['groupDetails']);
	
//	print_r( $xml->children()['groupDetails'] );
	
	

}

require "simple_html_dom.php";

echo "HTML Grab\n";
{
	$html = file_get_html( "http://store.steampowered.com/curator/537829/" );
	
	echo "Followers: " . $html->find('.num_followers', 0)->plaintext . "\n";
}

//echo "HTTP Grab\n";
//{
//	$response = http_get( "http://store.steampowered.com/curator/537829/", array("timeout"=>2), $info );
//	
//	preg_match('/<div >(.*?)<\/div>/s', $response, $matches);
//	
//	print_r($matches);
//}

?>