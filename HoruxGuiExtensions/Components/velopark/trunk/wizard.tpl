<fieldset class="adminform">
    <legend><%= Prado::localize("Subscription",array(),'velopark') %></legend>
    <table class="admintable" cellspacing="1">
        <tbody>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Select a subscription]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%= Prado::localize("Attribute a subscription",array(),'velopark') %></span>
                </td>
                <td>
                    <com:TListBox  ID="Subscription"
                                  DataTextField="name"
                                  DataValueField="id"
                                  Rows="20"
                                  />
                </td>
            </tr>


        </tbody>
    </table>
</fieldset>