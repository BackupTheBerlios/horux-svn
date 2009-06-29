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

                    <com:TConditional Condition="$this->getViewState('RefreshVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-refresh">
                              <com:TActiveLinkButton CssClass="toolbar" ID="refresh" OnClick="$this->getViewState('OnRefresh','Page.onRefresh')">
                                <span class="icon-32-refresh" title="<com:TTranslate Catalogue="messages">Refresh</com:TTranslate>"></span><com:TTranslate Catalogue="messages">Refresh</com:TTranslate>
                              </com:TActiveLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('EditVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-edit">
                              <com:TLinkButton CssClass="toolbar" ID="edit" OnClick="Page.onEdit">
                                <span class="icon-32-edit" title="<com:TTranslate Catalogue="messages">Edit</com:TTranslate>"></span><com:TTranslate Catalogue="messages">Edit</com:TTranslate>
                              </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('AddVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-new">
                                <a href="<%= $this->Service->constructUrl($this->getViewState('AddUrl','')) %>" class="toolbar">
                                    <span class="icon-32-new" title="<com:TTranslate Catalogue="messages">New</com:TTranslate>"></span><com:TTranslate Catalogue="messages">New</com:TTranslate>
                                </a>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('DelVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-delete">
                              <com:TLinkButton CssClass="toolbar" ID="delete" OnClick="Page.onDelete">
                                <span class="icon-32-delete" title="<com:TTranslate Catalogue="messages">Delete</com:TTranslate>"></span><com:TTranslate Catalogue="messages">Delete</com:TTranslate>
                              </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('PrintVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-print">
                              <a href="<%= $this->getViewState('PrintUrl','') %>"  <%=  $this->getViewState('JsClickPrint',false) == false ? '' : "onClick=\"".$this->getViewState('JsClickPrint',false)."\"" %>  class="toolbar">
                                <span class="icon-32-print" title="<com:TTranslate Catalogue="messages">Print</com:TTranslate>"></span><com:TTranslate Catalogue="messages">Print</com:TTranslate>
                              </a>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('ApplyVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-apply">
                                <com:TLinkButton CssClass="toolbar" OnClick="Page.onApply" ValidationGroup="Group1">
                                  <span class="icon-32-apply" title="<com:TTranslate Catalogue="messages">Apply</com:TTranslate>"></span><com:TTranslate Catalogue="messages">Apply</com:TTranslate>
                                </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>


                    <com:TConditional Condition="$this->getViewState('SaveVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-save">
                                <com:TLinkButton CssClass="toolbar" OnClick="Page.onSave" ValidationGroup="Group1">
                                  <span class="icon-32-save" title="<com:TTranslate Catalogue="messages">Save</com:TTranslate>"></span><com:TTranslate Catalogue="messages">Save</com:TTranslate>
                                </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('CancelVisible','false') == 'true'">
                        <prop:TrueTemplate>

                            <td class="button" id="toolbar-cancel">
                                <com:TLinkButton CssClass="toolbar" OnClick="Page.onCancel">
                                    <span class="icon-32-cancel" title="<com:TTranslate Catalogue="messages">Cancel</com:TTranslate>"></span><com:TTranslate Catalogue="messages">Cancel</com:TTranslate>
                                </com:TLinkButton>
                            </td>

                        </prop:TrueTemplate>
                    </com:TConditional>


                    <com:TConditional Condition="$this->getViewState('HelpVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-help">
                                <a href="./proxy/index.php?page=<%= $this->getApplication()->getService()->getRequestedPagePath() %>&lang=<%= $this->getApplication()->getGlobalState('lang') %>" title="<%[Help]%>" onClick="Modalbox.show(this.href, {title: this.title, width: 600, height: 600}); return false;" class="toolbar" >
                                    <span class="icon-32-help" title="<com:TTranslate Catalogue="messages">Help</com:TTranslate>"></span><com:TTranslate Catalogue="messages">Help</com:TTranslate>
                                </a>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                </tr>
            </table>
        </div><!-- end class toolbar -->
        <div class="header <%= $this->getViewState('CssIcon','') %>"><%= $this->getViewState('Title','') %> <span id="detected"></span> </div>
        <div class="clr"></div>
    </div><!-- end class m -->

    <div class="b">
        <div class="b">
            <div class="b"></div>
        </div>
    </div>
</div><!-- end class toolbar-box -->

<div class="clr"></div>