<?php
/**
* @version      $Id$
* @package      Horux
* @subpackage   Horux
* @copyright    Copyright (C) 2008  Letux. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Horus is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class translate extends PageList
{
    private $scanned_files = array();
    private $strings = array();

    public function onLoad($param)
    {
        parent::onLoad($param);

        $asset = $this->Application->getAssetManager();
        $url = $asset->publishFilePath('./protected/pages/components/translate/assets/icon-48-translate.png');		


        if(!$this->IsPostBack)
        {          
	    $this->language->DataTextField = "Text";
	    $this->language->DataValueField = "Value";
            $this->language->DataSource=$this->getLanguage();
            $this->language->dataBind();      

            $this->extension->DataTextField = "Text";
            $this->extension->DataValueField = "Value";               

        }  
     }	

    protected function getLanguage()
    {
            $d = array();
            $d[] = array("Text"=>Prado::localize("-- Select a language--"),"Value"=>"0");

            $list = scandir(".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."messages");
            $i=1;
            foreach($list as $l)
            {
                    if($l != "." && $l!=".." && $l!=".svn")
                    {
                            $doc=new TXmlDocument();
                            $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$l.DIRECTORY_SEPARATOR.'install.xml');

                            $name =  $doc->getElementByTagName('name');

                    $d[$i]['Value'] = ".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."messages".DIRECTORY_SEPARATOR.$l;
                    $d[$i++]['Text'] = $name->getValue();
                    }
            }	

            return $d;
    }

    public function selectionChangedModule($sender, $param)
    {
            $value = $sender->getSelectedValue();
            $d = array();
            $d[] = array("Text"=>Prado::localize("-- Select a Module--"),"Value"=>"0");


            if($value == "." || $value == "0")
            { 
                    $this->extension->DataSource=$d;
                    $this->extension->dataBind(); 
                    $this->displayLanguage();
                    return;
            }

            $list = array();

            if($value == "themes")
            {
              $list = scandir(".".DIRECTORY_SEPARATOR."themes");
            }
            else
            {
                $value = str_replace(".", DIRECTORY_SEPARATOR, $value);

                  $list = scandir(".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."pages".DIRECTORY_SEPARATOR.$value);
            }


            $i=1;
            foreach($list as $l)
            {

                    if($l != "." && $l!=".." && $l!=".svn")
                    {
                            $doc=new TXmlDocument();

                            if($value == "themes")
                              $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$l.DIRECTORY_SEPARATOR.'install.xml');
                            else
                              $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR.$l.DIRECTORY_SEPARATOR.'install.xml');

                            $name =  $doc->getElementByTagName('name');
                    $d[$i]['Value'] = $l;
                    $d[$i++]['Text'] = $name->getValue();
                    }
            }			

            $this->extension->DataSource=$d;
            $this->extension->dataBind();

            $this->displayLanguage();

    }

    public function selectionChangedExtension($sender, $param)
    {
            $this->displayLanguage();		
    }

    public function selectionChangedLanguage($sender, $param)
    {
            $this->displayLanguage();
    }

    protected function displayLanguage()
    {
            $this->DataGrid->EditItemIndex=-1;
            $this->DataGrid->DataSource=$this->getData();
            $this->DataGrid->dataBind();	
    }

    protected function getData()
    {
            $text = array();

            if($this->language->getSelectedValue()  == "0")
            {
                    $this->DataGrid->reset(); 
                    return $text;
            }

            if($this->module->getSelectedValue() == "0")
            {
                    $this->DataGrid->reset(); 
                    return $text;		
            }

            if($this->module->getSelectedValue() == ".")
            {

                    $lang = $this->language->getSelectedValue();


                    if(file_exists($lang.DIRECTORY_SEPARATOR.'messages.xml'))
                    {
                            $this->strings = $this->getScan(".".DIRECTORY_SEPARATOR."protected");

                            $xml = simplexml_load_file($lang.DIRECTORY_SEPARATOR.'messages.xml');
    
                            $translationUnit = $xml->xpath('//trans-unit');
    
                            $lastId = 0;

                            $source = array();
                            foreach($translationUnit as $unit)
                            {
                                    $lastId = (string)$unit['id'];
                                    $source[] = (string)$unit->source;
                                    $text[] = array('id'=>(string)$unit['id'],
                                                                    'source'=>htmlentities((string)$unit->source), 
                                                                    'text'=>(string)$unit->target);
                            }
                            $lastId++;
                            foreach($this->strings as $string)
                            {
                                    if(!in_array($string,$source))
                                    {
                                        $text[] = array('id'=>$lastId++,
                                                                    'source'=>htmlentities($string), 
                                                                    'text'=>"");
                                    }
                            }
    
                    }
                    else
                    {
                            $this->build(".".DIRECTORY_SEPARATOR."protected",
                                                      $lang.DIRECTORY_SEPARATOR.'messages.xml');

                            $xml = simplexml_load_file($lang.DIRECTORY_SEPARATOR.'messages.xml');
    
                            $translationUnit = $xml->xpath('//trans-unit');
    
                            foreach($translationUnit as $unit)
                            {
                                    $text[] = array('id'=>(string)$unit['id'],
                                                                    'source'=>htmlentities((string)$unit->source), 
                                                                    'text'=>(string)$unit->target);
                            }	
                    }


                    $this->DataGrid->reset(); 
                    return $text;

            }
            else if($this->module->getSelectedValue() == "themes")
            {
                    if($this->extension->getSelectedValue() == "0")
                    {
                            $this->DataGrid->reset(); 
                            return $text;
                    }
                    else
                    {
                            $lang = $this->language->getSelectedValue();
                            $module = $this->module->getSelectedValue();
                            $extension = $this->extension->getSelectedValue();

                            if($extension != "")
                            {
                                    
                                    if(file_exists($lang.DIRECTORY_SEPARATOR.$extension.'.xml'))
                                    {

                                            $this->strings = $this->getScan(".".DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$extension);

                                            $xml = simplexml_load_file($lang.DIRECTORY_SEPARATOR.$extension.'.xml');
                    
                                            $translationUnit = $xml->xpath('//trans-unit');

                                            $source = array();
                                            $lastId	= 0;		
                                            foreach($translationUnit as $unit)
                                            {
                                                    $lastId = (string)$unit['id'];
                                                    $source[] = (string)$unit->source;
                                                    $text[] = array('id'=>(string)$unit['id'],
                                                                                    'source'=>htmlentities((string)$unit->source), 
                                                                                    'text'=>(string)$unit->target);
                                            }

                        
                                            $lastId++;
                                            foreach($this->strings as $string)
                                            {
                                                    if(!in_array($string,$source))
                                                    {
                                                      $text[] = array('id'=>$lastId++,
                                                                                    'source'=>htmlentities($string), 
                                                                                    'text'=>"");
                                                    }
                                            }

                                    }
                                    else
                                    {
                                            $this->build(	".".DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$extension,
                                                                            $lang.DIRECTORY_SEPARATOR.$extension.'.xml');

                                            $xml = simplexml_load_file($lang.DIRECTORY_SEPARATOR.$extension.'.xml');
                    
                                            $translationUnit = $xml->xpath('//trans-unit');
                    
                                            foreach($translationUnit as $unit)
                                            {
                                                    $text[] = array('id'=>(string)$unit['id'],
                                                                                    'source'=>(string)$unit->source, 
                                                                                    'text'=>(string)$unit->target);
                                            }	

                                    }
                            }
                            $this->DataGrid->reset(); 
                            return $text;
                    }
            } 
            else
            {
                    if($this->extension->getSelectedValue() == "0")
                    {
                            $this->DataGrid->reset(); 
                            return $text;
                    }
                    else
                    {
                            $lang = $this->language->getSelectedValue();
                            $module = $this->module->getSelectedValue();
                            $extension = $this->extension->getSelectedValue();
                            $extension = str_replace(".", DIRECTORY_SEPARATOR, $extension);
                            $module = str_replace(".", DIRECTORY_SEPARATOR, $module);

                            if($extension != "")
                            {
                                    
                                    if(file_exists($lang.DIRECTORY_SEPARATOR.$extension.'.xml'))
                                    {

                                            $this->strings = $this->getScan(".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."pages".DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.$extension);

                                            $xml = simplexml_load_file($lang.DIRECTORY_SEPARATOR.$extension.'.xml');
                    
                                            $translationUnit = $xml->xpath('//trans-unit');

                                            $source = array();
                                            $lastId	= 0;		
                                            foreach($translationUnit as $unit)
                                            {
                                                    $lastId = (string)$unit['id'];
                                                    $source[] = (string)$unit->source;
                                                    $text[] = array('id'=>(string)$unit['id'],
                                                                                    'source'=>htmlentities((string)$unit->source), 
                                                                                    'text'=>(string)$unit->target);
                                            }

                        
                                            $lastId++;
                                            foreach($this->strings as $string)
                                            {
                                                    if(!in_array($string,$source))
                                                    {
                                                      $text[] = array('id'=>$lastId++,
                                                                                    'source'=>htmlentities($string), 
                                                                                    'text'=>"");
                                                    }
                                            }

                                    }
                                    else
                                    {
                                            $this->build(	".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."pages".DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.$extension,
                                                                            $lang.DIRECTORY_SEPARATOR.$extension.'.xml');

                                            $xml = simplexml_load_file($lang.DIRECTORY_SEPARATOR.$extension.'.xml');
                    
                                            $translationUnit = $xml->xpath('//trans-unit');
                    
                                            foreach($translationUnit as $unit)
                                            {
                                                    $text[] = array('id'=>(string)$unit['id'],
                                                                                    'source'=>(string)$unit->source, 
                                                                                    'text'=>(string)$unit->target);
                                            }	

                                    }
                            }
                            $this->DataGrid->reset(); 
                            return $text;

                    }

            }

    }

    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->DataGrid->DataSource=$this->getData();
        $this->DataGrid->dataBind();
    }

    public function saveItem($sender,$param)
    {
        $item=$param->Item;

        $this->updateText(
            $this->DataGrid->DataKeys[$item->ItemIndex], // id
            $item->sourceColumn->Text, // source
            $item->textColumn->TextBox->Text // text
        );
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->getData();
        $this->DataGrid->dataBind();
    }


    protected function updateText($id,$text,$target)
    {

        $lang = $this->language->getSelectedValue();
        $module = $this->module->getSelectedValue();
        $extension = $this->extension->getSelectedValue();

        $file = "";

        if($module == ".")
                $file = $lang.DIRECTORY_SEPARATOR.'messages.xml';
        else
                $file = $lang.DIRECTORY_SEPARATOR.$extension.'.xml';

        $dom = DOMDocument::load($file);

        //find the body element
        $xpath = new DomXPath($dom);
        $units = $xpath->query('//trans-unit');



        $found = false;

        //for each of the existin units
        foreach($units as $unit)
        {
            $targetted = false;
            $currentId = 0;
            foreach($unit->attributes as $attribute)
                $currentId = $attribute->nodeValue;

            //in each unit, need to find the source, target and comment nodes
            //it will assume that the source is before the target.
            foreach($unit->childNodes as $node)
            {
                //source node
                if($node->nodeName == 'source'
                  && $currentId == $id)
                {
                    $node->nodeValue = $text;
                    $found = true;
                }

                //found source, get the target and notes
                if($found)
                {
                    //set the new translated string
                    if($node->nodeName == 'target')
                    {
                        $node->nodeValue = $target;
                        $targetted = true;
                    }
                }
            }

            //append a target
            if($found && !$targetted)
                $unit->appendChild($dom->createElement('target',$target));

            //finished searching
            if($found) break;
        }

        if(!$found)
        {
          $this->addNewText($text, $target);
          return true;
        }

        $fileNode = $xpath->query('//file')->item(0);
        $fileNode->setAttribute('date', @date('Y-m-d\TH:i:s\Z'));

        if($dom->save($file) >0)
            return true;

        return false;
   
    }

    public function addNewText($source, $text)
    {
        $lang = $this->language->getSelectedValue();
        $module = $this->module->getSelectedValue();
        $extension = $this->extension->getSelectedValue();

        $file = "";

        if($module == ".")
                $file = $lang.DIRECTORY_SEPARATOR.'messages.xml';
        else
                $file = $lang.DIRECTORY_SEPARATOR.$extension.'.xml';

        $xml = simplexml_load_file($file);
        $translationUnit = $xml->xpath('//trans-unit[last()]');
        $lastId = 0;

        foreach($translationUnit as $unit)
        {
            $lastId = $unit['id'];
        }
        $lastId +=1;

        $dom = DOMDocument::load($file);

        //find the body element
        $xpath = new DomXPath($dom);
        $body = $xpath->query('//body')->item(0);

        $unit = $dom->createElement('trans-unit');
        $unit->setAttribute('id',$lastId);

        $source = $dom->createElement('source', $source);
        $target = $dom->createElement('target', $text);

        $unit->appendChild($dom->createTextNode("\n"));
        $unit->appendChild($source);
        $unit->appendChild($dom->createTextNode("\n"));
        $unit->appendChild($target);
        $unit->appendChild($dom->createTextNode("\n"));

        $body->appendChild($dom->createTextNode("\n"));
        $body->appendChild($unit);
        $body->appendChild($dom->createTextNode("\n"));

        $fileNode = $xpath->query('//file')->item(0);
        $fileNode->setAttribute('date', @date('Y-m-d\TH:i:s\Z'));

        //save it and clear the cache for this variant
        $dom->save($file);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->getData();
        $this->DataGrid->dataBind();
    }

    public function deleteItem($sender,$param)
    {
        $item=$param->Item;
        $this->deletetText(html_entity_decode($item->sourceColumn->Text));

        $this->renumTexts();

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->getData();
        $this->DataGrid->dataBind();
    }  

    protected function renumTexts()
    {
        $lang = $this->language->getSelectedValue();
        $module = $this->module->getSelectedValue();
        $extension = $this->extension->getSelectedValue();

        $file = "";

        if($module == ".")
                $file = $lang.DIRECTORY_SEPARATOR.'messages.xml';
        else
                $file = $lang.DIRECTORY_SEPARATOR.$extension.'.xml';

    
        $dom = DOMDocument::load($file);


        //find the body element
        $xpath = new DomXPath($dom);
        $units = $xpath->query('//trans-unit');


        $lastId = 1;
        //for each of the existin units
        foreach($units as $unit)
        {
            $unit->setAttribute('id',$lastId++);
        }
        
        $dom->save($file);
        return true;
    }

    protected function deletetText($text)
    {
        $lang = $this->language->getSelectedValue();
        $module = $this->module->getSelectedValue();
        $extension = $this->extension->getSelectedValue();

        $file = "";

        if($module == ".")
                $file = $lang.DIRECTORY_SEPARATOR.'messages.xml';
        else
                $file = $lang.DIRECTORY_SEPARATOR.$extension.'.xml';

    
        $dom = DOMDocument::load($file);


        //find the body element
        $xpath = new DomXPath($dom);
        $units = $xpath->query('//trans-unit');

        //for each of the existin units
        foreach($units as $unit)
        {
            //in each unit, need to find the source, target and comment nodes
            //it will assume that the source is before the target.
            foreach($unit->childNodes as $node)
            {
                //source node
                if($node->nodeName == 'source'
                  && $node->firstChild->wholeText == $text)
                {

                    //we found it, remove and save the xml file.
                    $unit->parentNode->removeChild($unit);

                    $fileNode = $xpath->query('//file')->item(0);
                    $fileNode->setAttribute('date', @date('Y-m-d\TH:i:s\Z'));

                    if($dom->save($file) >0)
                        return true;
                    else 
                        return false;
                }
            }
        }

        return false;        
    }


    public function itemCreated($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            $item->textColumn->TextBox->TextMode = "MultiLine";
            $item->textColumn->TextBox->Columns=50;
            $item->textColumn->TextBox->Rows=5;
        }

        if($item->ItemType==='Item' || 
           $item->ItemType==='AlternatingItem')
        {
                if(!in_array(html_entity_decode($param->Item->DataItem['source']), $this->strings ) )
                {
                        $item->textColumn->setBackColor("red");
                }

                if($param->Item->DataItem['text'] == "")
                {
                        $item->textColumn->setBackColor("green");
                }

        }

        if($item->ItemType==='Item' || 
           $item->ItemType==='AlternatingItem' || 
           $item->ItemType==='EditItem')
        {
            // add an aleart dialog to delete buttons
            $msg = Prado::Localize('Are you sure?');
            $item->DeleteColumn->Button->Attributes->onclick=
                "if(!confirm('$msg')) return false;";
        }
    }

   public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->getData();
        $this->DataGrid->dataBind();
    }


	protected function build($dir, $output)
	{	
		$strings = $this->getScan($dir);

		unset($this->scanned_files);	
	
		$xml = $this->build_xml($strings);	

		file_put_contents($output ,$xml);
	}

	protected function getScan($dir)
	{
		//Searching for PHP or PAGE files
		$this->files_scan($dir);
		//Search for localization tags and functions in files
		$strings = array();		

                $module = $this->module->getSelectedValue();
                $extension = $this->extension->getSelectedValue();

		foreach ($this->scanned_files AS $file)
		{
			preg_match_all('/\<\%\[(.+?)\]\%\>/i',file_get_contents($file),$matches);
			if (sizeof($matches[1])>0)
			{				
				$strings = array_merge($strings,$matches[1]);	
			}
			
			preg_match_all("/localize\(\'(.+?)\'/i",file_get_contents($file),$matches);
			if (sizeof($matches[1])>0)
			{				
				$strings = array_merge($strings,$matches[1]);	
			}									

			preg_match_all("/localize\(\"(.+?)\"/i",file_get_contents($file),$matches);
			if (sizeof($matches[1])>0)
			{				
				$strings = array_merge($strings,$matches[1]);	
			}									

                        if(substr($file,-11) == "install.xml")
                        {
                          preg_match_all("/name=\"(.+?)\"/i",file_get_contents($file),$matches);
                          if (sizeof($matches[1])>0)
                          {				
                                  $strings = array_merge($strings,$matches[1]);	
                          }	  
                        }

                        if($module == "themes")
                        {
                          preg_match_all("/<com:TTranslate Catalogue=\"".$extension."\" Text=\"(.+?)\"/i",file_get_contents($file),$matches);
                          if (sizeof($matches[1])>0)
                          {				
                                  $strings = array_merge($strings,$matches[1]);	
                          }	  
                        }


		}
		foreach ($strings AS &$string)
		{
			$string = trim($string);
		}	

		return array_values(array_unique($strings));
	}


	protected function build_xml($strings)
	{

		$lang = substr($this->language->getSelectedValue(),-2);
		
		$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n"
			."\n".'<xliff version="1.0">'
 			."\n".'<file source-language="en" target-language="'.$lang.'" datatype="plaintext" original="messages" date="'.date('Y-m-d',time()).'T'.date('G:i:s',time()).'Z'.'" product-name="messages">'
  			."\n<body>";
		
		$i = 0;
		foreach ($strings AS $string)
		{
			$i++;
                        
                        $translateGoogleText = $this->traduction_google_v1($string);
                        
                        if(!$translateGoogleText)
                        {
                          $xml .= "\n<trans-unit id=\"{$i}\">\n   <source><![CDATA[{$string}]]></source>\n   <target></target>\n</trans-unit>";	
                        }
                        else
                        {
                          $xml .= "\n<trans-unit id=\"{$i}\">\n   <source><![CDATA[{$string}]]></source>\n   <target><![CDATA[{$translateGoogleText}]]></target>\n</trans-unit>"; 
                        }
		}
		
		$xml .= "\n</body>\n</file>\n</xliff>";	
		
		return $xml;
	}	

	protected function files_scan($dir)
	{
		$files = scandir($dir);		
		foreach ($files AS $file)
		{
			if (is_dir($dir.'/'.$file) && $file != '.' && $file != '..' && $file != 'components' && $file != 'device' && $file != '.svn')
			{
				$this->files_scan($dir.'/'.$file);	
			}
			elseif (is_file($dir.'/'.$file) && is_readable($dir.'/'.$file) && ($file == 'install.xml' || strstr($file,'.tpl') || strstr($file,'.php') || strstr($file,'.page')))
			{
				$this->scanned_files[] = $dir.'/'.$file;
			}	
		}				
	}

        function traduction_google_v1($mot_a_traduire)
        {
        
          $lg_lg = "en|".substr($this->language->getSelectedValue(),-2,2);

          $ch = curl_init();
          curl_setopt ($ch,CURLOPT_FRESH_CONNECT, 1);
          curl_setopt ($ch, CURLOPT_POST, 1);
          curl_setopt ($ch, CURLOPT_POSTFIELDS, 'text='.$mot_a_traduire.'&h1=en&ie=UTF8');
          curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
          curl_setopt ($ch, CURLOPT_REFERER, "http://www.google.fr");
          //curl_setopt ($ch, CURLOPT_USERAGENT, "Curl");
          curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.001 (windows; U; NT4.0; en-us) Gecko/25250101");
          curl_setopt($ch, CURLOPT_URL, "http://translate.google.com/translate_t?langpair=$lg_lg");
          $contenu=curl_exec($ch);
          curl_close($ch);
          //recup du mot traduit dans la variable $contenu par l'intermediaire du dom (31 ieme balise td , 2 ieme balise br et noeud suivant)
//echo $contenu; exit;
         $doc = @DOMDocument::loadHTML($contenu);
          /*echo"$contenu";

          //si dessus en cas de modif de la page
          //permet de retrouver le numero du div contenant le mot traduit
          
          $i=0;
          while($i<50)
          {
          echo"Div numero--->".$i;
          $liste_td = $doc -> getElementsByTagName('div') ->  item($i)->  nodeValue;
          echo($liste_td);
          echo"<br><br>";
          $i++;
          }
          */
          $isFind = false;
          $i=0;
          while(!$isFind && $i < 100)
          {
            $mot_traduit = $doc->getElementsByTagName('div')->item($i)/*->nodeValue*/;
            if($mot_traduit->hasAttribute('id') && $mot_traduit->getAttribute('id') == 'result_box' )
            {
               $mot_traduit = $mot_traduit->nodeValue."<br>";
               $isFind = true;
            }
            $i++;
          }

          if ($mot_traduit!==""){return utf8_decode($mot_traduit);}else{return FALSE;}

        }
}

?>