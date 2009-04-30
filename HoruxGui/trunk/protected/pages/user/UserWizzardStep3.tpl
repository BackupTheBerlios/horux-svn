 <script language="Javascript">

  var serial = new Array();
  var counter = 0;
  var serialDetected = false;

  document.onkeydown = function(evt) {

  var kc = 0;
 
  if(evt)
  {
    kc = evt.keyCode;   // firefox
  }
  else
  {
    kc = window.event.keyCode;  // IE
  }
  
  if(kc == 16 || kc==17 || kc ==18)
    return false;
  
  if(kc == 51 && (counter == 0 || counter == 1))
  {
    serial.push(51);
    counter++;
    if(counter == 2 && serial[0] == 51 && serial[1] == 51)
    {
      serialDetected = true;
    }
  }
  else
  {
    if(kc == 51 && serialDetected)
    {
      serial.push(51);
      counter++;

      if(serial[serial.length-1] == 51 && serial[serial.length-2] == 51)
      {
        
        var sn = "";
        for(i=2; i<serial.length-2; i++)
          sn += String.fromCharCode(serial[i]);
        
        
        //window.location.replace('index.php?page=user.UserWizzard&sn=' + sn);
        document.getElementById('serialNumber').value = sn;
        document.getElementById('ctl0_adminForm').submit();

        counter = 0;
        delete serial;
        serial = new Array();
        serialDetected = false;
      }
    }
    else
    {
      if(serialDetected)
      {
        serial.push(kc);
        counter++;
      }
      else
      {
        counter = 0;
        delete serial;
        serial = new Array();
        serialDetected = false;
      }
    }
    
  }
  
  return false;
}


</script>
 <input type="hidden" name="serialNumber" id="serialNumber" value="" />
 <fieldset class="adminform">
 <legend><%[Keys]%></legend>
  <table class="admintable" cellspacing="1">
    <tbody>

      <tr>
        <td valign="top" class="key">
          <span onmouseover="Tip('<%[Select all the keys<br/>attributes to this user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Attribute the key]%></span>
        </td>
        <td>
			<com:TListBox SelectionMode="Multiple" ID="UnusedKey"
				DataTextField="identificator"
				DataValueField="id"
				Rows="20"
			/>
       </td> 
      </tr>

      
    </tbody>
  </table>
</fieldset>				
