<div id="toolbar-box">
    <div class="t">
        <div class="t">
            <div class="t"></div>
        </div>
    </div>
    <div class="m" >
        <div class="toolbar" id="toolbar">
            <table class="toolbar">
                <tr>
                    <com:TConditional Condition="$this->getViewState('DefaultVisible','false') == 'true'">
                        <prop:TrueTemplate>
                                <td class="button" id="toolbar-default">
                                  <com:TLinkButton CssClass="toolbar" ID="default" OnClick="Page.onDefault">
                                    <span class="icon-32-default" title="<com:TTranslate Catalogue='messages' Text='Default' />"></span><com:TTranslate Catalogue="messages" Text="Default" />
                                  </com:TLinkButton>
                                </td>
                        </prop:TrueTemplate>
                    </com:TConditional>


                    <com:TConditional Condition="$this->getViewState('UnInstallVisible','false') == 'true'">
                        <prop:TrueTemplate>
                                <td class="button" id="toolbar-uninstall">
                                  <com:TLinkButton CssClass="toolbar" ID="uninstall" OnClick="Page.onUninstall">
                                    <span class="icon-32-delete" title="<com:TTranslate Catalogue='messages' Text='Uninstall' />"></span><com:TTranslate Catalogue="messages" Text="Uninstall" />
                                  </com:TLinkButton>
                                </td>
                        </prop:TrueTemplate>
                    </com:TConditional>


                    <com:TConditional Condition="$this->getViewState('AddAccessVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-new">
                              <com:TLinkButton CssClass="toolbar" ID="stop" OnClick="Page.onAddAccess">
                                <span class="icon-32-new" title="<com:TTranslate Catalogue='messages' Text='Add access' />"></span><com:TTranslate Catalogue="messages" Text="Add access" />
                              </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('StopVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-stop">
                              <com:TActiveLinkButton CssClass="toolbar" ID="stop" OnClick="Page.onStop">
                                <span class="icon-32-stop" title="<com:TTranslate Catalogue='messages' Text='Stop' />"></span><com:TTranslate Catalogue="messages" Text="Stop" />
                              </com:TActiveLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('StartVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-start">
                              <com:TActiveLinkButton CssClass="toolbar" ID="start" OnClick="Page.onStart">
                                <span class="icon-32-start" title="<com:TTranslate Catalogue='messages' Text='Start' />"></span><com:TTranslate Catalogue="messages" Text="Start" />
                              </com:TActiveLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('AttributeVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-new">
                              <com:TLinkButton CssClass="toolbar" ID="attribution" OnClick="Page.onAttribute">
                                <span class="icon-32-new" title="<com:TTranslate Catalogue='messages' Text='Attribute' />"></span><com:TTranslate Catalogue="messages" Text="Attribute" />
                              </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('UnAttributeVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-delete">
                              <com:TLinkButton CssClass="toolbar" ID="UnAttribution" OnClick="Page.onUnAttribute">
                                <span class="icon-32-delete" title="<com:TTranslate Catalogue='messages' Text='Unattribute' />"></span><com:TTranslate Catalogue="messages" Text="Unattribute" />
                              </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>


                    <com:TConditional Condition="$this->getViewState('RefreshVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-refresh">
                              <com:TActiveLinkButton CssClass="toolbar" ID="refresh" OnClick="Page.onRefresh">
                                <span class="icon-32-refresh" title="<com:TTranslate Catalogue='messages' Text='Refresh' />"></span><com:TTranslate Catalogue="messages" Text="Refresh" />
                              </com:TActiveLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('EditVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-edit">
                              <com:TLinkButton CssClass="toolbar" ID="edit" OnClick="Page.onEdit">
                                <span class="icon-32-edit" title="<com:TTranslate Catalogue='messages' Text='Edit' />"></span><com:TTranslate Catalogue="messages" Text="Edit" />
                              </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('AddVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-new">
                                <a href="<%= $this->Service->constructUrl($this->getViewState('AddUrl','')) %>" class="toolbar">
                                    <span class="icon-32-new" title="<com:TTranslate Catalogue='messages' Text='New' />"></span><com:TTranslate Catalogue="messages" Text="New" />
                                </a>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('DelVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-delete">
                              <com:TLinkButton CssClass="toolbar" ID="delete" OnClick="Page.onDelete">
                                <span class="icon-32-delete" title="<com:TTranslate Catalogue='messages' Text='Delete' />"></span><com:TTranslate Catalogue="messages" Text="Delete" />
                              </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('PrintVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-print">
                              <a href="<%= $this->getViewState('PrintUrl','') %>"  <%=  $this->getViewState('JsClickPrint',false) == false ? '' : "onClick=\"".$this->getViewState('JsClickPrint',false)."\"" %>  class="toolbar">
                                <span class="icon-32-print" title="<com:TTranslate Catalogue='messages' Text='Print' />"></span><com:TTranslate Catalogue="messages" Text="Print" />
                              </a>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('ApplyVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-apply">
                                <com:TLinkButton CssClass="toolbar" OnClick="Page.onApply" ValidationGroup="Group1">
                                  <span class="icon-32-apply" title="<com:TTranslate Catalogue='messages' Text='Apply' />"></span><com:TTranslate Catalogue="messages" Text="Apply" />
                                </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>


                    <com:TConditional Condition="$this->getViewState('SaveVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-save">
                                <com:TLinkButton CssClass="toolbar" OnClick="Page.onSave" ValidationGroup="Group1">
                                  <span class="icon-32-save" title="<com:TTranslate Catalogue='messages' Text='Save' />"></span><com:TTranslate Catalogue="messages" Text="Save" />
                                </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('CancelVisible','false') == 'true'">
                        <prop:TrueTemplate>

                            <td class="button" id="toolbar-cancel">
                                <com:TLinkButton CssClass="toolbar" OnClick="Page.onCancel">
                                    <span class="icon-32-cancel" title="<com:TTranslate Catalogue='messages' Text='Cancel' />"></span><com:TTranslate Catalogue="messages" Text="Cancel" />
                                </com:TLinkButton>
                            </td>

                        </prop:TrueTemplate>
                    </com:TConditional>


                    <com:TConditional Condition="$this->getViewState('HelpVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-help">
                                <a href="./proxy/index.php?page=<%= $this->getApplication()->getService()->getRequestedPagePath() %>&lang=<%= $this->getApplication()->getGlobalState('lang') %>" title="<%[Help]%>" onClick="Modalbox.show(this.href, {title: this.title, width: 600, height: 600}); return false;" class="toolbar" >
                                    <span class="icon-32-help" title="<com:TTranslate Catalogue='messages' Text='Help' />"></span><com:TTranslate Catalogue="messages" Text="Help" />
                                </a>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                </tr>
            </table>
        </div><!-- end class toolbar -->
        <com:TConditional Condition="$this->getViewState('IconAsset','') == ''">
            <prop:TrueTemplate>
                <div class="header <%= $this->getViewState('CssIcon','') %>" ><%= $this->getViewState('Title','') %> <span id="detected"></span> </div>
            </prop:TrueTemplate>
            <prop:FalseTemplate>
                <div class="header" style="background-image:url(<%= $this->getViewState('IconAsset','') %>)" ><%= $this->getViewState('Title','') %> <span id="detected"></span> </div>
            </prop:FalseTemplate>
        </com:TConditional>
        <div class="clr"></div>
    </div><!-- end class m -->

    <div class="b">
        <div class="b">
            <div class="b"></div>
        </div>
    </div>
</div><!-- end class toolbar-box -->

<div class="clr"></div>