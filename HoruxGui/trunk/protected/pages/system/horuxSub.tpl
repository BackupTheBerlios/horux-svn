<com:TRepeater ID="HoruxRepeater" EnableViewState="false">

<prop:ItemTemplate>
    
<fieldset class="adminform">
    <legend><%[Horux Core Information]%> - <%#$this->Data['name']%></legend>

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
                <td>
                    <%[Horux Core Version]%>:
                </td>
                <td>
                    <com:TLabel id="horuxVersion" Text="<%#$this->Data['horuxVersion']%>" />
                </td>
            </tr>

            <tr>
                <td>
                    <%[Horux time live]%>:
                </td>
                <td>
                    <com:TLabel id="horuxTimeLive"  Text="<%#$this->Data['horuxTimeLive']%>" />
                </td>
            </tr>

            <tr>
                <td>
                    <%[Last status update]%>:
                </td>
                <td>
                    <com:TLabel id="lastUpdate"  Text="<%#$this->Data['lastUpdate']%>" />
                </td>
            </tr>

        </tbody>
    </table>

</fieldset>

</prop:ItemTemplate>

</com:TRepeater>
