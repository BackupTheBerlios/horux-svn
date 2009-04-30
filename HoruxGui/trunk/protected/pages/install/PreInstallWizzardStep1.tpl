<div id="installer">
	<div class="t">
		<div class="t">
			<div class="t"></div>
		</div>
	</div>
	<div class="m">

		<h2><%[Pre-installation check for Horux 1.0.0 Production/Stable]%></h2>



		<div class="install-body">
			<div class="t">
				<div class="t">
					<div class="t"></div>
				</div>
			</div>
			<div class="m">
		<com:THiddenField ID="isOk" value="true" /> 
		<com:TCustomValidator
		ValidationGroup="Group1"
		ControlToValidate="isOk"
		OnServerValidate="checkBaseParam"
		Text=<%[Configuration not ok]%> />
			
				<div class="install-text">
					<%[If any of these items is not supported (marked as <strong><font color="#ff0000">No</font></strong>) your system does not match the minimum requirements necessary. Please take the appropriate actions to correct the errors. Failure to do so could lead to your Horux installation not functioning correctly.]%>
				</div>
				
<br/><br/>						<fieldset>
				

							<table class="content">
							
							<tr>
								<td class="item" valign="top">
									<%[PHP Version]%>  >= 5.1.0
								</td>
								<td align= valign="top">
									<com:TLabel CssClass="Yes" ID="php_Version" text="<%[Yes]%>" />
									<span class="small">
									&nbsp;
									</span>
								</td>
							</tr>
							
							<tr>
								<td class="item" valign="top">
									- <%[XML support]%>
								</td>
								<td align= valign="top">
									<com:TLabel CssClass="Yes" ID="xml" text="<%[Yes]%>" />
									<span class="small">

									&nbsp;
									</span>
								</td>
							</tr>

							<tr>
								<td class="item" valign="top">
									- <%[ZIP support]%>
								</td>
								<td align= valign="top">
									<com:TLabel CssClass="Yes" ID="zip" text="<%[Yes]%>" />
									<span class="small">

									&nbsp;
									</span>
								</td>
							</tr>
							
							<tr>
								<td class="item" valign="top">
									- <%[MySQL support]%>
								</td>
								<td align= valign="top">

									<com:TLabel CssClass="Yes" ID="mysql" text="<%[Yes]%>" />
									<span class="small">
									&nbsp;
									</span>
								</td>
							</tr>

							<tr>
								<td class="item" valign="top">
									- <%[Sqlite support (optional)]%>
								</td>
								<td align= valign="top">

									<com:TLabel CssClass="Yes" ID="sqlite" text="<%[Yes]%>" />
									<span class="small">
									&nbsp;
									</span>
								</td>
							</tr>
							
													
							<tr>
								<td class="item" valign="top">
									- /protected/application_p.xml <%[Writeable]%>
								</td>

								<td align= valign="top">
									<com:TLabel CssClass="Yes" ID="application_xml" text="<%[Yes]%>" />
									<span class="small">
									&nbsp;
									</span>
								</td>
							</tr>

							
							<tr>
								<td valign="top" class="item">
								</td>
							</tr>
							</table>
							</fieldset>


			</div>

			<div class="b">
				<div class="b">
					<div class="b"></div>
				</div>
			</div>
			<div class="clr"></div>
		</div>
		
		<div class="newsection"></div>
			<h2><%[Recommended Settings:]%></h2>

			<div class="install-body">
				<div class="t">
					<div class="t">
				<div class="t"></div>
			</div>

		</div>
		<div class="m">

			<div class="install-text">
					<%[These settings are recommended for PHP in order to ensure full compatibility with Horux. However, Horux! will still operate if your settings do not quite match the recommended.]%>
			</div>

						<fieldset>
							<table class="content">
							<tr>
								<td class="toggle">
									<%[Directive]%>
								</td>
								<td class="toggle">
									<%[Suggest]%>
								</td>
								<td class="toggle">

									<%[Current]%>
								</td>
							</tr>
							
							<tr>
								<td class="item">
									Safe Mode:
								</td>
								<td class="toggle">
									Off
								</td>

								<td>
									<span class="Yes">
									<com:TLabel CssClass="Yes" ID="safe_mode" text="<%[Off]%>" />
									</span>
								<td>
							</tr>
							
							<tr>
								<td class="item">
									<%[Display the errors:]%>
								</td>

								<td class="toggle">
									On
								</td>
								<td>
									<span class="Yes">
									<com:TLabel CssClass="Yes" ID="errors" text="<%[Off]%>" />
									</span>
								<td>
							</tr>
							
							<tr>

								<td class="item">
									<%[File transfert:]%>
								</td>
								<td class="toggle">
									On
								</td>
								<td>
									<span class="Yes">
									<com:TLabel CssClass="Yes" ID="file_transfert" text="<%[Off]%>" />
									</span>

								<td>
							</tr>
							
							<tr>
								<td class="item">
									Magic Quotes Runtime:
								</td>
								<td class="toggle">
									Off
								</td>
								<td>

									<span class="Yes">
									<com:TLabel CssClass="Yes" ID="magic_quotes" text="<%[Off]%>" />
									</span>
								<td>
							</tr>
							
							<tr>
								<td class="item">
									Register Globals:
								</td>
								<td class="toggle">

									Off
								</td>
								<td>
									<span class="Yes">
									<com:TLabel CssClass="Yes" ID="register_global" text="<%[Off]%>" />
									</span>
								<td>
							</tr>
							
							<tr>
								<td class="item">

									Output Buffering:
								</td>
								<td class="toggle">
									Off
								</td>
								<td>
									<span class="Yes">
									<com:TLabel CssClass="Yes" ID="output_beffering" text="<%[Off]%>" />
									</span>
								<td>

							</tr>
							
							<tr>
								<td class="item">
									Session Auto Start:
								</td>
								<td class="toggle">
									Off
								</td>
								<td>
									<span class="Yes">

									<com:TLabel CssClass="Yes" ID="session_auto_start" text="<%[Off]%>" />
									</span>
								<td>
							</tr>
							
							</table>
						</fieldset>


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

<div class="clr"></div>
