<div id="header-box">
    <div id="module-status">
        <com:TConditional Condition="$this->isAccess('system.Alarms')">
            <prop:TrueTemplate>
                <com:TTimeTriggeredCallback ID="CheckAlarm" Interval="60" OnCallback="onDispAlarm" StartTimerOnLoad="true" />
            </prop:TrueTemplate>
        </com:TConditional>

        <com:TLinkButton  ID="homeIcon" OnClick="onHome"><span class="homeIcon">&nbsp;</span></com:TLinkButton>

        <com:TPanel CssClass="accesslink" ID="accessLink" Visible="false">
          <span class="accesslink">&nbsp;</span>
        </com:TPanel>

        <com:TConditional Condition="$this->isAccess('system.Alarms')">
            <prop:TrueTemplate>
            <com:TActiveLinkButton ID="alarmLabelButton" CssClass="alarm" OnClick="onCheckAlaram" >
                <com:TActiveLabel ID="alarmLabel"  Text="<%= $this->getAlarm() %>" />
            </com:TActiveLinkButton>
            </prop:TrueTemplate>
        </com:TConditional>

       	<com:TLabel ID="UserLogged" CssClass="loggedin-users" Text="<%= $this->getUserLogged() %>" />
        
        <span class="logout"><com:TActiveLinkButton  ID="logout" OnClick="onLogout" /></span>
    </div>

    <com:TConditional Condition="strtolower(substr($this->getApplication()->getService()->getRequestedPagePath(),-3,3)) != 'mod'">
        <prop:TrueTemplate>
            <div id="menu-box">
              <script type="text/javascript"><!--
              var myMenu = <%= $this->generateMenu() %>

              --></script>

                <div id="myMenuID"></div>
                <script type="text/javascript">
                <!--
                    var prop = cmClone (cmThemeIE);
                    prop.effect = new CMSlidingEffect (8);

                    cmDraw ('myMenuID', myMenu, 'hbr', prop);
                -->
                </script>
            </div>
        </prop:TrueTemplate>
        <prop:FalseTemplate>
            <div id="menu-box">
                <%= $this->generateMenuDisabled() %>
            </div>
        </prop:FalseTemplate>
    </com:TConditional>
</div>
