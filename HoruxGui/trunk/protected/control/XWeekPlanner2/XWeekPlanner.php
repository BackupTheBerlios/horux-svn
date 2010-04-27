<?php

/**
 * XWeekPlanner class file.
 *
 * @author Mauro Lewinzon <maurokun[at]gamil[dot]com>
 * @link http://www.enigmastudio.com.ar/
 * @copyright Copyright &copy; 2007 Enigma Studio
 * @license http://www.pradosoft.com/license/
 * @version 0.5
 * @package System.3rdParty.Enigma
 */


Prado::using('System.Web.UI.ActiveControls.TCallback');
Prado::using('System.Util.TDataFieldAccessor');

class XWeekPlanner extends TWebControl
{
	const CSS_FILE = 'assets/style.css';
	const JS_FILE = 'assets/XWeekPlanner_new.js';
	const CLOSE_IMG = 'assets/close.png';

	private $_dataSource = null;
	private $_currentDataSourceValid = null;
	private $startTime = null;
	private $endTime = null;
    private $pinCode = null;
    private $exitingOnly = null;
    private $specialRelayPlan = null;
    private $unlocking = null;
    private $supOpenTooLongAlarm = null;
    private $supWithoutPermAlarm = null;
    private $checkOnlyCompanyID = null;

	public function onLoad($param)
	{
		$cssFile = $this->publishAsset(self::CSS_FILE);
		$jsFile = $this->publishAsset(self::JS_FILE);
		$closeImg = $this->publishAsset(self::CLOSE_IMG);

		$csm = $this->getPage()->getClientScript();

        $this->startTime = new TTextBox();
        $this->startTime->setID('startTime');
        $this->addedControl($this->startTime);

        $this->endTime = new TTextBox();
        $this->endTime->setID('endTime');
        $this->addedControl($this->endTime);

        $plannerType = "";

        if($this->getPlannerType() == 'Access')
        {
            $this->pinCode = new TCheckBox();
            $this->pinCode->setID('pinCode');
            $this->addedControl($this->pinCode);

            $this->exitingOnly = new TCheckBox();
            $this->exitingOnly->setID('exitingOnly');
            $this->addedControl($this->exitingOnly);

            $plannerType .= 'pinCode           : $(\''.$this->pinCode->getClientID().'\'),';
            $plannerType .= 'exitingOnly           : $(\''.$this->exitingOnly->getClientID().'\'),';

        }
        else
        {
            $this->unlocking = new TCheckBox();
            $this->unlocking->setID('unlocking');
            $this->addedControl($this->unlocking);

            $this->supOpenTooLongAlarm = new TCheckBox();
            $this->supOpenTooLongAlarm->setID('supOpenTooLongAlarm');
            $this->addedControl($this->supOpenTooLongAlarm);

            $this->supWithoutPermAlarm = new TCheckBox();
            $this->supWithoutPermAlarm->setID('supWithoutPermAlarm');
            $this->addedControl($this->supWithoutPermAlarm);

            $this->checkOnlyCompanyID = new TCheckBox();
            $this->checkOnlyCompanyID->setID('checkOnlyCompanyID');
            $this->addedControl($this->checkOnlyCompanyID);


            $plannerType .= 'unlocking           : $(\''.$this->unlocking->getClientID().'\'),';
            $plannerType .= 'supOpenTooLongAlarm           : $(\''.$this->supOpenTooLongAlarm->getClientID().'\'),';
            $plannerType .= 'supWithoutPermAlarm           : $(\''.$this->supWithoutPermAlarm->getClientID().'\'),';
            $plannerType .= 'checkOnlyCompanyID           : $(\''.$this->checkOnlyCompanyID->getClientID().'\'),';

        }
        
        $this->specialRelayPlan = new TCheckBox();
        $this->specialRelayPlan->setID('specialRelayPlan');
        $this->addedControl($this->specialRelayPlan);

        $plannerType .= 'specialRelayPlan           : $(\''.$this->specialRelayPlan->getClientID().'\'),';


		$script = 'var wp = new XWeekPlanner("'. $this->getClientID() .'",
		{startDate				 : "'.$this->getStartDate().'",
         allowItemEdit     		 : "'.$this->getAllowInlineEdit().'",
         allowItemDelete		 : 1,
         allowItemSelect		 : 1,
         allowItemMove			 : 1,
         allowItemResize 		 : 1,
         deleteConfirmMessage: \''.Prado::localize('Are you sure you want to delete this item?').'\',
         headerDateFormat	 : \'d.m\',
         startHour			 : 0,
         endHour			 : 23,
         onitemclick		 : \'\',
         readOnly			 : 0,
         startTime           : $(\''.$this->startTime->getClientID().'\'),
         endTime           :$(\''.$this->endTime->getClientID().'\'),
         '.$plannerType.'
         levelId : 0
		});';

		if(!$this->getPage()->getIsCallback())
		{
			if(!$csm->isEndScriptRegistered('xweekplanner/config'))
	    		$csm->registerEndScript('xweekplanner/config',$script);
		}
	    
	    $csm->registerPradoScript('ajax');
	    	

       	if(!$csm->isStyleSheetFileRegistered('xweekplanner'))
       	  	$csm->registerStyleSheetFile('xweekplanner', $cssFile);
       	if(!$csm->isScriptFileRegistered('xweekplanner'))
          	$csm->registerScriptFile('xweekplanner', $jsFile);


		$t1 = new TCallback();
		$t1->setID('callbackweekSchedulerLoadAppointments');
		$t1->attachEventHandler("OnCallBack", array($this, "OnLoadAppointments"));
		$this->Page->getControls()->add($t1);

		$t1 = new TCallback();
		$t1->setID('callbackweekSchedulerSaveAppointment');
		$t1->attachEventHandler("OnCallBack", array($this, "OnSaveAppointment"));
		$this->Page->getControls()->add($t1);

		$t1 = new TCallback();
		$t1->setID('callbackweekSchedulerDeleteAppointment');
		$t1->attachEventHandler("OnCallBack", array($this, "OnDeleteAppointment"));
		$this->Page->getControls()->add($t1);
		
		$t1 = new TCallback();
		$t1->setID('callbackweekSchedulerSelectAppointment');
		$t1->attachEventHandler("OnCallBack", array($this, "OnSelectAppointment"));
		$this->Page->getControls()->add($t1);	
	}

    public function setEnabled($flag)
    {

        
    }

	/**
	 * Renders the body content enclosed between the control tag.
	 * By default, child controls and text strings will be rendered.
	 * You can override this method to provide customized content rendering.
	 * @param THtmlWriter the writer used for the rendering purpose
	 */
	public function renderContents($writer)
	{
  		$writer->renderBeginTag('center');
        $writer->addAttribute('class','field');
		$writer->renderBeginTag('div');
    		$writer->renderBeginTag('table');
        		$writer->renderBeginTag('tr');
                    $writer->renderBeginTag('td');
                        $startTimeLabel = new TLabel();
                        $startTimeLabel->setText(Prado::localize("Start time"));
                        $this->addedControl($startTimeLabel);
                        $startTimeLabel->render($writer);
            		$writer->renderEndTag();
                    $writer->renderBeginTag('td');
                        $this->startTime->render($writer);
            		$writer->renderEndTag();
                    $writer->renderBeginTag('td');
                        $endTimeLabel = new TLabel();
                        $endTimeLabel->setText(Prado::localize("End time"));
                        $this->addedControl($endTimeLabel);
                        $endTimeLabel->render($writer);
            		$writer->renderEndTag();
                    $writer->renderBeginTag('td');
                        $this->endTime->render($writer);
            		$writer->renderEndTag();

                    if($this->getPlannerType() == 'Access')
                    {
                        $writer->renderBeginTag('td');
                            $pincodeLabel = new TLabel();
                            $pincodeLabel->setText(Prado::localize("Pin Code necessary"));
                            $this->addedControl($pincodeLabel);
                            $pincodeLabel->render($writer);
                        $writer->renderEndTag();
                        $writer->renderBeginTag('td');
                            $this->pinCode->render($writer);
                        $writer->renderEndTag();

                        $writer->renderBeginTag('td');
                            $exitingOnlyLabel = new TLabel();
                            $exitingOnlyLabel->setText(Prado::localize("Exiting only"));
                            $this->addedControl($exitingOnlyLabel);
                            $exitingOnlyLabel->render($writer);
                        $writer->renderEndTag();
                        $writer->renderBeginTag('td');
                            $this->exitingOnly->render($writer);
                        $writer->renderEndTag();
                    }
                    else
                    {
                        $writer->renderBeginTag('td');
                            $unlockingLabel = new TLabel();
                            $unlockingLabel->setText(Prado::localize("Unlocking"));
                            $this->addedControl($unlockingLabel);
                            $unlockingLabel->render($writer);
                        $writer->renderEndTag();
                        $writer->renderBeginTag('td');
                            $this->unlocking->render($writer);
                        $writer->renderEndTag();
                        
                        $writer->renderBeginTag('td');
                            $supOpenTooLongAlarmLabel = new TLabel();
                            $supOpenTooLongAlarmLabel->setText(Prado::localize("Sup. 'door open too long alarm'"));
                            $this->addedControl($supOpenTooLongAlarmLabel);
                            $supOpenTooLongAlarmLabel->render($writer);
                        $writer->renderEndTag();
                        $writer->renderBeginTag('td');
                            $this->supOpenTooLongAlarm->render($writer);
                        $writer->renderEndTag();

                        $writer->renderBeginTag('td');
                            $supWithoutPermAlarmLabel = new TLabel();
                            $supWithoutPermAlarmLabel->setText(Prado::localize("Sup. 'open without permission alarm'"));
                            $this->addedControl($supWithoutPermAlarmLabel);
                            $supWithoutPermAlarmLabel->render($writer);
                        $writer->renderEndTag();
                        $writer->renderBeginTag('td');
                            $this->supWithoutPermAlarm->render($writer);
                        $writer->renderEndTag();

                        $writer->renderBeginTag('td');
                            $checkOnlyCompanyIDLabel = new TLabel();
                            $checkOnlyCompanyIDLabel->setText(Prado::localize("Check only company ID"));
                            $this->addedControl($checkOnlyCompanyIDLabel);
                            $checkOnlyCompanyIDLabel->render($writer);
                        $writer->renderEndTag();
                        $writer->renderBeginTag('td');
                            $this->checkOnlyCompanyID->render($writer);
                        $writer->renderEndTag();

                    }

                    $writer->renderBeginTag('td');
                        $specialRelayPlanLabel = new TLabel();
                        $specialRelayPlanLabel->setText(Prado::localize("Use special relay plan"));
                        $this->addedControl($specialRelayPlanLabel);
                        $specialRelayPlanLabel->render($writer);
            		$writer->renderEndTag();
                    $writer->renderBeginTag('td');
                        $this->specialRelayPlan->render($writer);
            		$writer->renderEndTag();

        		$writer->renderEndTag();
    		$writer->renderEndTag();
		$writer->renderEndTag();
		$writer->renderEndTag();

		$arrDays = $this->getDayNames();
		$date = mktime($this->getStartHourOfWeek(),0,0,5,5,2006);
		$suffix = "00"; // Enable this line in case you want to show hours like 08:00 - 23:00

		$writer->addAttribute('id','weekScheduler_top')	;
		$writer->renderBeginTag('div');
			$writer->addAttribute('class','spacer');
			$writer->renderBeginTag('div');
				$writer->renderBeginTag('span');
				$writer->renderEndTag();
			$writer->renderEndTag();
			$writer->addAttribute('id','weekScheduler_dayRow');
			$writer->addAttribute('class','days');
			$writer->renderBeginTag('div');
				foreach ($arrDays as $name)
				{
					$writer->renderBeginTag('div');
					$writer->write($name);
						$writer->renderBeginTag('span');
						$writer->renderEndTag();
					$writer->renderEndTag();
				}
			$writer->renderEndTag();
		$writer->renderEndTag();
		$writer->addAttribute('id','weekScheduler_content');
		$writer->renderBeginTag('div');
			$writer->addAttribute('id','weekScheduler_hours');
			$writer->renderBeginTag('div');

			for($no=$this->getStartHourOfWeek();$no<=$this->getEndHourOfWeek();$no++)
			{
				$suffix = date("a",$date);
				$hour = date("g",$date);
				$hour = $no.":00";
				$writer->addAttribute('class','calendarContentTime');
				$writer->renderBeginTag('div');
					$writer->write($hour);
					/*$writer->addAttribute('class','content_hour');
					$writer->renderBeginTag('span');
						$writer->write($suffix);
					$writer->renderEndTag();*/
				$writer->renderEndTag();
				$date = $date + 3600;
			}

			$writer->renderEndTag();
			$writer->addAttribute('id','weekScheduler_appointments');
			$writer->renderBeginTag('div');
				// Looping through the days of a week
				for($no=0;$no<7;$no++)
				{
					$writer->addAttribute('class','weekScheduler_appointments_day');
					$writer->renderBeginTag('div');
						for($no2=$this->getStartHourOfWeek();$no2<=$this->getEndHourOfWeek();$no2++)
						{
							$writer->addAttribute('id',"weekScheduler_appointment_hour".$no."_".$no2);
							$writer->addAttribute('class','weekScheduler_appointmentHour');
							$writer->renderBeginTag('div');
							$writer->renderEndTag();
						}
					$writer->renderEndTag();
				}

			$writer->renderEndTag();
		$writer->renderEndTag();
	}
	
	public function dataBind()
	{
		$arrItems = array();
		$this->onDataBinding(null);
		
		foreach ($this->getDataSource() as $row)
		{
			$color =  $this->getDataFieldValue($row,$this->getDataColorField()) == '' ? $this->getDefaultColor() :  $this->getDataFieldValue($row,$this->getDataColorField());
			$arrItems[] = array('id' => $this->getDataFieldValue($row,$this->getDataKeyField()),
						 'description' => $this->getDataFieldValue($row,$this->getDataTextField()),
						 'date' => $this->getDataFieldValue($row,$this->getDataDateField()),
						 'hour' => $this->getDataFieldValue($row,$this->getDataHourField()),
						 'duration' => $this->getDataFieldValue($row,$this->getDataDurationField()),
						 'bgColorCode' => $color);
		}
		$this->onDataBound(null);
		
		$this->getResponse()->getAdapter()->setResponseData($arrItems);		
		
	}
	
	public function updateItem($value)
	{
		if(!is_array($arr))
			$this->getResponse()->getAdapter()->setResponseData(array('id' => $value));	
		else 
			$this->getResponse()->getAdapter()->setResponseData($value);	
	}
	
	/**
	 * @return Traversable data source object, defaults to null.
	 */
	public function getDataSource()
	{
		return $this->_dataSource;
	}

	/**
	 * Sets the data source object associated with the databound control.
	 * The data source must implement Traversable interface.
	 * If an array is given, it will be converted to xxx.
	 * If a string is given, it will be converted to xxx.
	 * @param Traversable|array|string data source object
	 */
	public function setDataSource($value)
	{
		$this->_dataSource=$this->validateDataSource($value);
		$this->onDataSourceChanged();
	}
	
	/**
	 * Validates if the parameter is a valid data source.
	 * If it is a string or an array, it will be converted as a TList object.
	 * @return Traversable the data that is traversable
	 * @throws TInvalidDataTypeException if the data is neither null nor Traversable
	 */
	protected function validateDataSource($value)
	{
		if(is_string($value))
		{
			$list=new TList;
			foreach(TPropertyValue::ensureArray($value) as $key=>$value)
			{
				if(is_array($value))
					$list->add($value);
				else
					$list->add(array($value,is_string($key)?$key:$value));
			}
			return $list;
		}
		else if(is_array($value))
			return new TMap($value);
		else if(($value instanceof Traversable) || $value===null)
			return $value;
		else
			throw new TInvalidDataTypeException('databoundcontrol_datasource_invalid',get_class($this));
	}	

	/**
	 * Sets {@link setRequiresDataBinding RequiresDataBinding} as true if the control is initialized.
	 * This method is invoked when either {@link setDataSource} or {@link setDataSourceID} is changed.
	 */
	public function onDataSourceChanged()
	{
		$this->_currentDataSourceValid=false;
/*		if($this->getInitialized())
			$this->setRequiresDataBinding(true);*/
	}	
	
	/**
	 * Returns the value of the data at the specified field.
	 * If data is an array, TMap or TList, the value will be returned at the index
	 * of the specified field. If the data is a component with a property named
	 * as the field name, the property value will be returned.
	 * Otherwise, an exception will be raised.
	 * @param mixed data item
	 * @param mixed field name
	 * @return mixed data value at the specified field
	 * @throws TInvalidDataValueException if the data is invalid
	 */
	protected function getDataFieldValue($data,$field)
	{
		return TDataFieldAccessor::getDataFieldValue($data,$field);
	}	
	
	protected function getDayNames()
	{
		/*setlocale(LC_TIME,$this->getCulture());
		$arrDays = array();
		for ($i=0;$i<7;$i++)
			$arrDays[] = ucfirst(htmlentities(strftime("%A", strtotime("01/".(21+$i)."/2007"))));*/

        $arrDays[] = Prado::localize("Monday");
        $arrDays[] = Prado::localize("Tuesday");
        $arrDays[] = Prado::localize("Wednesday");
        $arrDays[] = Prado::localize("Thursday");
        $arrDays[] = Prado::localize("Friday");
        $arrDays[] = Prado::localize("Saturday");
        $arrDays[] = Prado::localize("Sunday");

		return $arrDays;
	}

	public function OnLoadAppointments($sender,$param)
	{
		$this->raiseEvent('OnLoadAppointments',$this,$param);
	}


	public function OnSaveAppointment($sender,$param)
	{
		$this->raiseEvent('OnSaveAppointment',$this,$param);
	}


	public function OnDeleteAppointment($sender,$param)
	{
		$this->raiseEvent('OnDeleteAppointment',$this,$param);
	}	
	
	/**
	 * Raises 'OnDataBinding' event.
	 * This method is invoked when {@link dataBind} is invoked.
	 * @param TEventParameter event parameter to be passed to the event handlers
	 */
	public function onDataBinding($param)
	{
		$this->raiseEvent('OnDataBinding',$this,$param);
	}	

	/**
	 * Raises <b>OnDataBound</b> event.
	 * This method should be invoked after a databind is performed.
	 * It is mainly used by framework and component developers.
	 */
	public function onDataBound($param)
	{
		$this->raiseEvent('OnDataBound',$this,$param);
	}	
	
	/**
	 * @return string tag name of the panel
	 */
	protected function getTagName()
	{
		return 'div';
	}

	/**
	 * Adds attributes to renderer.
	 * @param THtmlWriter the renderer
	 * @throws TInvalidDataValueException if default button is not right.
	 */
	protected function addAttributesToRender($writer)
	{
		$this->setCssClass('weekScheduler_container');
		parent::addAttributesToRender($writer);
	}

	/**
	 * @return boolean whether the content wraps within the panel. Defaults to true.
	 */
	public function getStartHourOfWeek()
	{
		return $this->getViewState('StartHourOfWeek','0');
	}

	/**
	 * Sets the value indicating whether the content wraps within the panel.
	 * @param boolean whether the content wraps within the panel.
	 */
	public function setStartHourOfWeek($value)
	{
		$this->setViewState('StartHourOfWeek',$value,'');
	}

	/**
	 * @return boolean whether the content wraps within the panel. Defaults to true.
	 */
	public function getEndHourOfWeek()
	{
		return $this->getViewState('EndHourOfWeek','23');
	}

	/**
	 * Sets the value indicating whether the content wraps within the panel.
	 * @param boolean whether the content wraps within the panel.
	 */
	public function setEndHourOfWeek($value)
	{
		$this->setViewState('EndHourOfWeek',$value,'');
	}

	/**
	 * @return string the legend text when the panel is used as a fieldset. Defaults to empty.
	 */
	public function getStartDate()
	{
		return $this->getViewState('StartDate',date('Y-m-d'));
	}

	/**
	 * @param string the legend text. If this value is not empty, the panel will be rendered as a fieldset.
	 */
	public function setStartDate($value)
	{
		$this->setViewState('StartDate',$value,'');
	}

	/**
	 * @return string the legend text when the panel is used as a fieldset. Defaults to empty.
	 */
	public function getAllowInlineEdit()
	{
		return $this->getViewState('AllowInlineEdit','0');
	}

	/**
	 * @param string the legend text. If this value is not empty, the panel will be rendered as a fieldset.
	 */
	public function setAllowInlineEdit($value)
	{
		$this->setViewState('AllowInlineEdit',$value,'');
	}

	/**
	 * @return string the legend text when the panel is used as a fieldset. Defaults to empty.
	 */
	public function getHeaderDateFormat()
	{
		return $this->getViewState('HeaderDateFormat','d.m');
	}

	/**
	 * @param string the legend text. If this value is not empty, the panel will be rendered as a fieldset.
	 */
	public function setHeaderDateFormat($value)
	{
		$this->setViewState('HeaderDateFormat',$value,'');
	}

	/**
	 * @return string the legend text when the panel is used as a fieldset. Defaults to empty.
	 */
	public function getAutoSave()
	{
		return $this->getViewState('AutoSave','1');
	}

	/**
	 * @param string the legend text. If this value is not empty, the panel will be rendered as a fieldset.
	 */
	public function setAutoSave($value)
	{
		$this->setViewState('AutoSave',$value,'');
	}

	/**
	 * @return string the legend text when the panel is used as a fieldset. Defaults to empty.
	 */
	public function getClientItemClick()
	{
		return $this->getViewState('ClientItemClick','');
	}

	/**
	 * @param string the legend text. If this value is not empty, the panel will be rendered as a fieldset.
	 */
	public function setClientItemClick($value)
	{
		$this->setViewState('ClientItemClick',$value,'');
	}

	/**
	 * @return string the legend text when the panel is used as a fieldset. Defaults to empty.
	 */
	public function getDeleteMessage()
	{
		return $this->getViewState('DeleteMessage','');
	}

	/**
	 * @param string the legend text. If this value is not empty, the panel will be rendered as a fieldset.
	 */
	public function setDeleteMessage($value)
	{
		$this->setViewState('DeleteMessage',$value,'');
	}

	public function getCulture()
	{
        return $this->getViewState('Culture',$this->getApplication()->getGlobalization()->getCulture() );
	}

	/**
	 * @param string the legend text. If this value is not empty, the panel will be rendered as a fieldset.
	 */
	public function setCulture($value)
	{
		$this->setViewState('Culture',$value,'');
	}

	
	////////////////////////////////
	
	public function getDataTextField()
	{
		return $this->getViewState('DataTextField');
	}
	
	public function setDataTextField($value)
	{
		$this->setViewState('DataTextField',$value);
	}
	
	public function getDataKeyField()
	{
		return $this->getViewState('DataKeyField');
	}
	
	public function setDataKeyField($value)
	{
		$this->setViewState('DataKeyField',$value);
	}	
	
	public function getDataDateField()
	{
		return $this->getViewState('DataDateField');
	}
	
	public function setDataDateField($value)
	{
		$this->setViewState('DataDateField',$value);
	}	
	
	public function getDataHourField()
	{
		return $this->getViewState('DataHourField');
	}
	
	public function setDataHourField($value)
	{
		$this->setViewState('DataHourField',$value);
	}

	public function getDataDurationField()
	{
		return $this->getViewState('DataDurationField');
	}
	
	public function setDataDurationField($value)
	{
		$this->setViewState('DataDurationField',$value);
	}	
	
	public function getDataColorField()
	{
		return $this->getViewState('DataDurationField');
	}
	
	public function setDataColorField($value)
	{
		$this->setViewState('DataDurationField',$value);
	}

	public function getPlannerType()
	{
		return $this->getViewState('PlannerType');
	}

	public function setPlannerType($value)
	{
		$this->setViewState('PlannerType',$value);
	}

	
}

?>