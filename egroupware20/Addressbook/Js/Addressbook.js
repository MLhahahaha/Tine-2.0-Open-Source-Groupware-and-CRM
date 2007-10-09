Ext.namespace("Egw.Addressbook");Egw.Addressbook=function(){var H;var c=true;var m=false;var Q;var O;var q=function(b){if(!Q){Q=Ext.getCmp("contact-tree").getSelectionModel().getSelectedNode();}b.baseParams.datatype=Q.attributes.datatype;b.baseParams.owner=Q.attributes.owner;switch(Q.attributes.datatype){case "list":b.baseParams.listId=Q.attributes.listId;b.baseParams.method="Addressbook.getList";break;case "contacts":case "otherpeople":case "sharedaddressbooks":b.baseParams.method="Addressbook.getContacts";b.baseParams.options=Ext.encode({displayContacts:c,displayLists:m});break;case "overview":b.baseParams.method="Addressbook.getOverview";b.baseParams.options=Ext.encode({displayContacts:c,displayLists:m});break;}b.baseParams.query=Ext.getCmp("quickSearchField").getRawValue();};var S=function(f,b){i("contactWindow","index.php?method=Addressbook.editContact&_contactId=",850,600);};var X=function(f,b){i("listWindow","index.php?method=Addressbook.editList&_listId=",450,600);};var Z=function(n,b){var V=Array();var l=O.getSelectionModel().getSelections();for(var f=0;f<l.length;++f){V.push(l[f].id);}V=Ext.util.JSON.encode(V);Ext.Ajax.request({url:"index.php",params:{method:"Addressbook.deleteContacts",_contactIds:V},text:"Deleting contact...",success:function(R,I){H.reload();},failure:function(I,R){Ext.MessageBox.alert("Failed","Some error occured while trying to delete the conctact.");}});};var M=function(l,b){var V=O.getSelectionModel().getSelections();var f=V[0].id;if(V[0].data.contact_tid=="l"){i("listWindow","index.php?method=Addressbook.editList&_listId="+f,450,600);}else{i("contactWindow","index.php?method=Addressbook.editContact&_contactId="+f,850,600);}};var o=function(b,f){H.reload();};var N=new Ext.Action({text:"add contact",handler:S,iconCls:"action_addContact"});var E=new Ext.Action({text:"add list",handler:X,iconCls:"action_addList"});var j=new Ext.Action({text:"edit",disabled:true,handler:M,iconCls:"action_edit"});var W=new Ext.Action({text:"delete",disabled:true,handler:Z,iconCls:"action_delete"});var z=function(){var f=Ext.getCmp("north-panel");var V=Ext.getCmp("applicationToolbar");f.remove(V);var n=new Ext.Action({handler:T,enableToggle:true,pressed:c,iconCls:"x-btn-icon action_displayContacts"});var I=new Ext.Action({handler:p,enableToggle:true,pressed:m,iconCls:"x-btn-icon action_displayLists"});var l=new Ext.app.SearchField({id:"quickSearchField",width:240,emptyText:"enter searchfilter"});l.on("change",o);var b=new Ext.Toolbar({region:"south",id:"applicationToolbar",split:false,height:26,items:[N,E,j,W,"-","Display",": ",n,I,"->","Search:"," "," ",l]});f.add(b);f.doLayout();return ;};var r=function(){var I=Ext.getCmp("west");if(I.items){for(var f=0;f<I.items.length;f++){I.remove(I.items.get(f));}}var n=new Ext.tree.TreeLoader({dataUrl:"index.php"});n.on("beforeload",function(d,R){d.baseParams.method="Addressbook.getSubTree";d.baseParams._node=R.id;d.baseParams._datatype=R.attributes.datatype;d.baseParams._owner=R.attributes.owner;d.baseParams._location="mainTree";},this);var V=new Ext.tree.TreePanel({id:"contact-tree",loader:n,rootVisible:false,border:false});var b=new Ext.tree.TreeNode({text:"root",draggable:false,allowDrop:false,id:"root"});V.setRootNode(b);for(f=0;f<initialTree.length;f++){b.appendChild(new Ext.tree.AsyncTreeNode(initialTree[f]));}V.on("click",function(d,R){Q=d;H.reload();},this);I.add(V);I.show();I.doLayout();V.expandPath("/root/alllists");V.selectPath("/root/addressbook");return ;var b=V.getRootNode();var l=Array();b.eachChild(function(R){l.push(R);},this);b.appendChild(new Ext.tree.AsyncTreeNode(initialTree));V.expandPath("/root/addressbook");V.selectPath("/root/addressbook");V.on("click",function(R){H.reload();});};var L=function(){var b=Ext.getCmp("center-panel");if(b.items){for(var f=0;f<b.items.length;f++){b.remove(b.items.get(f));}}H=new Ext.data.JsonStore({url:"index.php",root:"results",totalProperty:"totalcount",id:"contact_id",fields:[{name:"contact_id"},{name:"contact_tid"},{name:"contact_owner"},{name:"contact_private"},{name:"cat_id"},{name:"n_family"},{name:"n_given"},{name:"n_middle"},{name:"n_prefix"},{name:"n_suffix"},{name:"n_fn"},{name:"n_fileas"},{name:"contact_bday"},{name:"org_name"},{name:"org_unit"},{name:"contact_title"},{name:"contact_role"},{name:"contact_assistent"},{name:"contact_room"},{name:"adr_one_street"},{name:"adr_one_street2"},{name:"adr_one_locality"},{name:"adr_one_region"},{name:"adr_one_postalcode"},{name:"adr_one_countryname"},{name:"contact_label"},{name:"adr_two_street"},{name:"adr_two_street2"},{name:"adr_two_locality"},{name:"adr_two_region"},{name:"adr_two_postalcode"},{name:"adr_two_countryname"},{name:"tel_work"},{name:"tel_cell"},{name:"tel_fax"},{name:"tel_assistent"},{name:"tel_car"},{name:"tel_pager"},{name:"tel_home"},{name:"tel_fax_home"},{name:"tel_cell_private"},{name:"tel_other"},{name:"tel_prefer"},{name:"contact_email"},{name:"contact_email_home"},{name:"contact_url"},{name:"contact_url_home"},{name:"contact_freebusy_uri"},{name:"contact_calendar_uri"},{name:"contact_note"},{name:"contact_tz"},{name:"contact_geo"},{name:"contact_pubkey"},{name:"contact_created"},{name:"contact_creator"},{name:"contact_modified"},{name:"contact_modifier"},{name:"contact_jpegphoto"},{name:"account_id"}],remoteSort:true});H.setDefaultSort("n_family","asc");H.loadData({"results":[],"totalcount":"0","status":"success"});H.on("beforeload",q);H.load({params:{start:0,limit:50}});var V=new Ext.PagingToolbar({pageSize:25,store:H,displayInfo:true,displayMsg:"Displaying contacts {0} - {1} of {2}",emptyMsg:"No contacts to display"});var l=new Ext.grid.ColumnModel([{resizable:true,id:"contact_tid",header:"Type",dataIndex:"contact_tid",width:30,renderer:a},{resizable:true,id:"n_family",header:"Family name",dataIndex:"n_family"},{resizable:true,id:"n_given",header:"Given name",dataIndex:"n_given",width:80},{resizable:true,id:"n_fn",header:"Full name",dataIndex:"n_fn",hidden:true},{resizable:true,id:"n_fileas",header:"Name + Firm",dataIndex:"n_fileas",hidden:true},{resizable:true,id:"contact_email",header:"eMail",dataIndex:"contact_email",width:150,hidden:false},{resizable:true,id:"contact_bday",header:"Birthday",dataIndex:"contact_bday",hidden:true},{resizable:true,id:"org_name",header:"Organisation",dataIndex:"org_name",width:150},{resizable:true,id:"org_unit",header:"Unit",dataIndex:"org_unit",hidden:true},{resizable:true,id:"contact_title",header:"Title",dataIndex:"contact_title",hidden:true},{resizable:true,id:"contact_role",header:"Role",dataIndex:"contact_role",hidden:true},{resizable:true,id:"contact_room",header:"Room",dataIndex:"contact_room",hidden:true},{resizable:true,id:"adr_one_street",header:"Street",dataIndex:"adr_one_street",hidden:true},{resizable:true,id:"adr_one_locality",header:"Locality",dataIndex:"adr_one_locality",width:80,hidden:false},{resizable:true,id:"adr_one_region",header:"Region",dataIndex:"adr_one_region",hidden:true},{resizable:true,id:"adr_one_postalcode",header:"Postalcode",dataIndex:"adr_one_postalcode",hidden:true},{resizable:true,id:"adr_one_countryname",header:"Country",dataIndex:"adr_one_countryname",hidden:true},{resizable:true,id:"adr_two_street",header:"Street (private)",dataIndex:"adr_two_street",hidden:true},{resizable:true,id:"adr_two_locality",header:"Locality (private)",dataIndex:"adr_two_locality",hidden:true},{resizable:true,id:"adr_two_region",header:"Region (private)",dataIndex:"adr_two_region",hidden:true},{resizable:true,id:"adr_two_postalcode",header:"Postalcode (private)",dataIndex:"adr_two_postalcode",hidden:true},{resizable:true,id:"adr_two_countryname",header:"Country (private)",dataIndex:"adr_two_countryname",hidden:true},{resizable:true,id:"tel_work",header:"Phone",dataIndex:"tel_work",hidden:false},{resizable:true,id:"tel_cell",header:"Cellphone",dataIndex:"tel_cell",hidden:false},{resizable:true,id:"tel_fax",header:"Fax",dataIndex:"tel_fax",hidden:true},{resizable:true,id:"tel_car",header:"Car phone",dataIndex:"tel_car",hidden:true},{resizable:true,id:"tel_pager",header:"Pager",dataIndex:"tel_pager",hidden:true},{resizable:true,id:"tel_home",header:"Phone (private)",dataIndex:"tel_home",hidden:true},{resizable:true,id:"tel_fax_home",header:"Fax (private)",dataIndex:"tel_fax_home",hidden:true},{resizable:true,id:"tel_cell_private",header:"Cellphone (private)",dataIndex:"tel_cell_private",hidden:true},{resizable:true,id:"contact_email_home",header:"eMail (private)",dataIndex:"contact_email_home",hidden:true},{resizable:true,id:"contact_url",header:"URL",dataIndex:"contact_url",hidden:true},{resizable:true,id:"contact_url_home",header:"URL (private)",dataIndex:"contact_url_home",hidden:true},{resizable:true,id:"contact_note",header:"Note",dataIndex:"contact_note",hidden:true},{resizable:true,id:"contact_tz",header:"Timezone",dataIndex:"contact_tz",hidden:true},{resizable:true,id:"contact_geo",header:"Geo",dataIndex:"contact_geo",hidden:true}]);l.defaultSortable=true;O=new Ext.grid.GridPanel({store:H,cm:l,tbar:V,autoSizeColumns:false,selModel:new Ext.grid.RowSelectionModel({multiSelect:true}),enableColLock:false,autoExpandColumn:"n_family",border:false});b.add(O);b.show();b.doLayout();O.on("rowclick",function(d,R,I){var n=O.getSelectionModel().getCount();if(n<1){j.setDisabled(true);W.setDisabled(true);}else{if(n==1){j.setDisabled(false);W.setDisabled(false);}else{j.setDisabled(true);W.setDisabled(false);}}});O.on("rowcontextmenu",function(R,d,I){I.stopEvent();var n=R.getStore().getAt(d);u.showAt(I.getXY());});O.on("rowdblclick",function(I,R,d){var n=I.getStore().getAt(R);if(n.data.contact_tid=="l"){try{i("listWindow","index.php?method=Addressbook.editList&_listId="+n.data.contact_id,450,600);}catch(F){}}else{try{i("contactWindow","index.php?method=Addressbook.editContact&_contactId="+n.data.contact_id,850,600);}catch(F){}}});return ;textF1=new Ext.form.TextField({height:22,width:200,emptyText:"Suchparameter ...",allowBlank:false});textF1.on("specialkey",function(I,n){if(n.getKey()==n.ENTER||n.getKey()==e.RETURN){}});};var a=function(l,I,V,b,f,n){switch(l){case "l":return "<img src='images/oxygen/16x16/actions/users.png' width='12' height='12' alt='list'/>";default:return "<img src='images/oxygen/16x16/actions/user.png' width='12' height='12' alt='contact'/>";}};var T=function(f,b){c=f.pressed;H.reload();};var p=function(f,b){m=f.pressed;H.reload();};var u=new Ext.menu.Menu({id:"ctxMenuAddress",items:[j,W,"-",N,E]});var v=function(f,b){};var i=function(f,R,b,I){if(document.all){w=document.body.clientWidth;h=document.body.clientHeight;x=window.screenTop;y=window.screenLeft;}else{if(window.innerWidth){w=window.innerWidth;h=window.innerHeight;x=window.screenX;y=window.screenY;}}var n=((w-b)/2)+y;var l=((h-I)/2)+x;var V=window.open(R,f,"width="+b+",height="+I+",top="+l+",left="+n+",directories=no,toolbar=no,location=no,menubar=no,scrollbars=no,status=no,resizable=no,dependent=no");return V;};var K=function(d,V){var f;var Y=1024,R=786;var l=850,F=600;if(V=="list"){l=450,F=600;}if(document.all){Y=document.body.clientWidth;R=document.body.clientHeight;x=window.screenTop;y=window.screenLeft;}else{if(window.innerWidth){Y=window.innerWidth;R=window.innerHeight;x=window.screenX;y=window.screenY;}}var n=((Y-l)/2)+y,I=((R-F)/2)+x;if(V=="list"&&!d){f="index.php?method=Addressbook.editList";}else{if(V=="list"&&d){f="index.php?method=Addressbook.editList&contactid="+d;}else{if(V!="list"&&d){f="index.php?method=Addressbook.editContact&contactid="+d;}else{f="index.php?method=Addressbook.editContact";}}}appId="addressbook";var b=window.open(f,"popupname","width="+l+",height="+F+",top="+I+",left="+n+",directories=no,toolbar=no,location=no,menubar=no,scrollbars=no,status=no,resizable=no,dependent=no");return ;};var A=function(b){b=(b==null)?false:b;window.opener.Egw.Addressbook.reload();if(b==true){window.setTimeout("window.close()",400);}};var k=function(l){if(!V){var V=new Ext.Window({title:"please select addressbook",modal:true,width:375,height:400,minWidth:375,minHeight:400,layout:"fit",plain:true,bodyStyle:"padding:5px;",buttonAlign:"center"});var n=Ext.tree;treeLoader=new n.TreeLoader({dataUrl:"index.php"});treeLoader.on("beforeload",function(R,I){R.baseParams.method="Addressbook.getSubTree";R.baseParams._node=I.id;R.baseParams._datatype=I.attributes.datatype;R.baseParams._owner=I.attributes.owner;R.baseParams._location="selectFolder";},this);var b=new n.TreePanel({animate:true,id:"addressbookTree",loader:treeLoader,containerScroll:true,rootVisible:false});var f=new n.TreeNode({text:"root",draggable:false,allowDrop:false,id:"root"});b.setRootNode(f);Ext.each(application,function(I){f.appendChild(new n.AsyncTreeNode(I));});b.on("click",function(d){d.select();if(b.getSelectionModel().getSelectedNode()){var F=b.getSelectionModel().getSelectedNode().id;var R=b.getNodeById(F).attributes.owner;if((R>0)||(R<0)){var I=Ext.getCmp("addressbook_");I.setValue(R);V.hide();}else{Ext.MessageBox.alert("wrong selection","please select a valid addressbook");}}else{Ext.MessageBox.alert("no selection","please select an addressbook");}});V.add(b);V.show();}};return {show:function(b){z();r();L(b);},displayAddressbookSelectDialog:k,reload:function(){H.reload();}};}();Egw.Addressbook.ContactEditDialog=function(){var r;var v;var m;var p=function(T,o){var k=Ext.getCmp("contactDialog").getForm();k.render();if(k.isValid()){var c={};if(formData.values){c.contact_id=formData.values.contact_id;}k.submit({waitTitle:"Please wait!",waitMsg:"saving contact...",params:c,success:function(E,i,X){window.opener.Egw.Addressbook.reload();},failure:function(E,i){}});}else{Ext.MessageBox.alert("Errors","Please fix the errors noted.");}};var u=function(T,o){var k=Ext.getCmp("contactDialog").getForm();k.render();if(k.isValid()){var c={};if(formData.values){c.contact_id=formData.values.contact_id;}k.submit({waitTitle:"Please wait!",waitMsg:"saving contact...",params:c,success:function(E,i,X){window.opener.Egw.Addressbook.reload();window.setTimeout("window.close()",400);},failure:function(E,i){}});}else{Ext.MessageBox.alert("Errors","Please fix the errors noted.");}};var K=function(c,k){var o=Ext.util.JSON.encode([formData.values.contact_id]);Ext.Ajax.request({url:"index.php",params:{method:"Addressbook.deleteContacts",_contactIds:o},text:"Deleting contact...",success:function(E,T){window.opener.Egw.Addressbook.reload();window.setTimeout("window.close()",400);},failure:function(T,E){Ext.MessageBox.alert("Failed","Some error occured while trying to delete the conctact.");}});};var H=new Ext.Action({text:"save and close",handler:u,iconCls:"action_saveAndClose"});var L=new Ext.Action({text:"apply changes",handler:p,iconCls:"action_applyChanges"});var q=new Ext.Action({text:"delete contact",handler:K,iconCls:"action_delete"});var z=function(){Ext.QuickTips.init();Ext.form.Field.prototype.msgTarget="side";var V=true;if(formData.values){V=false;}var O=new Ext.Toolbar({region:"south",id:"applicationToolbar",split:false,height:26,items:[H,L,q]});var b=new Ext.data.JsonStore({url:"index.php",baseParams:{method:"Egwbase.getCountryList"},root:"results",id:"shortName",fields:["shortName","translatedName"],remoteSort:false});var i=new Ext.form.TriggerField({fieldLabel:"Addressbook",name:"contact_owner",id:"addressbook_",anchor:"95%",readOnly:true});i.onTriggerClick=function(){Egw.Addressbook.displayAddressbookSelectDialog(M);};var k=function(l){l.baseParams.method="Addressbook.getContacts";l.baseParams.options=Ext.encode({displayContacts:false,displayLists:true});};var f=[["AL","Alabama"],["AK","Alaska"],["AZ","Arizona"],["WV","West Virginia"],["WI","Wisconsin"],["WY","Wyoming"]];var c=new Ext.data.SimpleStore({fields:["contact_id","contact_tid"],data:f});var o=new Ext.data.SimpleStore({fields:["contact_id","contact_tid"],});var A=new Ext.DataView({style:"overflow:auto",singleSelect:true,itemSelector:"div.thumb-wrap",store:c,tpl:new Ext.XTemplate("<tpl for=\".\">","<div class=\"thumb-wrap\" id=\"{contact_id}\">","<span>{contact_tid}</span></div>","</tpl>")});A.on("dblclick",function(I,d,R,n){var l=c.getAt(d);c.remove(l);o.add(l);o.sort("contact_tid","ASC");});var W=new Ext.DataView({style:"overflow:auto",singleSelect:true,itemSelector:"div.thumb-wrap",store:o,tpl:new Ext.XTemplate("<tpl for=\".\">","<div class=\"thumb-wrap\" id=\"{contact_id}\">","<span>{contact_tid}</span></div>","</tpl>")});W.on("dblclick",function(I,d,R,n){var l=o.getAt(d);o.remove(l);c.add(l);c.sort("contact_tid","ASC");});var E=new Ext.Panel({id:"list_source",title:"available lists",region:"center",margins:"5 5 5 0",layout:"fit",items:A});var X=new Ext.Panel({id:"list_selected",title:"chosen lists",region:"center",margins:"5 5 5 0",layout:"fit",items:W});var T=new Ext.FormPanel({url:"index.php",baseParams:{method:"Addressbook.saveContact"},labelAlign:"top",bodyStyle:"padding:5px",anchor:"100%",deferredRender:false,region:"center",id:"contactDialog",tbar:O,deferredRender:false,items:[{layout:"column",border:false,anchor:"100%",items:[{columnWidth:0.4,layout:"form",border:false,items:[{xtype:"textfield",fieldLabel:"First Name",name:"n_given",anchor:"95%"},{xtype:"textfield",fieldLabel:"Middle Name",name:"n_middle",anchor:"95%"},{xtype:"textfield",fieldLabel:"Last Name",name:"n_family",allowBlank:false,anchor:"95%"}]},{columnWidth:0.2,layout:"form",border:false,items:[{xtype:"textfield",fieldLabel:"Prefix",name:"n_prefix",anchor:"95%"},{xtype:"textfield",fieldLabel:"Suffix",name:"n_suffix",anchor:"95%"},i]},{columnWidth:0.4,layout:"form",border:false,items:[{xtype:"textarea",name:"contact_note",fieldLabel:"Notes",grow:false,preventScrollbars:false,anchor:"95% 85%"}]}]},{xtype:"tabpanel",plain:true,activeTab:0,anchor:"100% 70%",defaults:{bodyStyle:"padding:10px"},items:[{title:"Business information",layout:"column",border:false,items:[{columnWidth:0.333,layout:"form",border:false,items:[{xtype:"textfield",fieldLabel:"Company",name:"org_name",anchor:"95%"},{xtype:"textfield",fieldLabel:"Street",name:"adr_one_street",anchor:"95%"},{xtype:"textfield",fieldLabel:"Street 2",name:"adr_one_street2",anchor:"95%"},{xtype:"textfield",fieldLabel:"Postalcode",name:"adr_one_postalcode",anchor:"95%"},{xtype:"textfield",fieldLabel:"City",name:"adr_one_locality",anchor:"95%"},{xtype:"textfield",fieldLabel:"Region",name:"adr_one_region",anchor:"95%"},new Ext.form.ComboBox({fieldLabel:"Country",name:"adr_one_countryname",hiddenName:"adr_one_countryname",store:b,displayField:"translatedName",valueField:"shortName",typeAhead:true,mode:"remote",triggerAction:"all",emptyText:"Select a state...",selectOnFocus:true,anchor:"95%"})]},{columnWidth:0.333,layout:"form",border:false,items:[{xtype:"textfield",fieldLabel:"Phone",name:"tel_work",anchor:"95%"},{xtype:"textfield",fieldLabel:"Cellphone",name:"tel_cell",anchor:"95%"},{xtype:"textfield",fieldLabel:"Fax",name:"tel_fax",anchor:"95%"},{xtype:"textfield",fieldLabel:"Car phone",name:"tel_car",anchor:"95%"},{xtype:"textfield",fieldLabel:"Pager",name:"tel_pager",anchor:"95%"},{xtype:"textfield",fieldLabel:"Email",name:"contact_email",vtype:"email",anchor:"95%"},{xtype:"textfield",fieldLabel:"URL",name:"contact_url",vtype:"url",anchor:"95%"},]},{columnWidth:0.333,layout:"form",border:false,items:[{xtype:"textfield",fieldLabel:"Unit",name:"org_unit",anchor:"95%"},{xtype:"textfield",fieldLabel:"Role",name:"contact_role",anchor:"95%"},{xtype:"textfield",fieldLabel:"Title",name:"contact_title",anchor:"95%"},{xtype:"textfield",fieldLabel:"Room",name:"contact_room",anchor:"95%"},{xtype:"textfield",fieldLabel:"Name Assistent",name:"contact_assistent",anchor:"95%"},{xtype:"textfield",fieldLabel:"Phone Assistent",name:"tel_assistent",anchor:"95%"},]}]},{title:"Private information",layout:"column",border:false,items:[{columnWidth:0.333,layout:"form",border:false,items:[{xtype:"textfield",fieldLabel:"Street",name:"adr_two_street",anchor:"95%"},{xtype:"textfield",fieldLabel:"Street2",name:"adr_two_street2",anchor:"95%"},{xtype:"textfield",fieldLabel:"Postalcode",name:"adr_two_postalcode",anchor:"95%"},{xtype:"textfield",fieldLabel:"City",name:"adr_two_locality",anchor:"95%"},{xtype:"textfield",fieldLabel:"Region",name:"adr_two_region",anchor:"95%"},new Ext.form.ComboBox({fieldLabel:"Country",name:"adr_two_countryname",hiddenName:"adr_two_countryname",store:b,displayField:"translatedName",valueField:"shortName",typeAhead:true,mode:"remote",triggerAction:"all",emptyText:"Select a state...",selectOnFocus:true,anchor:"95%"})]},{columnWidth:0.333,layout:"form",border:false,items:[new Ext.form.DateField({fieldLabel:"Birthday",name:"contact_bday",format:formData.config.dateFormat,altFormats:"Y-m-d",anchor:"95%"}),{xtype:"textfield",fieldLabel:"Phone",name:"tel_home",anchor:"95%"},{xtype:"textfield",fieldLabel:"Cellphone",name:"tel_cell_private",anchor:"95%"},{xtype:"textfield",fieldLabel:"Fax",name:"tel_fax_home",anchor:"95%"},{xtype:"textfield",fieldLabel:"Email",name:"contact_email_home",vtype:"email",anchor:"95%"},{xtype:"textfield",fieldLabel:"URL",name:"contact_url_home",vtype:"url",anchor:"95%"}]},{columnWidth:0.333,layout:"form",border:false,items:[new Ext.form.FieldSet({id:"photo",legend:"Photo"})]}]},{title:"Lists",layout:"column",border:false,items:[{columnWidth:0.5,layout:"form",border:false,items:[new Ext.Panel({layout:"fit",id:"source",width:250,height:350,items:[E]})]},{columnWidth:0.5,layout:"form",border:false,items:[new Ext.Panel({layout:"fit",id:"destination",width:250,height:350,items:[X]})]}]},{title:"Categories",layout:"column",border:false,items:[{}]}]}]});var N=new Ext.Viewport({layout:"border",items:T});var a=new Ext.data.SimpleStore({fields:["id","addressbooks"],data:formData.config.addressbooks});return ;};var j=function(k,T){var c=Ext.getCmp("contactDialog").getForm();for(var E in T){var o=c.findField(E);if(o){o.setValue(T[E]);}}};var S=function(o,k){Ext.MessageBox.alert("Export","Not yet implemented.");};var M=function(k,c){var o=Ext.getCmp("addressbook_");o.setValue(c);};var Q=function(){var E=Ext.Element.get("container");var N=E.createChild({tag:"div",id:"iWindowTag"});var X=E.createChild({tag:"div",id:"iWindowContTag"});var k=new Ext.data.SimpleStore({fields:["category_id","category_realname"],data:[["1","erste Kategorie"],["2","zweite Kategorie"],["3","dritte Kategorie"],["4","vierte Kategorie"],["5","fuenfte Kategorie"],["6","sechste Kategorie"],["7","siebte Kategorie"],["8","achte Kategorie"]]});k.load();ds_checked=new Ext.data.SimpleStore({fields:["category_id","category_realname"],data:[["2","zweite Kategorie"],["5","fuenfte Kategorie"],["6","sechste Kategorie"],["8","achte Kategorie"]]});ds_checked.load();var W=new Ext.form.Form({labelWidth:75,url:"index.php?method=Addressbook.saveAdditionalData",reader:new Ext.data.JsonReader({root:"results"},[{name:"category_id"},{name:"category_realname"},])});var o=1;var A=new Array();ds_checked.each(function(i){A[i.data.category_id]=i.data.category_realname;});k.each(function(i){if((o%12)==1){W.column({width:"33%",labelWidth:50,labelSeparator:""});}if(A[i.data.category_id]){W.add(new Ext.form.Checkbox({boxLabel:i.data.category_realname,name:i.data.category_realname,checked:true}));}else{W.add(new Ext.form.Checkbox({boxLabel:i.data.category_realname,name:i.data.category_realname}));}if((o%12)==0){W.end();}o=o+1;});W.render("iWindowContTag");if(!T){var T=new Ext.LayoutDialog("iWindowTag",{modal:true,width:700,height:400,shadow:true,minWidth:700,minHeight:400,autoTabs:true,proxyDrag:true,center:{autoScroll:true,tabPosition:"top",closeOnTab:true,alwaysShowTabs:true}});T.addKeyListener(27,this.hide);T.addButton("save",function(){Ext.MessageBox.alert("Todo","Not yet implemented!");T.hide;},T);T.addButton("cancel",function(){Ext.MessageBox.alert("Todo","Not yet implemented!");T.hide;},T);var c=T.getLayout();c.beginUpdate();c.add("center",new Ext.ContentPanel("iWindowContTag",{autoCreate:true,title:"Category"}));c.endUpdate();}T.show();};var Z=function(){var E=Ext.Element.get("container");var N=E.createChild({tag:"div",id:"iWindowTag"});var X=E.createChild({tag:"div",id:"iWindowContTag"});var k=new Ext.data.SimpleStore({fields:["list_id","list_realname"],data:[["1","Liste A"],["2","Liste B"],["3","Liste C"],["4","Liste D"],["5","Liste E"],["6","Liste F"],["7","Liste G"],["8","Liste H"]]});k.load();ds_checked=new Ext.data.SimpleStore({fields:["list_id","list_realname"],data:[["2","Liste B"],["5","Liste E"],["6","Liste F"],["8","Liste H"]]});ds_checked.load();var W=new Ext.form.Form({labelWidth:75,url:"index.php?method=Addressbook.saveAdditionalData",reader:new Ext.data.JsonReader({root:"results"},[{name:"list_id"},{name:"list_realname"},])});var o=1;var A=new Array();ds_checked.each(function(i){A[i.data.list_id]=i.data.list_realname;});k.each(function(i){if((o%12)==1){W.column({width:"33%",labelWidth:50,labelSeparator:""});}if(A[i.data.list_id]){W.add(new Ext.form.Checkbox({boxLabel:i.data.list_realname,name:i.data.list_realname,checked:true}));}else{W.add(new Ext.form.Checkbox({boxLabel:i.data.list_realname,name:i.data.list_realname}));}if((o%12)==0){W.end();}o=o+1;});W.render("iWindowContTag");if(!T){var T=new Ext.LayoutDialog("iWindowTag",{modal:true,width:700,height:400,shadow:true,minWidth:700,minHeight:400,autoTabs:true,proxyDrag:true,center:{autoScroll:true,tabPosition:"top",closeOnTab:true,alwaysShowTabs:true}});T.addKeyListener(27,this.hide);T.addButton("save",function(){Ext.MessageBox.alert("Todo","Not yet implemented!");},T);T.addButton("cancel",function(){window.location.reload();T.hide;},T);var c=T.getLayout();c.beginUpdate();c.add("center",new Ext.ContentPanel("iWindowContTag",{autoCreate:true,title:"Lists"}));c.endUpdate();}T.show();};return {display:function(){var k=z();if(formData.values){j(k,formData.values);}}};}();Egw.Addressbook.ListEditDialog=function(){var r;var z=new Ext.Action({text:"save and close",iconCls:"action_saveAndClose"});var S=new Ext.Action({text:"apply changes",iconCls:"action_applyChanges"});var K=new Ext.Action({text:"delete contact",iconCls:"action_delete"});var p=function(){Ext.QuickTips.init();Ext.form.Field.prototype.msgTarget="side";var A=true;if(formData.values){A=false;}var N=new Ext.Toolbar({region:"south",id:"applicationToolbar",split:false,height:26,items:[z,S,K]});var X=new Ext.data.SimpleStore({fields:["id","addressbooks"],data:formData.config.addressbooks});var k=new Ext.form.TriggerField({fieldLabel:"Addressbook",name:"list_owner",anchor:"95%",readOnly:true});k.onTriggerClick=function(){Egw.Addressbook.displayAddressbookSelectDialog(Q);};var c=new Ext.FormPanel({url:"index.php",baseParams:{method:"Addressbook.saveList"},labelAlign:"top",bodyStyle:"padding:5px",anchor:"100%",region:"center",id:"listDialog",tbar:N,items:[{layout:"form",title:"list information",border:false,anchor:"100%",items:[k,{xtype:"textfield",fieldLabel:"List Name",name:"list_name",anchor:"95%"},{xtype:"textarea",fieldLabel:"List Description",name:"list_description",grow:false,anchor:"95%"}]}]});c.on("beforeaction",function(O,a){O.baseParams._listOwner=O.getValues().list_owner;O.baseParams._listmembers=m(q);if(formData.values&&formData.values.list_id){O.baseParams._listId=formData.values.list_id;}else{O.baseParams._listId="";}});if(formData.values){var v=formData.values.list_owner;var W=formData.values.list_id;}else{var v=-1;var W=-1;}searchDS=new Ext.data.JsonStore({url:"index.php",baseParams:{method:"Addressbook.getOverview",owner:v,options:"{\"displayContacts\":true,\"displayLists\":false}",},root:"results",totalProperty:"totalcount",id:"contact_id",fields:[{name:"contact_id"},{name:"n_family"},{name:"n_given"},{name:"contact_email"}],remoteSort:true,success:function(a,O){},failure:function(a,O){}});searchDS.setDefaultSort("n_family","asc");var o=new Ext.Template("<div class=\"search-item\">","{n_family}, {n_given} {contact_email}","</div>");var L=new Ext.form.ComboBox({title:"select new list members",store:searchDS,displayField:"n_family",typeAhead:false,loadingText:"Searching...",width:415,pageSize:10,hideTrigger:true,tpl:o,onSelect:function(O){var a=new H({contact_id:O.data.contact_id,n_family:O.data.n_family,contact_email:O.data.contact_email});q.add(a);q.sort("n_family");L.reset();L.collapse();}});L.on("specialkey",function(I,b){if(searchDS.getCount()==0){var n=/^[a-z0-9_-]+(\.[a-z0-9_-]+)*@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,4}|museum)$/;var O=n.exec(L.getValue());if(O&&(b.getKey()==b.ENTER||b.getKey()==e.RETURN)){var l=L.getValue();var a=l.indexOf("@");if(a!=-1){var V=Ext.util.Format.capitalize(l.substr(0,a));}else{var V=l;}var f=new H({contact_id:"-1",n_family:V,contact_email:l});q.add(f);q.sort("n_family");L.reset();}}});c.add(L);var T=new Ext.Viewport({layout:"border",items:c});var H=Ext.data.Record.create([{name:"contact_id",type:"int"},{name:"n_family",type:"string"},{name:"contact_email",type:"string"}]);if(formData.values){var j=formData.values.list_members;}var q=new Ext.data.SimpleStore({fields:["contact_id","n_family","contact_email"],data:j});q.sort("n_family","ASC");var E=new Ext.grid.ColumnModel([{resizable:true,id:"n_family",header:"Family name",dataIndex:"n_family"},{resizable:true,id:"contact_email",header:"eMail address",dataIndex:"contact_email"}]);E.defaultSortable=true;var i=new Ext.menu.Menu({id:"ctxListMenu",items:[{id:"delete",text:"delete entry",icon:"images/oxygen/16x16/actions/edit-delete.png",handler:u}]});var M=new Ext.grid.GridPanel({store:q,columns:E,sm:new Ext.grid.RowSelectionModel({multiSelect:true}),monitorWindowResize:false,trackMouseOver:true,autoExpandColumn:"contact_email"});M.on("rowcontextmenu",function(b,f,O){O.stopEvent();var a=b.getDataSource().getAt(f);if(a.data.contact_tid=="l"){i.showAt(O.getXY());}else{i.showAt(O.getXY());}});T.add(M);return ;};var Q=function(q,v){r.setValues([{id:"list_owner",value:v}]);};var u=function(H,q){var j=Array();var M=listGrid.getSelectionModel().getSelections();for(var v=0;v<M.length;++v){ds_listMembers.remove(M[v]);}};var Z=function(q,v){q.findField("list_name").setValue(v["list_name"]);q.findField("list_description").setValue(v["list_description"]);q.findField("list_owner").setValue(v["list_owner"]);};var m=function(v){var q=new Array();v.each(function(j){q.push(j.data);},this);return Ext.util.JSON.encode(q);};return {display:function(){var q=p();if(formData.values){}}};}();