<com:THiddenField ID="pictureName" value="" />

<script type="text/javascript"> 
    // <![CDATA[

    function genPinCode(pin_length)
    {
        document.getElementById( '<%= $this->pin_code->getClientID() %>' ).value = Math.floor(Math.random() * Math.pow( 10 , pin_length ) );
    }

    -->
</script>

<fieldset class="adminform">
    <legend><%[Global]%></legend>
    <table class="admintable" cellspacing="1">
        <tbody>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Select the sex for this user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)">* <%[Sex]%></span>
                </td>
                <td>
                    <com:TRadioButton
                        ID="sexF"
                        GroupName="RadioGroup"
                        Text="<%[Mrs.]%>"
                        Checked="1"
                        />
                    <com:TRadioButton
                        ID="sexM"
                        GroupName="RadioGroup"
                        Text="<%[Mr.]%>"
                        />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the name for this user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)">* <%[Name]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="name"
                        Width="50" />
                    <com:TRequiredFieldValidator
                        ValidationGroup="Group1"
                        ControlToValidate="name"
                        EnableClientScript="true"
                        Text="<%[This field is required]%>"
                        Display="Dynamic"/>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%[Enter the firstname for this user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)">* <%[Firstname]%></span></td>
                <td>
                    <com:TTextBox CssClass="text_area" ID="firstname" Width="50" Text=""/>
                    <com:TRequiredFieldValidator
                        ValidationGroup="Group1"
                        ControlToValidate="firstname"
                        Text="<%[This field is required]%>"
                        Display="Dynamic"/>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Select the language for this user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Language]%></span>
                </td>
                <td>
                    <com:TDropDownList ID="language"
						DataTextField="name"
						DataValueField="param" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"><span onmouseover="Tip('<%[Add the picture of the user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Picture]%></span></td>
                <td>
                    <com:TFileUpload
                        ID="picture_upload"
                        OnFileUpload="fileUploaded"
                        Attributes.maxlength="100000"
                        Attributes.accept = "image/*"
                        />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key"></td>
                <td>
                    <com:TImage ID="picture"/>
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the pin code for this user, if used]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)">* <%[PIN Code]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="pin_code"
                        Width="50" />
                    <a href="#" onClick="genPinCode(4)">4</a>&nbsp;/&nbsp;
                    <a href="#" onClick="genPinCode(5)">5</a>&nbsp;/&nbsp;
                    <a href="#" onClick="genPinCode(6)">6</a>&nbsp;/&nbsp;
                    <a href="#" onClick="genPinCode(7)">7</a>&nbsp;/&nbsp;
                    <a href="#" onClick="genPinCode(8)">8</a>
                </td>
            </tr>

                <tr>
                    <td valign="top" class="key">
                        <span onmouseover="Tip('<%[Enter the password for this user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Password]%></span>
                    </td>
                    <td>
                        <com:TTextBox
                            CssClass="text_area"
                            ID="password"
                            TextMode="Password"
                            Width="50" />
                        <com:TCustomValidator
                            ValidationGroup="Group1"
                            EnableClientScript="false"
                            ControlToValidate="password"
                            OnServerValidate="serverValidatePassword"
                            Display="Dynamic"
                            Text="<%[The password must be equal to the confirmation]%>" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" class="key">
                        <span onmouseover="Tip('<%[Enter the confirmation password for this user]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Confirmation]%></span>
                    </td>
                    <td>
                        <com:TTextBox
                            CssClass="text_area"
                            ID="confirmation"
                            TextMode="Password"
                            Width="50" />
                    </td>
                </tr>

        </tbody>
    </table>
</fieldset>

<!--<div class="Wizzard2">-->

<fieldset class="adminform">
    <legend><%[Personal information]%></legend>
    <table class="admintable" cellspacing="1">
        <tbody>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the personal street]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Street]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="street"
                        Width="50" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the personal zip number]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[ZIP]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="zip"
                        Width="50" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the personal city]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[City]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="city"
                        Width="50" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the personal country]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Country]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="country"
                        Width="50" />
                </td>
            </tr>


            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the personal phone number]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Phone]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="phone1"
                        Width="50" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the personal email]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Email]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="email1"
                        Width="50" />
                    <com:TEmailAddressValidator
                        ValidationGroup="Group1"
                        ControlToValidate="email1"
                        Text="<%[Invalid email address.]%>"
                        Display="Dynamic"
                        EnableClientScript="true" />

                </td>
            </tr>

        </tbody>
    </table>

</fieldset>

<fieldset class="adminform">
    <legend><%[Professional information]%></legend>
    <table class="admintable" cellspacing="1">
        <tbody>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the firme]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Firme]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="firme"
                        Width="50" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the department]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Department]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="department"
                        Width="50" />
                </td>
            </tr>


            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the professional street]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Street]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="street_pr"
                        Width="50" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the professional zip number]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[ZIP]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="zip_pr"
                        Width="50" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the professional city]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[City]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="city_pr"
                        Width="50" />
                </td>
            </tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the professional country]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Country]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="country_pr"
                        Width="50" />
                </td>
            </tr>


            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the professional phone number]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Phone]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="phone2"
                        Width="50" />
                </td>
            </tr>
			<tr>
				<td valign="top" class="key">
					<span onmouseover="Tip('<%[Enter the professional fax number]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Fax]%></span>
				</td>
				<td>
					<com:TTextBox
						CssClass="text_area"
						ID="fax"
						Width="50" />
				</td>
			</tr>

            <tr>
                <td valign="top" class="key">
                    <span onmouseover="Tip('<%[Enter the professional email]%>', BALLOON, true, BALLOONIMGPATH, './js/tip_balloon', OFFSETX, -10, TEXTALIGN, 'justify', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><%[Email]%></span>
                </td>
                <td>
                    <com:TTextBox
                        CssClass="text_area"
                        ID="email2"
                        Width="50" />
                    <com:TEmailAddressValidator
                        ValidationGroup="Group1"
                        ControlToValidate="email2"
                        Text="<%[Invalid email address.]%>"
                        Display="Dynamic"
                        EnableClientScript="true" />
                </td>
            </tr>

        </tbody>
    </table>

</fieldset>
<!--</div>-->