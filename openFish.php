<?php
//>=|
function getProxyList()
{
    //Unsecurely get the proxy list
    $proxyHtml = file_get_contents("https://www.sslproxies.org/");
    // //table[@id="proxylisttable"]/tbody//td[1]
    $d = new DOMDocument();
    $libxml_previous_state = libxml_use_internal_errors(true);
    $d->loadHTML($proxyHtml);
    libxml_clear_errors();
    libxml_use_internal_errors($libxml_previous_state);
    $xpath = new DOMXPath($d);
    $ipNodes = $xpath->query("//table[@id=\"proxylisttable\"]/tbody//td[1]"); //Ips
    $portNodes = $xpath->query("//table[@id=\"proxylisttable\"]/tbody//td[2]"); //Ports
    $countryNodes = $xpath->query("//table[@id=\"proxylisttable\"]/tbody//td[3]"); //Country Code

    $length = $portNodes->length;
    $validLength = ($ipNodes->length == $length) && ($length == $countryNodes->length);
    if (!$validLength)
    {
        throw new Exception("Proxy list error.");
    }
    $proxies = array();
    for ($i=0; $i<$length; $i++)
    {
        $proxy = array();
        $proxy["ip"] = $ipNodes->item($i)->textContent;
        $proxy["port"] = $portNodes->item($i)->textContent;
        $proxy["country"] = $countryNodes->item($i)->textContent;
        $proxies[] = $proxy;
    }
    return $proxies;
}

function curlSetProxy(&$ch, $proxy)
{
    curl_setopt($ch, CURLOPT_PROXY, $proxy["ip"].":".$proxy["port"]);
}

function createCurl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    return $ch;
}

$proxyBank = getProxyList();
$selectedProxy = $proxyBank[array_rand($proxyBank)];
$ch = createCurl("https://webhook.site/ba9d2397-fc94-4afa-b47f-91cbcff54880");
curlSetProxy($ch, $selectedProxy);
var_dump($selectedProxy);
echo "...";
var_dump(curl_exec($ch));

?>