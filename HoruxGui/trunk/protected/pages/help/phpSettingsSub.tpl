<fieldset class="adminform">
    <legend><%[Relevant PHP Settings]%></legend>
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
                    <%[Safe Mode]%>:
                </td>
                <td>
                    <%%
                    $r =  (ini_get('safe_mode') == '1' ? 1 : 0);
                    echo $r ? Prado::localize('ON') : Prado::localize('OFF') ;
                    %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Open basedir]%>:
                </td>
                <td>
                    <%% echo (($ob = ini_get('open_basedir')) ? $ob : Prado::localize( 'none' ) ); %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Display Errors]%>:
                </td>
                <td>
                    <%%
                    $r =  (ini_get('display_errors') == '1' ? 1 : 0);
                    echo $r ? Prado::localize('ON') : Prado::localize('OFF') ;
                    %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Short Open Tags]%>:
                </td>
                <td>
                    <%%
                    $r =  (ini_get('short_open_tag') == '1' ? 1 : 0);
                    echo $r ? Prado::localize('ON') : Prado::localize('OFF') ;
                    %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[File Uploads]%>:
                </td>
                <td>
                    <%%
                    $r =  (ini_get('file_uploads') == '1' ? 1 : 0);
                    echo $r ? Prado::localize('ON') : Prado::localize('OFF') ;
                    %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Magic Quotes]%>:
                </td>
                <td>
                    <%%
                    $r =  (ini_get('magic_quotes_gpc') == '1' ? 1 : 0);
                    echo $r ? Prado::localize('ON') : Prado::localize('OFF') ;
                    %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Register Globals]%>:
                </td>
                <td>
                    <%%
                    $r =  (ini_get('register_globals') == '1' ? 1 : 0);
                    echo $r ? Prado::localize('ON') : Prado::localize('OFF') ;
                    %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Output Buffering]%>:
                </td>
                <td>
                    <%%
                    $r =  (ini_get('output_buffering') == '1' ? 1 : 0);
                    echo $r ? Prado::localize('ON') : Prado::localize('OFF') ;
                    %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Session save path]%>:
                </td>
                <td>
                    <%% echo (($sp=ini_get('session.save_path')) ? $sp : Prado::localize( 'none' ) ); %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Session auto start]%>:
                </td>
                <td>
                    <%% echo intval( ini_get( 'session.auto_start' ) ); %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[XML enabled]%>:
                </td>
                <td>
                    <%% echo extension_loaded('xml') ? Prado::localize( 'Yes' ) : Prado::localize( 'No' ); %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Zlib enabled]%>:
                </td>
                <td>
                    <%% echo extension_loaded('zlib') ? Prado::localize( 'Yes' ) : Prado::localize( 'No' ); %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Disabled Functions]%>:
                </td>
                <td>
                    <%% echo (($df=ini_get('disable_functions')) ? $df : Prado::localize( 'none' ) ); %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Mbstring enabled]%>:
                </td>
                <td>
                    <%% echo extension_loaded('mbstring') ? Prado::localize( 'Yes' ) : Prado::localize( 'No' ); %>
                </td>
            </tr>
            <tr>
                <td>
                    <%[Iconv available]%>:
                </td>
                <td>
                    <%% echo function_exists('iconv') ? Prado::localize( 'Yes' ) : Prado::localize( 'No' ); %>
                </td>
            </tr>
        </tbody>
    </table>
</fieldset>