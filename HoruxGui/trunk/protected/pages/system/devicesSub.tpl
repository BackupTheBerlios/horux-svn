<fieldset class="adminform">
    <legend><%[Horux Core Devices Information]%></legend>

    <table class="adminlist">
        <thead>
            <tr>
                <th width="250">
                    <%[ID]%>
                </th>
                <th>
                    <%[Name]%>
                </th>
                <th>
                    <%[Serial Number]%>
                </th>
                <th>
                    <%[Connection status]%>
                </th>
                <th>
                    <%[Firmware Version]%>
                </th>
                <th>
                    <%[Horux Controller]%>
                </th>
                <th>
                    <%[Details]%>
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

            <com:TRepeater ID="DeviceR" EnableViewState="false">

                <prop:ItemTemplate>
                    <tr style="background-color:#BFCFFF">
                        <td><%#$this->Data['id']%></td>
                        <td><%#$this->Data['name']%></td>
                        <td><%#$this->Data['serialNumber']%></td>
                        <td><%#$this->Data['isConnected']=='1'?'<span style="color:green">'.Prado::localize('Yes').'</span>':'<span style="color:red">'.Prado::localize('No').'</span>'%></td>
                        <td><%#$this->Data['firmwareVersion']%></td>
                        <td><%#$this->Data['horuxController']%></td>
                        <td>
                            <a href="./proxy/deviceInfo.php?id=<%#$this->Data['id']%>&port=<%#$this->Data['port']%>&host=<%#$this->Data['host']%>&mode=<%#$this->Data['mode']%>&saasdbname=<%#$this->Data['saasdbname']%>" title="<%[Device Info]%>" onClick="Modalbox.show(this.href, {title: this.title, width: 600, height: 600}); return false;" class="toolbar" >
                                <%[show]%>
                            </a>
                        </td>
                    </tr>
                </prop:ItemTemplate>

            </com:TRepeater>
        </tbody>
    </table>

</fieldset>