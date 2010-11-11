<?php

include("xmlrpc/lib/xmlrpc.inc");

class HoruxGuiUpdate {

    protected $out = array();


    public function compareFiles() {
        
        $this->listHoruxGuiFile(".");

        $client = new xmlrpc_client("http://www.horux.ch/update/xml_rpc_update.php");

        $message = new xmlrpcmsg("compareVersion", array(new xmlrpcval(serialize($this->out), 'string')));
        $resp = $client->send($message);

        if( $resp->faultCode() ) {
            return 'KO. Error: '.$resp->faultString();
        } else {

            $value = $resp->value();
            return unserialize($value->scalarval());
        }
    }


    public function updateFile($file) {

        $this->listHoruxGuiFile(".");

        $client = new xmlrpc_client("http://www.horux.ch/update/xml_rpc_update.php");

        $message = new xmlrpcmsg("getFile", array(new xmlrpcval($file, 'string')));
        $resp = $client->send($message);

        if( $resp->faultCode() ) {
            return false;
        } else {

            $value = $resp->value();
            return $value->scalarval();
        }
    }

    /**
     * List the Horux Gui File. This function don't check only the Horux Gui
     * files and not the extensions.
     * @param <type> $path
     * @return <type>
     */
    protected function listHoruxGuiFile($path='.') {

	$i = 0;
	$path = substr($path, strlen($path) - 1, strlen($path)) !== '/' ? $path.'/' : $path;
	if (!is_dir($path) || !$handle = @dir($path)) {
		trigger_error('\''.$path.'\' doesn\'t exists or is not a valid directory', E_USER_ERROR);
	} else {
		while ($entry = $handle->read()) {
			if (($entry !== "." && $entry !== ".." && $entry != ".svn" )) {
				$path_to_entry = $path.$entry;
				if ($entry !== '.' && $entry !== '..' && @is_dir($path_to_entry)) {
                                    if($path_to_entry != './assets' && 
                                       $path_to_entry != './protected/runtime' &&
                                       $path_to_entry != './protected/pages/hardware/device' &&
                                       $path_to_entry != './protected/pages/components' && 
                                            $path_to_entry != './tmp'
                                            ) {
                                            $this->out[$path_to_entry] = "";
                                            $this->listHoruxGuiFile($path_to_entry);
                                    }
				} else {
                                  if($entry != "application_p.xml")
					$this->out[$path_to_entry] = md5(file_get_contents($path_to_entry));
				}
			}
		}
	}
    }

}

?>
