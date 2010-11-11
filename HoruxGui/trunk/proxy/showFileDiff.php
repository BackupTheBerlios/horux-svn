<pre>


<?php
include("../xmlrpc/lib/xmlrpc.inc");

if(isset($_GET['file'])) {
    $file_content = file_get_contents("../".$_GET['file']);

   $msg = new xmlrpcmsg("fileDiff", array(new xmlrpcval($_GET['file'], 'string'), new xmlrpcval($file_content, 'string')));

    $cli = new xmlrpc_client("http://www.horux.ch/update/xml_rpc_update.php");
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
