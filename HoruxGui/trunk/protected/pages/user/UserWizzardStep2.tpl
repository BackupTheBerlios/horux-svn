<fieldset class="adminform">
    <legend><%[Groups]%></legend>
    <table class="admintable" cellspacing="1">
        <tbody>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Select all the groups<br/>attributes to this user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Attribute the group]%></span>
                </td>
                <td>
                    <com:TListBox SelectionMode="Multiple" ID="UnusedGroup"
                                  DataTextField="name"
                                  DataValueField="id"
                                  Rows="20"
                                  />
                </td>
            </tr>


        </tbody>
    </table>
</fieldset>				
