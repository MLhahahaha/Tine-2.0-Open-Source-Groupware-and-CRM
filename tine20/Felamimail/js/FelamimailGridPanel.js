/**
 * Tine 2.0
 * 
 * @package     Felamimail
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id:GridPanel.js 7170 2009-03-05 10:58:55Z p.schuele@metaways.de $
 *
 */
 
Ext.namespace('Tine.Felamimail');

/**
 * Message grid panel
 */
Tine.Felamimail.GridPanel = Ext.extend(Tine.Tinebase.widgets.app.GridPanel, {
    // model generics
    recordClass: Tine.Felamimail.Model.Message,
    evalGrants: false,
    
    // grid specific
    defaultSortInfo: {field: 'sent', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'subject'
    },
    
    initComponent: function() {
        this.recordProxy = Tine.Felamimail.recordBackend;
        
        this.actionToolbarItems = this.getToolbarItems();
        this.gridConfig.columns = this.getColumns();
        this.initFilterToolbar();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);        
        
        Tine.Felamimail.GridPanel.superclass.initComponent.call(this);
        
        this.action_addInNewWindow.setDisabled(! Tine.Tinebase.common.hasRight('manage', 'Felamimail', 'records'));
        this.action_editInNewWindow.requiredGrant = 'editGrant';
        
    },
    
    /**
     * initialises filter toolbar
     */
    initFilterToolbar: function() {
        this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            filterModels: [
                {label: this.app.i18n._('Subject'),    field: 'subject',       operators: ['contains']},
                // @todo add filters
                /*
                {label: this.app.i18n._('Message'),    field: 'query',       operators: ['contains']},
                {label: this.app.i18n._('Description'),    field: 'description', operators: ['contains']},
                new Tine.Felamimail.TimeAccountStatusGridFilter({
                    field: 'status'
                }),
                */
                //new Tine.widgets.tags.TagFilter({app: this.app})
             ],
             defaultFilter: 'subject',
             filters: []
        });
    },    
    
    /**
     * returns cm
     * @private
     */
    getColumns: function(){
        return [{
            id: 'id',
            header: this.app.i18n._("Id"),
            width: 100,
            sortable: true,
            dataIndex: 'id'
        },{
            id: 'subject',
            header: this.app.i18n._("Subject"),
            width: 300,
            sortable: true,
            dataIndex: 'subject'
        },{
            id: 'from',
            header: this.app.i18n._("From"),
            width: 150,
            sortable: true,
            dataIndex: 'from'
            //renderer: this.statusRenderer.createDelegate(this)
        },{
            id: 'to',
            header: this.app.i18n._("To"),
            width: 150,
            sortable: true,
            dataIndex: 'to'
        },{
            id: 'sent',
            header: this.app.i18n._("Sent"),
            width: 150,
            sortable: true,
            dataIndex: 'sent'
        }];
    },
    
    /**
     * status column renderer
     * @param {string} value
     * @return {string}
     */
    statusRenderer: function(value) {
        return this.app.i18n._hidden(value);
    },
    
    /**
     * return additional tb items
     */
    getToolbarItems: function(){
    	/*
        this.action_showClosedToggle = new Tine.widgets.grid.FilterButton({
            text: this.app.i18n._('Show closed'),
            iconCls: 'action_showArchived',
            field: 'showClosed'
        });
        */
        
        return [
            /*
            new Ext.Toolbar.Separator(),
            this.action_showClosedToggle
            */
        ];
    }    
});
