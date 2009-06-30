<div id="installer">

    <div class="t">
        <div class="t">
            <div class="t"></div>
        </div>
    </div>
    <div class="m">

        <h2><%[Connection Settings:]%></h2>
        <div class="install-text">

            <p><%[Choice the server database.]%></p>
            <%[DB Server]%> <com:TDropDownList ID="dbServer" AutoPostBack="true" >
                <com:TListItem Value="mysql" Text="MySql" Selected="true" />
                <!--<com:TListItem Value="sqlite" Text="Sqlite (expérimentale)"  />-->
            </com:TDropDownList>

            <p><%[Enter the database username, password, and database name you wish to use with Horux. These must already exist for the database you are going to use.]%></p>

            <p><com:TCustomValidator
                    ID="dberror"
                    ValidationGroup="Group1"
                    ControlToValidate="dbServer"
                    OnServerValidate="createDb"
                    Text="" />
            </p>



        </div>
        <div class="install-body">

            <div class="t">
                <div class="t">
                    <div class="t"></div>
                </div>
            </div>
            <div class="m">
                <h3 class="title-smenu" title="Basic"><%[Basic Settings]%></h3>
                <div class="section-smenu">

                    <table class="content2">
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>

                            <td colspan="2">
                                <label for="vars_dbhostname">
                                    <span id="dbhostnamemsg"><%[ Host Name   ]%></span>
                                </label>
                                <br/>
                                <com:TTextBox
                                    CssClass="inputbox"
                                    ID="hostname"
                                    Width="50"
                                    Text="localhost" />
                            </td>
                            <td>

                                <em>
                                    <%[This is usually localhost or a host name provided by the administrator ]%>
                                </em>
                                <com:TRequiredFieldValidator
                                    ValidationGroup="Group1"
                                    ControlToValidate="hostname"
                                    Text="<%[This field is required]%>"
                                    Display="Dynamic"/>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label for="vars_dbusername">

                                    <span id="dbusernamemsg"><%[User Name]%></span>
                                </label>
                                <br/>
                                <com:TTextBox
                                    CssClass="inputbox"
                                    ID="username_db"
                                    Width="50"
                                    Text="root" />
                            </td>
                            <td>
                                <em>
                                    <%[This can be the default MySQL username root or a username provided by the administrator, or one that you have created whilst setting up your database server. ]%>
                                </em>
                                <com:TRequiredFieldValidator
                                    ValidationGroup="Group1"
                                    ControlToValidate="username_db"
                                    Text="<%[This field is required]%>"
                                    Display="Dynamic"/>

                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label for="vars_dbpassword">
                                    <%[Password]%>
                                </label>
                                <br/>
                                <com:TTextBox
                                    CssClass="inputbox"
                                    ID="password_db"
                                    TextMode="Password"
                                    Width="50" />

                            </td>
                            <td>

                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">

                                <label for="vars_dbname">
                                    <span id="dbnamemsg"><%[Database Name]%></span>
                                </label>
                                <br/>
                                <com:TTextBox
                                    CssClass="inputbox"
                                    ID="dbname"
                                    Width="50" />
                            </td>
                            <td>
                                <com:TRequiredFieldValidator
                                    ValidationGroup="Group1"
                                    ControlToValidate="dbname"
                                    Text="<%[This field is required]%>"
                                    Display="Dynamic"/>

                            </td>
                        </tr>
                    </table>
                    <br /><br />


                </div>

                <div class="clr"></div>
            </div>
            <div class="b">
                <div class="b">
                    <div class="b"></div>

                </div>
            </div>
            <div class="clr"></div>
        </div>
        <div class="clr"></div>
    </div>
    <div class="b">
        <div class="b">
            <div class="b"></div>

        </div>
    </div>
</div>
</div>
</div>


