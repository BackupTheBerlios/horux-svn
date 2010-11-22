<com:THiddenField ID="id" value="" />

<fieldset class="adminform">
    <legend><%= Prado::localize("General parameters", array(), "messages")  %></legend>
    <table class="admintable" cellspacing="1">
        <tbody>
            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%= Prado::localize('Enter a <strong>unique</strong> name for this device', array(), 'messages')  %>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)">* <%[Name]%></span>
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
                <td valign="top" class="key"><span onmouseover="Tip('<%= Prado::localize('Set if the device musst be active or not', array(), 'messages') %>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%= Prado::localize('Is Active', array(), 'messages') %></span></td>
                <td>
                    <com:TCheckBox Text="" ID="isActive" Checked="true" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%= Prado::localize('Allow to log the communication in a log file', array(), 'messages') %>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%= Prado::localize('Debug', array(), 'messages') %></span></td>
                <td>
                    <com:TCheckBox Text="" ID="isLog"/>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%= Prado::localize('Set on which controller the device is connected', array(), 'messages') %>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%= Prado::localize('Horux controller', array(), 'messages') %></span></td>
                <td>
                    <com:TDropDownList ID="horuxControllerId">

                    </com:TDropDownList>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%= Prado::localize('Set on which device this device<br/>is connected. Set none if the device is not connected to any device', array(), 'messages') %>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%= Prado::localize('Parent device', array(), 'messages') %></span></td>
                <td>
                    <com:TDropDownList ID="parent">

                    </com:TDropDownList>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%= Prado::localize('Define the access plugin used for the access control.<br/>If empty, all plugins are likely to give the access', array(), 'messages') %>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%= Prado::localize('Access Plugin Name', array(), 'messages') %></span></td>
                <td>
                <com:TTextBox CssClass="text_area" ID="accessPlugin" Width="50" />
            </tr>



            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%= Prado::localize('You could insert a description for this device.', array(), 'messages') %>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%= Prado::localize('Description', array(), 'messages') %></span></td>
                <td>
                    <com:TTextBox CssClass="text_area" ID="comment" Width="400px"/>
                </td>
            </tr>
        </tbody>
    </table>
</fieldset>