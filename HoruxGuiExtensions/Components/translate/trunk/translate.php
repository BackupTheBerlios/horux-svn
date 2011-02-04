<?php


Prado::using("horux.pages.components.translate.gtranslate.GTranslate");

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

    public function selectionChangedFiltre($sender, $param)
    {
        $this->displayLanguage();
    }

    public function selectionChangedLockUnlock($sender, $param)
    {
        $this->displayLanguage();
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

        // translate Horux text
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
                    $lock = isset($unit['lock']) ? (string)$unit['lock'] : false;
                    $source[] = (string)$unit->source;
                    $text[] = array('id'=>(string)$unit['id'],
                                    'source'=>htmlentities((string)$unit->source),
                                    'text'=>(string)$unit->target,
                                    'lock'=>$lock);
                }
                $lastId++;
                foreach($this->strings as $string)
                {
                    if(!in_array($string,$source))
                    {
                        $text[] = array('id'=>$lastId++,
                                        'source'=>htmlentities($string),
                                        'text'=>"",
                                        'lock'=>false);
                    }
                }

                $text_filter = array();

                if($this->filter->getSelectedValue() == "notexist")
                {
                    for($i=0; $i< count($text);$i++)
                    {
                        if(!in_array(html_entity_decode($text[$i]['source']), $this->strings ) )
                        {
                           $text_filter[] = $text[$i];
                        }
                    }

                    $text = $text_filter;
                }

               
                if($this->filter->getSelectedValue() == "nottranslate")
                {
                    for($i=0; $i< count($text);$i++)
                    {
                        if($text[$i]['text'] == '')
                        {
                            $text_filter[] = $text[$i];
                        }

                    }

                    $text = $text_filter;
                }

                $text_filter = array();
                
                if($this->lockUnlock->getSelectedValue() == "lock")
                {
                    for($i=0; $i< count($text);$i++)
                    {
                        if($text[$i]['lock'] )
                        {
                           $text_filter[] = $text[$i];
                        }
                    }

                    $text = $text_filter;
                }


                if($this->lockUnlock->getSelectedValue() == "unlock")
                {
                    for($i=0; $i< count($text);$i++)
                    {
                        if( !$text[$i]['lock'] )
                        {
                            $text_filter[] = $text[$i];
                        }

                    }

                    $text = $text_filter;
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
                                    'text'=>(string)$unit->target,
                                    'lock'=>false);
                }
            }


            $this->DataGrid->reset();
            return $text;

        } // translate theme text
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
                            $lock = isset($unit['lock']) ? (string)$unit['lock'] : false;
                            $source[] = (string)$unit->source;
                            $text[] = array('id'=>(string)$unit['id'],
                                            'source'=>htmlentities((string)$unit->source),
                                            'text'=>(string)$unit->target,
                                            'lock'=>$lock);
                        }


                        $lastId++;
                        foreach($this->strings as $string)
                        {
                            if(!in_array($string,$source))
                            {
                                $text[] = array('id'=>$lastId++,
                                                'source'=>htmlentities($string),
                                                'text'=>"",
                                                'lock'=>false);
                            }
                        }

                        $text_filter = array();

                        if($this->filter->getSelectedValue() == "notexist")
                        {
                            for($i=0; $i< count($text);$i++)
                            {
                                if(!in_array(html_entity_decode($text[$i]['source']), $this->strings ) )
                                {
                                   $text_filter[] = $text[$i];
                                }
                            }

                            $text = $text_filter;
                        }


                        if($this->filter->getSelectedValue() == "nottranslate")
                        {
                            for($i=0; $i< count($text);$i++)
                            {
                                if($text[$i]['text'] == '')
                                {
                                    $text_filter[] = $text[$i];
                                }

                            }

                            $text = $text_filter;
                        }

                        $text_filter = array();

                        if($this->lockUnlock->getSelectedValue() == "lock")
                        {
                            for($i=0; $i< count($text);$i++)
                            {
                                if($text[$i]['lock'] )
                                {
                                   $text_filter[] = $text[$i];
                                }
                            }

                            $text = $text_filter;
                        }


                        if($this->lockUnlock->getSelectedValue() == "unlock")
                        {
                            for($i=0; $i< count($text);$i++)
                            {
                                if( !$text[$i]['lock'] )
                                {
                                    $text_filter[] = $text[$i];
                                }

                            }

                            $text = $text_filter;
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
                                            'text'=>(string)$unit->target,
                                            'lock'=>false);
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
                            $lock = isset($unit['lock']) ? (string)$unit['lock'] : false;
                            $source[] = (string)$unit->source;
                            $text[] = array('id'=>(string)$unit['id'],
                                            'source'=>htmlentities((string)$unit->source),
                                            'text'=>(string)$unit->target,
                                            'lock'=>$lock);
                        }


                        $lastId++;
                        foreach($this->strings as $string)
                        {
                            if(!in_array($string,$source))
                            {
                                $text[] = array('id'=>$lastId++,
                                                'source'=>htmlentities($string),
                                                'text'=>"",
                                                'lock'=>false);
                            }
                        }

                        $text_filter = array();

                        if($this->filter->getSelectedValue() == "notexist")
                        {
                            for($i=0; $i< count($text);$i++)
                            {
                                if(!in_array(html_entity_decode($text[$i]['source']), $this->strings ) )
                                {
                                   $text_filter[] = $text[$i];
                                }
                            }

                            $text = $text_filter;
                        }


                        if($this->filter->getSelectedValue() == "nottranslate")
                        {
                            for($i=0; $i< count($text);$i++)
                            {
                                if($text[$i]['text'] == '')
                                {
                                    $text_filter[] = $text[$i];
                                }

                            }

                            $text = $text_filter;
                        }

                        $text_filter = array();

                        if($this->lockUnlock->getSelectedValue() == "lock")
                        {
                            for($i=0; $i< count($text);$i++)
                            {
                                if($text[$i]['lock'] )
                                {
                                   $text_filter[] = $text[$i];
                                }
                            }

                            $text = $text_filter;
                        }


                        if($this->lockUnlock->getSelectedValue() == "unlock")
                        {
                            for($i=0; $i< count($text);$i++)
                            {
                                if( !$text[$i]['lock'] )
                                {
                                    $text_filter[] = $text[$i];
                                }

                            }

                            $text = $text_filter;
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
                                            'text'=>(string)$unit->target,
                                            'lock'=>false);
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
            
            $currentId = $unit->getAttribute('id');

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
            else
            {
                if($param->Item->DataItem['text'] == "")
                {
                    $item->textColumn->setBackColor("green");
                }
            }

        }



        if($item->ItemType==='Item' ||
            $item->ItemType==='AlternatingItem' ||
            $item->ItemType==='EditItem')
        {
            if($param->Item->DataItem['lock'])
            {             
                $item->DeleteColumn->Button->enabled = false;
                $item->GoogleTranslateColumn->Button->enabled = false;
                $item->EditColumn->enabled = false;
            }
            else
            {
                if($item->textColumn->getBackColor() != 'green' && $item->textColumn->getBackColor() != 'red')
                    $item->textColumn->setBackColor("orange");
                // add an aleart dialog to delete buttons
                $msg = Prado::Localize('Are you sure?');
                $item->DeleteColumn->Button->Attributes->onclick=
                    "if(!confirm('$msg')) return false; else return true;";
            }
        }
    }

    public function setLocked($sender,$param)
    {
        $lang = $this->language->getSelectedValue();
        $module = $this->module->getSelectedValue();
        $extension = $this->extension->getSelectedValue();

        $id = $sender->Text;


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
            
            $currentId = $unit->getAttribute('id');

            if($currentId == $id)
            {
                if($unit->hasAttribute('lock'))
                {
                  if((bool)$unit->getAttribute('lock'))
                    $unit->setAttribute('lock', "0");
                  else
                    $unit->setAttribute('lock', "1");

                }
                else
                {
                    $unit->setAttribute('lock', "1");
                }

                $found = true;
            }

            
            //finished searching
            if($found) break;
        }

        if($found)
        {
            $fileNode = $xpath->query('//file')->item(0);
            $fileNode->setAttribute('date', @date('Y-m-d\TH:i:s\Z'));

            $dom->save($file);

            $this->DataGrid->EditItemIndex=-1;
            $this->DataGrid->DataSource=$this->getData();
            $this->DataGrid->dataBind();
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
        if($extension == "0") $extension = "messages";

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

            preg_match_all("/<com:TTranslate Catalogue=\"".$extension."\" Text=\"(.+?)\"/i",file_get_contents($file),$matches);
            if (sizeof($matches[1])>0)
            {
                $strings = array_merge($strings,$matches[1]);
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

            if($this->google->getChecked())
            {
                $translateGoogleText = $this->traduction_google_v1($string);
            }
            else
                $translateGoogleText = false;

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
            if (is_dir($dir.'/'.$file) && $file != '.' && $file != '..' && $file != 'components' && $file != 'device' && $file != '.svn' && $file != 'swift')
            {
                $this->files_scan($dir.'/'.$file);
            }
            elseif (is_file($dir.'/'.$file) && is_readable($dir.'/'.$file) && ($file == 'install.xml' || strstr($file,'.tpl') || strstr($file,'.php') || strstr($file,'.page')))
            {
                $this->scanned_files[] = $dir.'/'.$file;
            }
        }
    }

    public function googleTranslate($sender,$param)
    {
        $item=$param->Item;

        $text = $this->traduction_google_v1($item->sourceColumn->Text);

        $this->updateText(
            $this->DataGrid->DataKeys[$item->ItemIndex], // id
            $item->sourceColumn->Text, // source
            $text // text
        );
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->getData();
        $this->DataGrid->dataBind();
    }

    protected function traduction_google_v1($mot_a_traduire)
    {

        $translate_string = "Das ist wunderschön";
        try{
            $gt = new Gtranslate;
            $gt->setRequestType('curl');
            $function = "en_to_".substr($this->language->getSelectedValue(),-2,2);
            return $gt->$function($mot_a_traduire);

        }
        catch (GTranslateException $ge)
        {
            return "";
        }
    }
}

?>