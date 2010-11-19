<com:THiddenField ID="id" value="" />

<fieldset class="adminform">
    <legend><%[General parameters]%></legend>
    <table class="admintable" cellspacing="1">
        <tbody>
            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter a <strong>unique</strong> name for this device]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)">* <%[Name]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="name"
                        Width="50" />
                    <com:TCustomValidator
                        ValidationGroup="Group1"
                        EnableClientScript="false"
                        ControlToValidate="name"
                        OnServerValidate="serverValidateName"
                        Display="Dynamic"
                        Text="<%[This name is already used]%>" />
                    <com:TRequiredFieldValidator
                        ValidationGroup="Group1"
                        ControlToValidate="name"
                        Text="<%[This field is required]%>"
                        Display="Dynamic"/>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%[Set if the device musst be active or not]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Is Active]%></span></td>
                <td>
                    <com:TCheckBox Text="" ID="isActive" Checked="true" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%[Allow to log the communication in a log file]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Debug]%></span></td>
                <td>
                    <com:TCheckBox Text="" ID="isLog"/>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%[Set on which controller the device is connected ]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Horux controller]%></span></td>
                <td>
                    <com:TDropDownList ID="horuxControllerId">

                    </com:TDropDownList>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%[Set on which device this device<br/>is connected. Set none if the device is not connected to any device]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Parent device]%></span></td>
                <td>
                    <com:TDropDownList ID="parent">

                    </com:TDropDownList>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%[Define the access plugin used for the access control.<br/>If empty, all plugins are likely to give the access]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Access Plugin Name]%></span></td>
                <td>
                <com:TTextBox CssClass="text_area" ID="accessPlugin" Width="50" />
            </tr>



            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%[You could insert a description for this device.]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Description]%></span></td>
                <td>
                    <com:TTextBox CssClass="text_area" ID="comment" Width="400px"/>
                </td>
            </tr>
        </tbody>
    </table>
</fieldset>