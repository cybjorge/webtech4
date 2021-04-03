<?php
//Smotanka finest code
$curl=curl_init();
$home="https://github.com/apps4webte";
$raw="https://raw.githubusercontent.com";
curl_setopt($curl, CURLOPT_URL,$home."/curldata2021");
curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
$html=curl_exec($curl);
curl_close($curl);

$doc = new DOMDocument();
$doc->loadHTML($html);

$selector = new DOMXPath($doc);
$result=$doc->getElementsByTagName('a');



foreach($result as $node) {
    $url=$node->getAttribute('href');
    $url=str_replace ( "/blob", "", $url );
    if(str_ends_with ( $url , ".csv" ))
        echo "<a href=".$raw.$url.">".$home.$url."</a><br>";
}
