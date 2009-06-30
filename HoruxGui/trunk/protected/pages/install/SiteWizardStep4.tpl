<div id="installer">

    <div class="t">
        <div class="t">
            <div class="t"></div>
        </div>
    </div>
    <div class="m">

        <h2><%[Site configuration:]%></h2>
        <div class="install-text">

            <p><%[Enter the name of the access site.]%></p>

        </div>
        <div class="install-body">

            <div class="t">
                <div class="t">
                    <div class="t"></div>
                </div>
            </div>
            <div class="m">
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
                                    <span id="dbhostnamemsg"><%[Site Name]%></span>
                                </label>
                                <br/>
                                <com:TTextBox
                                    CssClass="inputbox"
                                    ID="sitename"
                                    Width="50"
                                    Text="Site" />
                            </td>
                            <td>
                                <com:TRequiredFieldValidator
                                    ValidationGroup="Group1"
                                    ControlToValidate="sitename"
                                    Text="<%[This field is required]%>"
                                    Display="Dynamic"/>
                                <com:TCustomValidator
                                    ValidationGroup="Group1"
                                    ControlToValidate="sitename"
                                    OnServerValidate="addSite"
                                    Text="" />
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


