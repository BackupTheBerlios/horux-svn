<fieldset class="adminform">
    <legend><%[System Information]%></legend>
    <table class="adminlist">
            <thead>
                <tr>
                    <th width="250">
                        <%[Setting]%>
                    </th>
                    <th>
                        <%[Value]%>
                    </th>
                </tr>
            </thead>
            <tfoot>
            <tr>
                <th colspan="2">
                    &nbsp;
                </th>
            </tr>
            </tfoot>
            <tbody>
            <tr>
                <td valign="top">
                    <strong><%[PHP built On]%>:</strong>
                </td>
                <td>
                    <%% echo php_uname(); %>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong><%[Prado]%>:</strong>
                </td>
                <td>
                    <%=  Prado::getVersion() %>
                </td>
            </tr>            
            <tr>
                <td>
                    <strong><%[Database Version]%>:</strong>
                </td>
                <td>
                    <%%
                        $db = $this->Application->getModule('horuxDb')->DbConnection;
                        $db->Active=true;
                        echo $this->db->DriverName.' V '.$db->getAttribute(PDO::ATTR_SERVER_VERSION);
                    %>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><%[PHP Version]%>:</strong>
                </td>
                <td>
                    <%% echo phpversion(); %>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><%[Web Server]%>:</strong>
                </td>
                <td>
                    <%% if (isset($_SERVER['SERVER_SOFTWARE'])) {
                        echo $_SERVER['SERVER_SOFTWARE'];
                    } else if (($sf = getenv('SERVER_SOFTWARE'))) {
                        echo $sf;
                    } else {
                        echo Prado::localize("n/a");
                    } %>                                                           
                </td>
            </tr>
            <tr>
                <td>
                    <strong><%[WebServer to PHP interface]%>:</strong>
                </td>
                <td>
                    <%%echo php_sapi_name(); %>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><%[Horux Version]%>:</strong>
                </td>
                <td>
                    <%$ HoruxVersion %>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><%[User Agent]%>:</strong>
                </td>
                <td>
                    <%% echo phpversion() <= "4.2.1" ? getenv( "HTTP_USER_AGENT" ) : $_SERVER['HTTP_USER_AGENT'];%>
                </td>
            </tr>
            </table>
</fieldset>