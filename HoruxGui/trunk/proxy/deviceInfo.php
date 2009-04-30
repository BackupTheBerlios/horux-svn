<?php

	$app_type = "production";

    $id = $port = "";

    if(isset($_GET['id']))
        $id = $_GET['id'];
	else
	{
		echo "";
		return;
	}

    if(isset($_GET['port']))
        $port = $_GET['port'];
	else
	{
		echo "";
		return;
	}

	$result = "";
	$content_error = "";

	if($app_type != 'demo')
	{
		
		require_once( 'XML/RPC.php' );
		

	    $client = new XML_RPC_Client("RPC2", "localhost", $port);
	    $msg = new XML_RPC_Message("horux.getSystemInfo");
	    @$response = $client->send($msg);
	
	    if($response)
	    {
	            if (!$response->faultCode()) 
	            {
	                    $v = $response->value();
	
	                    $result = html_entity_decode( $v->scalarval() );
	            } 
	            else 
	            {
	                    $content_error = "ERROR - ";
	                    $content_error .= "Code: " . $response->faultCode() . " Reason '" . $response->faultString() . "'<br/>";
	            };			
	    }
    
	}
	else
		$result = file_get_contents("../demo.xml");
    
    if($content_error != "")
    {
    	    
    	echo  $content_error;
    }
    else
    {
    	if($result != "")
    	{
    		$xml = simplexml_load_string($result);

   			foreach ($xml->devices as $devices) 
   			{
  				
	   			foreach ($devices as $device) 
	   			{
	   				if((string)$device['id'] == $id) 
	   				{
	   					
	   					$html = '<table class="adminlist" >';
						$html .= '<thead><tr><th>Parameter</th><th>Value</th></tr></thead>';
    					$html .= '<tfoot><tr><th colspan="6">&nbsp;</th></tr></tfoot><tbody>';							   					
	   					foreach($device as $p)
	   					{
	   					
	   					$html .= '<tr>';
	   					$html .= '<th>';
	   					$html .= $p->getName();
	   					$html .= '</th>';
	   					$html .= '<td>';
	   					if(($p == '1' or $p=='0') && $p->getName() != 'address')
	   						$html .= $p=='1' ?'true':'false';
	   					else
	   						$html .= $p;
	   					$html .= '</td>';
	   					$html .= '</tr>';
	   					
	   					}
	   					
	   					$html .= '</tbody></table>';
	   					
	   					echo $html;
	   					return;
	   				}
	   			}   				
   			}
				
    		echo "";
    	}	
    }	
?>