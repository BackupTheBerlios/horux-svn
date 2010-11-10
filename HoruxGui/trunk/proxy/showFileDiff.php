<pre>


<?php
require_once 'XML/RPC.php';

if(isset($_GET['file'])) {
    $file_content = file_get_contents("../".$_GET['file']);

    $params = array(new XML_RPC_Value($_GET['file'], 'string'),new XML_RPC_Value($file_content, 'string'));
    $msg = new XML_RPC_Message('fileDiff', $params);

    $cli = new XML_RPC_Client('/update/xml_rpc_update.php', 'http://www.horux.ch');
   // $cli->setDebug(1);
    $resp = $cli->send($msg);

    if (!$resp) {
        echo 'Communication error: ' . $cli->errstr;
    }

    if (!$resp->faultCode()) {
        $val = $resp->value();

        print_r( unserialize($val->scalarval()) );

    } else {
        /*
         * Display problems that have been gracefully cought and
         * reported by the xmlrpc.php script.
         */
        $error =  'Fault Code: ' . $resp->faultCode() . "<br/>";
        $error .= 'Fault Reason: ' . $resp->faultString() . "<br/>";
        echo $error;
    }

}

?>
</pre>
