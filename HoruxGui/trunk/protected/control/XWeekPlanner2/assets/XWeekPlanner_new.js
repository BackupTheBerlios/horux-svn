
/**

	This is a component originally based on the one created by www.dhtmlgoodies.com
	Author: Mauro Lewinzon
	Web: www.enigmastudio.com.ar
	
	

*/


// Adds some functionality to the Date object to work with MySql date format (of course this doesn't means that you
// have to work with MySql)

Date.prototype.getMysqlFormat = function(){
	
	return this.getFullYear() + '-' + (this.getMonth()+1) + '-' + this.getDate();
	
}


Date.prototype.setMysqlFormat = function(str){

	var dateItems = str.split(/\-/g);
	
	this.setFullYear(dateItems[0]);
	this.setDate(dateItems[2]/1);
	this.setMonth(dateItems[1]/1-1);
	this.setHours(1);
	this.setMinutes(0);
	this.setSeconds(0);
	
}

Date.prototype.addTimeString = function (str){
	
	var str = str.split(':');
	
	try{
		this.setHours(this.getHours() + (str[0] -0));
		this.setMinutes(this.getMinutes() + (str[1]-0));
	}
	catch(ex){
		
	}
	
}

Date.prototype.getTimeString = function () {
	
	if(this.getMinutes() < 10)
		return this.getHours() + ':0' + this.getMinutes();
	else
		return this.getHours() + ':' + this.getMinutes();
	
}

// Method: px2int(void)
// Description: Replaces "px" in a string (Ex.: "32px" => "32")

String.prototype.px2int = function (){
	
	return this.replace("px","") -0;
}


// Creates xWeekPlanner Class
XWeekPlanner = Class.create();

XWeekPlanner.prototype = {
	
	// initialize (string,array) : void
	// containerId: the ID of the DIV element
	// options: array with the configuration
	// By now, it uses static names for the child elements (as "weekScheduler_appointments"). The
	// idea is to make this dynamic with the DIV ID, to allow to have more than one XWeekPlanner
	// in the same page.
	initialize: function(containerId, options){
		
		/* PRIVATE */
		this.dayPositionArray = new Array();
		this.dayDateArray = new Array();
		this.dateStartOfWeek = new Date();
		this.containerId = containerId;
		this.container = $(containerId);
		this.appointmentsContainer = $('weekScheduler_appointments');
		this.zIndex = 500;
		this.idIndex = 0;
        this.idIndexNew = 5555555;
		this.appointments = new Array();
		this.activeItem = false;
		this.activeCallback = false;
		
		this.columnWidth = ($('weekScheduler_appointments').getElementsByTagName('DIV')[0].clientWidth);
		this.rowHeight = ($('weekScheduler_appointments').getElementsByTagName('DIV')[1].clientHeight);
		
		this.setOptions(options);
		
		this.makeStartDate();
		
		this.updateHeader();
		
		this.loadAppointments();

        document.getElementById('weekScheduler_content').scrollTop = 420;

		Event.observe(document, 'keypress', this.keyPress.bindEvent(this) );
		Event.observe(document, 'dblclick', this.dblclick.bindEvent(this) );
		Event.observe(this.options.startTime,'change',this.change.bind(this));
		Event.observe(this.options.endTime,'change',this.change.bind(this));

        if(this.options.pinCode)
            Event.observe(this.options.pinCode,'click',this.pcChange.bind(this));

        if(this.options.exitingOnly)
    		Event.observe(this.options.exitingOnly,'click',this.eoChange.bind(this));

        if(this.options.unlocking)
    		Event.observe(this.options.unlocking,'click',this.uChange.bind(this));

        if(this.options.supOpenTooLongAlarm)
    		Event.observe(this.options.supOpenTooLongAlarm,'click',this.a1Change.bind(this));

        if(this.options.supWithoutPermAlarm)
    		Event.observe(this.options.supWithoutPermAlarm,'click',this.a2Change.bind(this));

        if(this.options.checkOnlyCompanyID)
    		Event.observe(this.options.checkOnlyCompanyID,'click',this.cChange.bind(this));


		Event.observe(this.options.specialRelayPlan,'click',this.srpChange.bind(this));

	},


    uChange: function () {
        if(this.activeItem)
        {
            if(!this.options.unlocking.checked)
            {
                 this.options.supWithoutPermAlarm.checked = false;
                 this.activeItem.supWithoutPermAlarm = this.options.supWithoutPermAlarm.checked;
            }
            
            this.activeItem.unlocking = this.options.unlocking.checked;
            this.activeItem.resize2(this.options.startTime.value,this.options.endTime.value);
        }
    },

    a1Change: function () {
        if(this.activeItem)
        {
            this.activeItem.supOpenTooLongAlarm = this.options.supOpenTooLongAlarm.checked;
            this.activeItem.resize2(this.options.startTime.value,this.options.endTime.value);
        }
    },

    a2Change: function () {
        if(this.activeItem)
        {
            if(this.options.supWithoutPermAlarm.checked)
            {
                this.options.unlocking.checked = true;
                this.activeItem.unlocking = this.options.unlocking.checked;
            }
            this.activeItem.supWithoutPermAlarm = this.options.supWithoutPermAlarm.checked;
            this.activeItem.resize2(this.options.startTime.value,this.options.endTime.value);
        }
    },

    cChange: function () {
        if(this.activeItem)
        {
            this.activeItem.checkOnlyCompanyID = this.options.checkOnlyCompanyID.checked;
            this.activeItem.resize2(this.options.startTime.value,this.options.endTime.value);
        }
    },


    pcChange: function () {
        if(this.activeItem)
        {
            this.activeItem.pinCode = this.options.pinCode.checked;
            this.activeItem.resize2(this.options.startTime.value,this.options.endTime.value);
        }
    },

    eoChange: function () {
        if(this.activeItem)
        {
            this.activeItem.exitingOnly = this.options.exitingOnly.checked;
            this.activeItem.resize2(this.options.startTime.value,this.options.endTime.value);
        }
    },

    srpChange: function () {
        if(this.activeItem)
        {
            this.activeItem.specialRelayPlan = this.options.specialRelayPlan.checked;
            this.activeItem.resize2(this.options.startTime.value,this.options.endTime.value);
        }
    },


	getScrollTop: function () {
		
		return $('weekScheduler_content').scrollTop;
	},
	
	// Sets the default options
	setOptions: function(options) {
      this.options = {
         startDate           : '2007-02-12',
         allowInlineEdit     : 1,
         allowDelete		 : 1,
         allowSelect		 : 1,
         allowMove			 : 1,
         allowResize 		 : 1,
         deleteConfirmMessage: 'Are you sure you want to delete this item?',
         headerDateFormat	 : 'd.m',
         startHour			 : 0,
         endHour			 : 23,
         onitemclick		 : '',
         readOnly			 : 0,
         pinCode         : 0,
         exitingOnly     : 0,
         specialRelayPlan : 0,
         unlocking : 0,
         supOpenTooLongAlarm : 0,
         supWithoutPermAlarm : 0,
         checkOnlyCompanyID : 0
      }
      Object.extend(this.options, options || {});
   },


   // Given a MySql string date, this creates a JS Date object based on the timestamp
   makeStartDate: function() {
   	
   		this.dateStartOfWeek.setMysqlFormat(this.options.startDate);
		var day = this.dateStartOfWeek.getDay();
		// This is to know the date of the first day of the week, if a different day is given
		this.dateStartOfWeek.setTime(this.dateStartOfWeek.getTime() - (1000*60*60*24) * (day));
	},

	// Translates a position of a div appointment in time
	getYPositionFromTime: function (hour,minute){
		
		return Math.floor((hour - this.options.startHour ) * (this.rowHeight+1) + (minute/60 * (this.rowHeight+1)));
	},
	
	// Gives the real X position
	getXPosition: function(pos) {
		
		pos = pos - this.appointmentsContainer.offsetLeft - this.container.offsetLeft;
		for(var i = 0; i < 7; i++){
			
			if(pos >= (this.columnWidth * i) && pos <= (this.columnWidth * (i + 1) ))
			{
				return ((this.columnWidth+1) * i) + this.appointmentsContainer.offsetLeft;
				break;
			}
		}
	},
	// Gives the real Y position (Because the container may not be on the top of the page)
	getYPosition: function (pos) {
		
		
		return pos - this.appointmentsContainer.offsetTop - this.container.offsetTop + this.getScrollTop();
		
		
	},
	// Given a day of the week (1,2..7) this returns the X position in pixels
	getXPositionFromDay: function(day){
		
		return day  * (this.columnWidth+1) + this.appointmentsContainer.offsetLeft ;
			
		
	},
	
	getStartDateFromPosition: function(element) {
		
		
		var day = this.getDayFromPosition(element);

		var hour = (element.style.top.replace("px","") ) / this.rowHeight + this.options.startHour;
        var hourFloor = Math.floor(hour);
        hour = (element.style.top.replace("px","") - hourFloor) / this.rowHeight + this.options.startHour;

		var newDate = new Date(this.dateStartOfWeek);
		newDate.setDate(newDate.getDate() + day);
		newDate.setHours(hour);
		
		newDate.setMinutes((hour - Math.floor(hour)) * 6000 / 100);
		return newDate;
		
	},
	// Returns the day number given the X position of an element
	getDayFromPosition: function (element) {
		
		return  Math.floor((element.style.left.replace("px","") -0) / this.columnWidth);
	},

	// Updates the header with the correct dates (in the begining and each time the user goes foward or backwards)
	updateHeader: function()
	{
		/*var subDivs = $('weekScheduler_dayRow').getElementsByTagName('DIV');

		var tmpDate2 = new Date(this.dateStartOfWeek);
	
	
		for(var no=0;no<subDivs.length;no++){
			var month = tmpDate2.getMonth()/1 + 1;
			var date = tmpDate2.getDate();
			var tmpHeaderFormat = " " + this.options.headerDateFormat;
			tmpHeaderFormat = tmpHeaderFormat.replace('d',date);
			tmpHeaderFormat = tmpHeaderFormat.replace('m',month);
	
			subDivs[no].getElementsByTagName('SPAN')[0].innerHTML = tmpHeaderFormat;
	
			tmpDate2.setTime(tmpDate2.getTime() + (1000*60*60*24));
		}*/
	},
	// Gives the next ID for an DIV apponintment
	getNextId: function () {
		
		this.idIndex ++;
		
		return 'weekScheduler_item_' + this.idIndex;
		
	},

	callbackLoadAppointments: function (request,result)	{
		for (var i = 0; i < result.length; i++)
		{
			itmApp = new XAppointment(this,result[i]);
			
			this.addItem(itmApp);
		}
	},	
	// Ads an XApponintment to the XWeekPlanner appointments container
	addItem: function (item) {
       
			this.appointments[item.id] = item;
			item.divElement = this.appointmentsContainer.appendChild(item.createDivElement());
			item.onclick = this.options.onitemclick;
			item.show();
            this.idIndex = item.id;
			return item;
	},
	
	callbackDeleteAppointment: function (request,result) {
			this.activeItem.remove();
			delete this.activeItem;
			this.activeItem = null;		
	},
	
	callbackSaveAppointment: function (request,result) {
		
		if(result != null)
		{
			this.activeItem.loadFromServer(result);
		}
		
	},
	

	loadAppointments: function ()
	{
		var date = this.dateStartOfWeek.getDay();
		
		new Prado.Callback('callbackweekSchedulerLoadAppointments',
			{CommandName:'load', CommandParameter:date, levelId:this.options.levelId}, null,
			{'CausesValidation':false, 'onSuccess' : this.callbackLoadAppointments.bind(this)}
		);
		
		this.activeCallback = 'loadAppointments';
	
	},
	
	deleteAppointment: function (item){
		this.setActiveItem(item);
		new Prado.Callback('callbackweekSchedulerDeleteAppointment',
			{CommandName:'delete', CommandParameter:item.getAsArray()}, null,
			{'CausesValidation':false, 'onSuccess': this.callbackDeleteAppointment.bind(this) }
		);
		
	},
	
	saveAppointment: function (item){
		this.setActiveItem(item);
		new Prado.Callback('callbackweekSchedulerSaveAppointment',
			{CommandName:'save', CommandParameter:item.getAsArray()}, null,
			{'CausesValidation':false, 'onSuccess': this.callbackSaveAppointment.bind(this) }
		);
	},
	
	clearAppointments: function ()
	{
		for(var prop in this.appointments){
			
			if(this.appointments[prop].remove)
				this.appointments[prop].remove();
		}
		this.appointments = new Array();
	},
	// Sets the active XAppointment
	setActiveItem: function (item){
		
		if(item instanceof XAppointment)
		{
			if(this.activeItem)
				this.activeItem.blur();
			
			item.focus();
			this.activeItem = item;
		}
		
	},

   change: function(event) {
		if(this.activeItem)
        {
            this.activeItem.resize2(this.options.startTime.value,this.options.endTime.value);
        }
   },


	keyPress: function(event){
	},
	
	dblclick: function (event) {
		
		if(Event.element(event).className == 'weekScheduler_appointmentHour')
		{
			var tmpDate = new Date(this.dateStartOfWeek);

			tmpDate.setDate(tmpDate.getDate() + this.getXPosition(Event.pointerX(event)) / this.columnWidth);
			tmpDate.setHours(this.getYPosition(Event.pointerY(event)) / this.rowHeight -1);
			var item = new XAppointment(this);
			item.startDate = tmpDate;
            item.id = this.idIndexNew++;
			item = this.addItem(item);
            this.saveAppointment(item);

            this.options.startTime.value = item.getTimeStart();
            this.options.endTime.value = item.getTimeEnd();
		}
		
	},
	
	previousWeek: function (){
	
		this.dateStartOfWeek.setTime(this.dateStartOfWeek.getTime() - (1000*60*60*24*7));
		this.reload();
	
	},
	
	nextWeek: function()
	{
		this.dateStartOfWeek.setTime(this.dateStartOfWeek.getTime() + (1000*60*60*24*7));
		this.reload();
	
	},
	
	reload: function(){
		this.updateHeader();
		this.clearAppointments();
		this.loadAppointments();
	},
	
	moveY: function(top,element){

		if(top >= 0 && (top + element.style.height.px2int()) <  $('weekScheduler_content').scrollHeight)
			return true;
		else
			return false;
		
	}

};

// XAppointment class: represents an Apponintment of the XWeekPlanner
XAppointment = Class.create();

XAppointment.prototype = {
	
	// initialize (object XWeekPlanner[,object])
	// object XWeekPlanner: the XWeekPlanner this XAppointment belongs to
	// object: optionally an object with the information of the Appointment
	initialize: function (parent,info) {
		
		this.description = '';
		this.bgColor = '';
		this.startDate = '';
		this.duration = '1:00';
		this.id = 0;
		this.clientId = parent.getNextId();
		this.weekPlanner = parent;
		this.divElement = false;
		this.isEditInProgress = false;
		this.ondblclick = '';
        this.zIndex = 501;
        this.pinCode = "0";
        this.exitingOnly = "0";
        this.specialRelayPlan = "0";

        this.unlocking = "0";
        this.supOpenTooLongAlarm = "0";
        this.supWithoutPermAlarm = "0";
        this.checkOnlyCompanyID = "0";

        
		if(info)
			this.loadFromServer(info);
        


	},
	// Gets the information of the XAppointment as an array
	getAsArray: function () {

        if(this.duration == "24:00") this.duration = "23:59";

		return { 
			"id"			: this.id,
			'day' 			: this.startDate.getDay(),
			'duration' 		: this.duration,
			'hour' 			: this.startDate.getHours() + ':' + this.startDate.getMinutes(),
            'pinCode'       : this.pinCode,
            'exitingOnly'       : this.exitingOnly,
            'specialRelayPlan'       : this.specialRelayPlan,
            'unlocking'       : this.unlocking,
            'supOpenTooLongAlarm'       : this.supOpenTooLongAlarm,
            'supWithoutPermAlarm'       : this.supWithoutPermAlarm,
            'checkOnlyCompanyID'       : this.checkOnlyCompanyID
		};	
		
		
		
	},

	getDuration: function () {
        var duration = (this.divElement.style.height.replace("px","")) / this.weekPlanner.rowHeight;
        var durationFloor = Math.floor(duration);
		duration = (this.divElement.style.height.replace("px","") ) / this.weekPlanner.rowHeight ;
        duration -= ((durationFloor-1)/60);
		var min = Math.floor( ((duration - Math.floor(duration) ) * 60) );
		if(min < 10)
			duration = Math.floor(duration) + ':' + '0' + min;
		else
			duration = Math.floor(duration) + ':' + min;
		
		return duration;
	},
	
	getTopPos: function () {
		return this.weekPlanner.getYPositionFromTime(this.startDate.getHours(),this.startDate.getMinutes());
	},
	
	getLeftPos: function () {
        
		return this.weekPlanner.getXPositionFromDay(this.startDate.getDay());
	},	
	
	getHeight: function () {
		
		var height = this.duration.split(':');
        var addPixel = (height[0]/1)-1;
		height = (height[0]-0) + height[1] / 60;
		return Math.floor(height * (this.weekPlanner.rowHeight ) + addPixel);
	},
	
	getTime: function () {
		
		var tmpDate = new Date(this.startDate);
		tmpDate.addTimeString(this.duration);
		return this.startDate.getTimeString() + '-' + tmpDate.getTimeString();
	},
	getTimeStart: function () {

		var tmpDate = new Date(this.startDate);
		tmpDate.addTimeString(this.duration);
		return this.startDate.getTimeString();
	},
	getTimeEnd: function () {

		var tmpDate = new Date(this.startDate);
		tmpDate.addTimeString(this.duration);
		return tmpDate.getTimeString();
	},
	
	loadFromServer: function (itm) {
        
        var hour = itm.hour.split(':');
        this.startDate = new Date(2007,2,11+itm.day,hour[0],hour[1],0,0);
        this.duration = itm.duration;
		this.id = itm.id;
        this.pinCode = itm.pinCode;
        this.exitingOnly = itm.exitingOnly;
        this.specialRelayPlan = itm.specialRelayPlan;
        this.unlocking = itm.unlocking;
        this.supOpenTooLongAlarm = itm.supOpenTooLongAlarm;
        this.supWithoutPermAlarm = itm.supWithoutPermAlarm;
        this.checkOnlyCompanyID = itm.checkOnlyCompanyID;
		
	},
	
	createDivElement: function () {
		
		var div = document.createElement('DIV');
		
		div.className='weekScheduler_anAppointment';
		
		div.style.left 		= this.getLeftPos() + 'px';
		div.style.top 		= this.getTopPos() + 'px';
		div.style.height 	= this.getHeight() + 'px';
		div.style.zIndex 	= this.zIndex;
		div.id 				= this.clientId;      	
      	div.onclick 		= this.click.bindEvent(this);	
      	div.mousedown		= this.mouseDown.bindEvent(this);	
      	div.ondblclick 		= this.ondblclick;

		if(this.bgColor) div.style.backgroundColor = this.bgColor;

        var timeCloseDiv = document.createElement('DIV');
		timeCloseDiv.id 			= 'weekScheduler_appointment_time_close_' + this.id;
		timeCloseDiv.className	= 'weekScheduler_appointment_time_close';
		timeCloseDiv.innerHTML 	= '';
        Event.observe(timeCloseDiv, 'click', this.clickClose.bind(this));
		div.appendChild(timeCloseDiv);


		var timeDiv = document.createElement('DIV');
		timeDiv.id 			= 'weekScheduler_appointment_time_' + this.id;
		timeDiv.className	= 'weekScheduler_appointment_time';

        var tcOption = "";
        if(this.pinCode == "1")
            tcOption += "PC ";
        if(this.exitingOnly == "1")
            tcOption += "EO ";

        if(this.unlocking == "1" && this.supWithoutPermAlarm == "0")
            tcOption += "U ";
        if(this.unlocking == "1" && this.supWithoutPermAlarm == "1")
            tcOption += "UA ";
        if(this.supOpenTooLongAlarm == "1")
            tcOption += "A ";
        if(this.checkOnlyCompanyID == "1")
            tcOption += "C ";

        if(this.specialRelayPlan == "1")
            tcOption += "SR ";


        tcOption += " ";

		timeDiv.innerHTML 	= tcOption + this.getTime();
		div.appendChild(timeDiv);

	
		var header = document.createElement('DIV');
		header.className	= 'weekScheduler_appointment_header';
		Event.observe(header, 'mousedown', this.mouseDown.bindAsEventListener(this));
		
		div.appendChild(header);
	
		var innerSpan = document.createElement('SPAN');
		innerSpan.innerHTML = this.description;
		innerSpan.className = 'weekScheduler_appointment_txt';
		div.appendChild(innerSpan);
	
		var textarea = document.createElement('TEXTAREA');
		textarea.className		= 'weekScheduler_appointment_textarea';
		textarea.style.display	= 'none';
		div.appendChild(textarea);
	
		var footerDiv = document.createElement('DIV');
		footerDiv.className		= 'weekScheduler_appointment_footer';

		Event.observe(footerDiv, 'mousedown', this.mouseDown.bindAsEventListener(this));
		
		div.appendChild(footerDiv);
		
		this.divElement = div;
		
		return this.divElement;	
				
	},

    clickClose: function(event) {
        if(confirm(this.weekPlanner.options.deleteConfirmMessage))
						this.weekPlanner.deleteAppointment(this);
    },
    
	mouseDown: function(event){

		if(this.isEditInProgress)
			this.endEdit();		
		
		if(Event.element(event).className == 'weekScheduler_appointment_footer')
        {
			this.weekPlanner.container.onmousemove = this.resize.bindAsEventListener(this);
        }
		else if(Event.element(event).className == 'weekScheduler_appointment_header')
		{
			this.weekPlanner.container.onmousemove = this.move.bindAsEventListener(this);
		}
		document.onmouseup = this.mouseUp.bind(this);
		
	
		Event.stop(event);
		
	},
	
	mouseUp: function(event) {
		
		this.startDate = this.weekPlanner.getStartDateFromPosition(this.divElement);
		this.duration = this.getDuration();
		this.weekPlanner.saveAppointment(this);

        var tcOption = "";
        if(this.pinCode == "1")
            tcOption += "PC ";
        if(this.exitingOnly == "1")
            tcOption += "EO ";

        if(this.unlocking == "1" && this.supWithoutPermAlarm == "0")
            tcOption += "U ";
        if(this.unlocking == "1" && this.supWithoutPermAlarm == "1")
            tcOption += "UA ";
        if(this.supOpenTooLongAlarm == "1")
            tcOption += "A ";
        if(this.checkOnlyCompanyID == "1")
            tcOption += "C ";

        if(this.specialRelayPlan == "1")
            tcOption += "SR ";
        tcOption += " ";

		document.onmouseup = null;
		this.weekPlanner.container.onmousemove = null;
		$('weekScheduler_appointment_time_' + this.id).innerHTML = tcOption + this.getTime();
		
	},
	
	resize: function (event){

       var height = Event.pointerY(event) - this.divElement.style.top.replace("px","") - this.divElement.offsetParent.offsetTop + this.weekPlanner.getScrollTop();

       if(height > 0)
       {
	       this.divElement.style.height = height + "px"; 
       }
	   this.duration = this.getDuration();

        var tcOption = "";
        if(this.pinCode == "1")
            tcOption += "PC ";
        if(this.exitingOnly == "1")
            tcOption += "EO ";

        if(this.unlocking == "1" && this.supWithoutPermAlarm == "0")
            tcOption += "U ";
        if(this.unlocking == "1" && this.supWithoutPermAlarm == "1")
            tcOption += "UA ";
        if(this.supOpenTooLongAlarm == "1")
            tcOption += "A ";
        if(this.checkOnlyCompanyID == "1")
            tcOption += "C ";

        if(this.specialRelayPlan == "1")
            tcOption += "SR ";
        tcOption += " ";

       $('weekScheduler_appointment_time_' + this.id).innerHTML = tcOption + this.getTime();
	   this.weekPlanner.options.startTime.value = this.getTimeStart();

       if(this.getTimeEnd() == '0:00')
       {
           this.weekPlanner.options.endTime.value = "23:59";
       }
       else
           this.weekPlanner.options.endTime.value = this.getTimeEnd();
	},

    resize2: function(start, end)
    {
        if(end == "0:00")
            end = "23:59";

        var day = this.startDate.getDay();

        var hourStart = start.split(':');
        this.startDate = new Date(2007,2,11+day,hourStart[0],hourStart[1],0,0);

        var hourEnd = end.split(':');
        var tmpDate = new Date(2007,2,11+this.startDate.getDay()-1,hourEnd[0],hourEnd[1],0,0);

        tmpDate = tmpDate.getTime()-this.startDate.getTime();
        tmpDate = new Date(tmpDate);

        var duration = 0;
        
        if(tmpDate.getHours() > 0)
            duration = (tmpDate.getHours()-1) + ':' + tmpDate.getMinutes();
        else
            duration = '23:' + tmpDate.getMinutes();

        this.duration = duration;
		this.divElement.style.top 		= this.getTopPos() + 'px';
		this.divElement.style.height 	= this.getHeight() + 'px';

        var tcOption = "";
        if(this.pinCode == "1")
            tcOption += "PC ";
        if(this.exitingOnly == "1")
            tcOption += "EO ";

        if(this.unlocking == "1" && this.supWithoutPermAlarm == "0")
            tcOption += "U ";
        if(this.unlocking == "1" && this.supWithoutPermAlarm == "1")
            tcOption += "UA ";
        if(this.supOpenTooLongAlarm == "1")
            tcOption += "A ";
        if(this.checkOnlyCompanyID == "1")
            tcOption += "C ";

        if(this.specialRelayPlan == "1")
            tcOption += "SR ";
        tcOption += " ";


        $('weekScheduler_appointment_time_' + this.id).innerHTML = tcOption + this.getTime();


        this.weekPlanner.saveAppointment(this);
    },
	
	move: function (event) {
		
		var top = Event.pointerY(event) - this.divElement.offsetParent.offsetTop + this.weekPlanner.getScrollTop();
				
		if(this.weekPlanner.moveY(top,this.divElement))
        	this.divElement.style.top = top + "px";		
        	
        left = this.weekPlanner.getXPosition(Event.pointerX(event));
        
        if(left >= 0)
        	this.divElement.style.left = left + "px";
	   this.startDate = this.weekPlanner.getStartDateFromPosition(this.divElement);

        var tcOption = "";
        if(this.pinCode == "1")
            tcOption += "PC ";
        if(this.exitingOnly == "1")
            tcOption += "EO ";

        if(this.unlocking == "1" && this.supWithoutPermAlarm == "0")
            tcOption += "U ";
        if(this.unlocking == "1" && this.supWithoutPermAlarm == "1")
            tcOption += "UA ";
        if(this.supOpenTooLongAlarm == "1")
            tcOption += "A ";
        if(this.checkOnlyCompanyID == "1")
            tcOption += "C ";

        if(this.specialRelayPlan == "1")
            tcOption += "SR ";
        tcOption += " ";


       $('weekScheduler_appointment_time_' + this.id).innerHTML = tcOption + this.getTime();
		this.weekPlanner.options.startTime.value = this.getTimeStart();
		this.weekPlanner.options.endTime.value = this.getTimeEnd();

	},
	
	click: function(event){
		
		this.weekPlanner.setActiveItem(this);
		this.weekPlanner.options.startTime.value = this.getTimeStart();
		this.weekPlanner.options.endTime.value = this.getTimeEnd();

        if(this.pinCode == '1')
            this.weekPlanner.options.pinCode.checked = true ;
        else
            this.weekPlanner.options.pinCode.checked = false ;

        if(this.exitingOnly == '1')
            this.weekPlanner.options.exitingOnly.checked = true ;
        else
            this.weekPlanner.options.exitingOnly.checked = false ;

        if(this.specialRelayPlan == '1')
            this.weekPlanner.options.specialRelayPlan.checked = true ;
        else
            this.weekPlanner.options.specialRelayPlan.checked = false ;


        if(this.unlocking == '1')
            this.weekPlanner.options.unlocking.checked = true ;
        else
            this.weekPlanner.options.unlocking.checked = false ;

        if(this.supOpenTooLongAlarm == '1')
            this.weekPlanner.options.supOpenTooLongAlarm.checked = true ;
        else
            this.weekPlanner.options.supOpenTooLongAlarm.checked = false ;

        if(this.supWithoutPermAlarm == '1')
            this.weekPlanner.options.supWithoutPermAlarm.checked = true ;
        else
            this.weekPlanner.options.supWithoutPermAlarm.checked = false ;

        if(this.checkOnlyCompanyID == '1')
            this.weekPlanner.options.checkOnlyCompanyID.checked = true ;
        else
            this.weekPlanner.options.checkOnlyCompanyID.checked = false ;


		Event.stop(event);
		
	},
	
	blur: function(){
		this.divElement.className = 'weekScheduler_anAppointment';
	},
	
	focus: function(){
		this.divElement.className = 'weekScheduler_anAppointment_active';
	},
	
	remove: function (){
		new Effect.Fade(this.divElement.id,0,700,20);
		this.weekPlanner.options.startTime.value = '';
		this.weekPlanner.options.endTime.value = '';
	},
	
	show: function() {
		new Effect.Appear(this.divElement.id,1,210,700);
	},
	
	edit: function() {
		
		txtArea = this.divElement.getElementsByTagName('TEXTAREA')[0];
		txtArea.value = this.description;
		txtArea.style.display = 'block';
		txtArea.onkeydown = this.keydown.bindEvent(this);
		span = this.divElement.getElementsByTagName('SPAN')[0];
		span.style.display = 'none';
		txtArea.focus();
		this.isEditInProgress = true;		
	},
	
	endEdit: function() {
		
		txtArea = this.divElement.getElementsByTagName('TEXTAREA')[0];
		txtArea.style.display = 'none';
		span = this.divElement.getElementsByTagName('SPAN')[0];
		span.innerHTML = txtArea.value;
		span.style.display = 'block';
		this.description = txtArea.value;
		this.weekPlanner.saveAppointment(this);
		
		
	},
	
	keydown: function (event) {
		
		if(Event.keyCode(event) == 13)
			this.endEdit();
		
	}
	

	
};