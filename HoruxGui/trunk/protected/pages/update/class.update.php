<?php


require_once 'XML/RPC.php';

class HoruxGuiUpdate {

    protected $out = array();


    public function compareFiles() {
        
        $this->listHoruxGuiFile(".");


        $params = array(new XML_RPC_Value(serialize($this->out), 'string'));
        $msg = new XML_RPC_Message('compareVersion', $params);

        $cli = new XML_RPC_Client('/update/xml_rpc_update.php', 'http://www.horux.ch');
        //$cli->setDebug(1);
        $resp = $cli->send($msg);

        if (!$resp) {
            return 'Communication error: ' . $cli->errstr;
        }

        if (!$resp->faultCode()) {
            $val = $resp->value();

            $diff = unserialize($val->scalarval());

            return $diff;

        } else {
            /*
             * Display problems that have been gracefully cought and
             * reported by the xmlrpc.php script.
             */
            $error =  'Fault Code: ' . $resp->faultCode() . "<br/>";
            $error .= 'Fault Reason: ' . $resp->faultString() . "<br/>";
            return $error;
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
