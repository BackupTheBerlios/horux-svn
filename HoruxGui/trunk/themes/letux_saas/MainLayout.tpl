<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<com:THead Title=<%$ SiteTitle %> >
<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Cache-Control" content="no-cache"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="shortcut icon" href="favicon.ico">

<link rel="stylesheet" type="text/css" href="./themes/letux_saas/css/general.css" />
<link rel="stylesheet" type="text/css" href="./themes/letux_saas/css/icon.css" />
<link rel="stylesheet" type="text/css" href="./themes/letux_saas/css/menu.css" />
<link rel="stylesheet" type="text/css" href="./themes/letux_saas/css/component.css" />
<link rel="stylesheet" type="text/css" href="./themes/letux_saas/css/horus.css" />

<link rel="stylesheet" type="text/css" href="./themes/letux_saas/css/rounded.css" />
<link rel="stylesheet" type="text/css" href="./js/modalbox/modalbox.css">
<link rel="stylesheet" type="text/css" href="./js/ThemeIE/theme.css" />

<!--[if IE 7]>
<link href="./themes/letux_saas/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

<!--[if lte IE 6]>
<link href="./themes/letux_saas/css/ie6.css" rel="stylesheet" type="text/css" />
<![endif]-->

</com:THead>
<body>
<com:TClientScript ScriptUrl="./js/JSCookMenu.js" />
<com:TClientScript ScriptUrl="./js/ThemeIE/theme.js" />
<com:TClientScript ScriptUrl="./js/effect.js" />
<com:TClientScript ScriptUrl="./js/wz_tooltip.js" />
<com:TClientScript ScriptUrl="./js/tip_balloon.js" />
<com:TClientScript ScriptUrl="./js/tip_centerwindow.js" />

<com:TClientScript PradoScripts="ajax" />

<div id="border-top">
    <div>
        <div>
            <span class="version"> <com:TTranslate Catalogue="letux" Text="Version" /> <%$ HoruxVersion %></span>
            <span class="title"><com:TLabel ID="site" Text=""/><com:TTranslate Catalogue="letux" Text="Access Control Managment" /></span>
        </div>
    </div>
</div>

<com:TForm ID="adminForm">
  <com:TClientScript ScriptUrl="./js/modalbox/modalbox.js" />

  <!-- Add the header box if we are not in the installation process-->
  <com:TConditional Condition="file_exists('./protected/runtime/.installed')">
    <prop:TrueTemplate>
        <com:Application.portlets.HeaderBox ID="mainMenu"/>
    </prop:TrueTemplate>
    <prop:FalseTemplate>
    </prop:FalseTemplate>
  </com:TConditional>

  <!-- main content -->
  <com:TContentPlaceHolder ID="Main" />
</com:TForm>

<noscript>
   <com:TTranslate Catalogue="letux" Text="!Warning! Javascript must be enabled for proper operation of the Administrator" />
</noscript>
<div id="border-bottom">
  <div>
    <div></div>
  </div>
</div>
<div id="footer">
    <p class="copyright">
        <a href="http://www.letux.ch" target="_blank">Horux ®</a>
        <com:TTranslate Catalogue="letux" Text="is Free Software released under the AGPL License" /> -  Powered by <a href="http://www.letux.ch" target="_blank">Letux Sàrl</a>
    </p>
</div>
</body>
</html>