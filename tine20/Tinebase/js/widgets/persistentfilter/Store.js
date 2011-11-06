/*
 * Tine 2.0
 * 
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @copyright   Copyright (c) 2010-2011 Metaways Infosystems GmbH (http://www.metaways.de)
 */
Ext.ns('Tine.widgets.persistentfilter.store');

/**
 * @namespace   Tine.widgets.persistentfilter
 * @class       Tine.widgets.persistentfilter.store.PersistentFilterStore
 * @extends     Ext.data.ArrayStore
 * 
 * <p>Store for Persistent Filter Records</p>
 * 
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * 
 * @param       {Object} config
 * @constructor
 * Create a new Tine.widgets.persistentfilter.store.PersistentFilterStore
 */
Tine.widgets.persistentfilter.store.PersistentFilterStore = Ext.extend(Ext.data.ArrayStore, {

});

/**
 * @namespace   Tine.widgets.persistentfilter
 * 
 * get store of all persistent filters
 * 
 * @static
 * @singleton
 * @return {PersistentFilterStore}
 */
Tine.widgets.persistentfilter.store.getPersistentFilterStore = function() {
    if (! Tine.widgets.persistentfilter.store.persistentFilterStore) {
        
        if (window.isMainWindow) {
        	
        	var fields = Tine.widgets.persistentfilter.model.PersistentFilter.getFieldDefinitions();
        	fields.push('sorting');
        	
            // create store
            var s = Tine.widgets.persistentfilter.store.persistentFilterStore = new Tine.widgets.persistentfilter.store.PersistentFilterStore({
                fields: fields,
                sortInfo: {field: 'sorting', direction: 'ASC'}
            });
            
            // populate store
            var persistentFiltersData = Tine.Tinebase.registry.get("persistentFilters").results;
            
            // get sorting from state
            var state = Ext.state.Manager.get('widgets-persistentfilter-pickerpanel', {});
            
            Ext.each(persistentFiltersData, function(data,index) {
            	
            	if(state[data.id]) data.sorting = state[data.id];
            	else data.sorting = 10000;
            	
                var r = new Tine.widgets.persistentfilter.model.PersistentFilter(data);
                s.addSorted(r);
            }, this);
        } else {
            // TODO test this in IE!
            var mainWindow = Ext.ux.PopupWindowMgr.getMainWindow();
            Tine.widgets.persistentfilter.store.persistentFilterStore = mainWindow.Tine.widgets.persistentfilter.store.getPersistentFilterStore();
        }
    }
    
    return Tine.widgets.persistentfilter.store.persistentFilterStore;
}
