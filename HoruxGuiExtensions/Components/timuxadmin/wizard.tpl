<fieldset class="adminform">
    <legend><%= Prado::localize("Super user",array(),'messages') %></legend>
    <table class="admintable" cellspacing="1">
        <tbody>

                <tr>
                    <td valign="top" class="key">
                        <span onmouseover="Tip('<%[Enter the username for this super user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Username]%></span>
                    </td>
                    <td>
                        <com:TTextBox
                            CssClass="text_area"
                            ID="su_username"
                            Width="50" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" class="key">
                        <span onmouseover="Tip('<%[Enter the password for this super user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Password]%></span>
                    </td>
                    <td>
                        <com:TTextBox
                            CssClass="text_area"
                            ID="su_password"
                            TextMode="Password"
                            Width="50" />
                    </td>
                </tr>


                <tr>
                    <td valign="top" class="key">
                        <span onmouseover="Tip('<%[Select super user group]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Group]%></span>
                    </td>
                    <td>
                        <com:TDropDownList ID="group_id"
                            DataTextField="name"
                            DataValueField="id"
                        />
                    </td>
                </tr>

        </tbody>
    </table>
</fieldset>