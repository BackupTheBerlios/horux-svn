<div id="installer">

			<div class="t">
		<div class="t">
			<div class="t"></div>
		</div>
		</div>
		<div class="m">

				<h2><%[Administrator Settings:]%></h2>
				<div class="install-text">

				</div>
				<div class="install-body">

				<div class="t">
			<div class="t">
				<div class="t"></div>
			</div>
			</div>
			<div class="m">
							<h3 class="title-smenu" title="Basic"><%[Administrator]%></h3>
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
											<span id="dbhostnamemsg"><%[Administrator Username]%></span>
										</label>
										<br/>
			                              <com:TTextBox 
			                                CssClass="inputbox" 
			                                ID="admin_username"
			                                Width="50"
			                                Text="admin" />
									</td>
									<td>
											<com:TRequiredFieldValidator
											ValidationGroup="Group1" 
											ControlToValidate="admin_username" 
											Text="<%[This field is required]%>" 
											Display="Dynamic"/>	
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbusername">

											<span id="dbusernamemsg"><%[Password]%></span>
										</label>
										<br/>
			                              <com:TTextBox 
			                                CssClass="inputbox" 
			                                ID="admin_password"
			                                Width="50"
			                                TextMode="Password"
			                                 />
									</td>
									<td>
											<com:TRequiredFieldValidator
											ValidationGroup="Group1" 
											ControlToValidate="admin_password" 
											Text="<%[This field is required]%>" 
											Display="Dynamic"/>	

									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbpassword">
											<%[Confirmation]%>
										</label>
										<br/>
			                              <com:TTextBox 
			                                CssClass="inputbox" 
			                                ID="admin_confirm"
			                                Width="50"
			                                TextMode="Password"
			                                 />
			                                 

									</td>
									<td>
											<com:TCompareValidator
											    ValidationGroup="Group1"
											    ControlToValidate="admin_password"
											    ControlToCompare="admin_confirm"
											    Text="The password and the confirmation must have the same value." />
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


