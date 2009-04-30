<fieldset class="adminform">
    <legend><%[Directory Permissions]%></legend>
        <table class="adminlist">
        <thead>
            <thead>
                <tr>
                    <th width="650">
                        <%[Directory]%>
                    </th>
                    <th>
                        <%[Status]%>
                    </th>
                </tr>
            </thead>
        </thead>
        <tfoot>
            <td colspan="2">
                &nbsp;
            </td>
        </tfoot>
        <tbody>
            <tr>
                <td class="item">/assets</td>
                <td>
                <%% if(is_writable("./assets"))
                    echo '<b><font color="green">'.Prado::localize("Writeable").'</font></b>';
                  else
                    echo '<b><font color="red">'.Prado::localize("Unwriteable").'</font></b>';
                %>
                </td>
            </tr>
            <tr>
                <td class="item"><strong><%[Devices Directory]%></strong> /protected/pages/hardware/device</td>
                <td>
                <%% if(is_writable("./protected/pages/hardware/device"))
                    echo '<b><font color="green">'.Prado::localize("Writeable").'</font></b>';
                  else
                    echo '<b><font color="red">'.Prado::localize("Unwriteable").'</font></b>';
                %>
                </td>
            </tr>
            <tr>
                <td class="item"><strong><%[Components Directory]%></strong> /protected/pages/components</td>
                <td>
                <%% if(is_writable("./protected/pages/components"))
                    echo '<b><font color="green">'.Prado::localize("Writeable").'</font></b>';
                  else
                    echo '<b><font color="red">'.Prado::localize("Unwriteable").'</font></b>';
                %>
                </td>
            </tr>
            <tr>
                <td class="item"><strong><%[Cache Directory]%></strong> /protected/runtime</td>
                <td>
                <%% if(is_writable("./protected/runtime"))
                    echo '<b><font color="green">'.Prado::localize("Writeable").'</font></b>';
                  else
                    echo '<b><font color="red">'.Prado::localize("Unwriteable").'</font></b>';
                %>
                </td>
            </tr>
            <tr>
                <td class="item"><strong><%[Templates Directory]%></strong> /themes</td>
                <td>
                <%% if(is_writable("./themes"))
                    echo '<b><font color="green">'.Prado::localize("Writeable").'</font></b>';
                  else
                    echo '<b><font color="red">'.Prado::localize("Unwriteable").'</font></b>';
                %>
                </td>
            </tr>
            <tr>
                <td class="item"><strong><%[Languages Directory]%></strong> /protected/messages</td>
                <td>
                <%% if(is_writable("./protected/messages"))
                    echo '<b><font color="green">'.Prado::localize("Writeable").'</font></b>';
                  else
                    echo '<b><font color="red">'.Prado::localize("Unwriteable").'</font></b>';
                %>
                </td>
            </tr>
            <tr>
                <td class="item"><strong><%[Pictures Directory]%></strong> /pictures</td>
                <td>
                <%% if(is_writable("./pictures"))
                    echo '<b><font color="green">'.Prado::localize("Writeable").'</font></b>';
                  else
                    echo '<b><font color="red">'.Prado::localize("Unwriteable").'</font></b>';
                %>
                </td>
            </tr>
            <tr>
                <td class="item"><strong><%[temp Directory]%></strong> /tmp</td>
                <td>
                <%% if(is_writable("./tmp"))
                    echo '<b><font color="green">'.Prado::localize("Writeable").'</font></b>';
                  else
                    echo '<b><font color="red">'.Prado::localize("Unwriteable").'</font></b>';
                %>
                </td>
            </tr>
        </tbody>
        </table>
</fieldset>