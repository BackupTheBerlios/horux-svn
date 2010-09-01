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

                    <com:TConditional Condition="$this->getViewState('SaveVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-save">
                                <com:TLinkButton ID="Save" CssClass="toolbar" OnClick="Page.onSave" ValidationGroup="Group1">
                                  <span class="icon-32-save" title="<com:TTranslate Catalogue='messages' Text='Save' />"></span><com:TTranslate Catalogue="messages" Text="Save" />
                                </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('AddConfVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-new">
                                <com:THyperLink ID="add" NavigateUrl="<%= $this->Service->constructUrl($this->getViewState('AddUrl','')) %>" CssClass="toolbar" >
                                    <span class="icon-32-new" title="<com:TTranslate Catalogue='messages' Text='New configuration' />"></span><com:TTranslate Catalogue="messages" Text="New configuration" />
                                </com:THyperLink>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('DelConfVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-delete">
                              <com:TLinkButton CssClass="toolbar" ID="delete" OnClick="Page.onDeleteConf">
                                <span class="icon-32-delete" title="<com:TTranslate Catalogue='messages' Text='Delete configuration' />"></span><com:TTranslate Catalogue="messages" Text="Delete configuration" />
                              </com:TLinkButton>
                            </td>
                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('CancelVisible','false') == 'true'">
                        <prop:TrueTemplate>

                            <td class="button" id="toolbar-cancel">
                                <com:TLinkButton ID="cancel" CssClass="toolbar" OnClick="Page.onCancel">
                                    <span class="icon-32-cancel" title="<com:TTranslate Catalogue='messages' Text='Cancel' />"></span><com:TTranslate Catalogue="messages" Text="Cancel" />
                                </com:TLinkButton>
                            </td>

                        </prop:TrueTemplate>
                    </com:TConditional>

                    <com:TConditional Condition="$this->getViewState('ImportVisible','false') == 'true'">
                        <prop:TrueTemplate>
                            <td class="button" id="toolbar-import">
                                <com:TLinkButton ID="Import" CssClass="toolbar" OnClick="Page.onImport" ValidationGroup="Group1">
                                  <span class="icon-32-import" title="<com:TTranslate Catalogue='messages' Text='Import' />"></span><com:TTranslate Catalogue="messages" Text="Import" />
                                </com:TLinkButton>
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
