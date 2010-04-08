<?php

$urlPath = "";
$soapPassword = "";
$soapUsername = "";


function checkStatus($file)
{
    global $urlPath;

    $xml = simplexml_load_file($file);

    $lastupdate = explode(" / ", $xml->lastUpdate);
    $lastDate = explode(".",$lastupdate[1]);
    $lastTime = explode(":",$lastupdate[0]);

    $lastDateTime = mktime($lastTime[0], $lastTime[1], $lastTime[2], $lastDate[1], $lastDate[0],$lastDate[2]);
    $currentDateTime = mktime();

    // if we more thant 30 minutes, we guess that the system si down
    if( $currentDateTime - $lastDateTime > /*1800*/600 )
    {
        // Send a alarms
        $soapClient = new SoapClient("http://{$_SERVER['SERVER_NAME']}/{$urlPath}index.php?soap=notification.wsdl&password=$soapPassword&username=$soapUsername");

        $error = 0;
        try
        {
            $param = array('param'=>array(array('key'=>'type', 'value'=>'ALARM'),array('key'=>'code', 'value'=>'900'),array('key'=>'object', 'value'=>'0')));

            $info = $soapClient->__call("sendMail", $param);
        }
        catch (SoapFault $fault)
        {
            $error = 1;
            print("
            alert('Sorry, blah returned the following ERROR: ".$fault->faultcode."-".$fault->faultstring.". We will now take you back to our home page.');
            window.location = 'main.php';
            ");
        }
    }


}


$path = "../tmp";

$dir_handle = @opendir($path) or die("Unable to open $path");

while ($file = readdir($dir_handle))
{
    if(substr($file, 0, strlen("system_status_")) == "system_status_")
    {
        checkStatus($path."/".$file);
    }
}

closedir($dir_handle);


?>
