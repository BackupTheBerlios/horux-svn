<fieldset class="adminform">
    <legend><%[Horux Core Plugins Information]%></legend>

    <table class="adminlist">
        <thead>
            <tr>
                <th width="250">
                    <%[Name]%>
                </th>
                <th>
                    <%[Description]%>
                </th>
                <th>
                    <%[Type]%>
                </th>
                <th>
                    <%[Version]%>
                </th>
                <th>
                    <%[Author]%>
                </th>
                <th>
                    <%[Copyright]%>
                </th>
                <th>
                    <%[Horux Controller]%>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th colspan="7">
                    &nbsp;
                </th>
            </tr>
        </tfoot>
        <tbody>

            <com:TRepeater ID="PluginsR" EnableViewState="false">

                <prop:ItemTemplate>
                    <tr style="background-color:#BFCFFF">
                        <td><%#$this->Data['name']%></td>
                        <td><%#$this->Data['description']%></td>
                        <td><%#$this->Data['type']%></td>
                        <td><%#$this->Data['version']%></td>
                        <td><%#$this->Data['author']%></td>
                        <td><%#$this->Data['copyright']%></td>
                        <td><%#$this->Data['horuxController']%></td>
                    </tr>
                </prop:ItemTemplate>

            </com:TRepeater>
        </tbody>
    </table>

</fieldset>
