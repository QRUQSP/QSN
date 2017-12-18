//
// This is the main app for the qsn module
//
function qruqsp_qsn_main() {
    //
    // The panel to list the rtlpowersample
    //
    this.menu = new M.panel('RTL Heatmap', 'qruqsp_qsn_main', 'menu', 'mc', 'full', 'sectioned', 'qruqsp.qsn.main.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
        'selector':{'label':'', 'fields':{
            'start_frequency':{'label':'Start Freq', 'type':'text'},
            'end_frequency':{'label':'End Freq', 'type':'text'},
            'start_date':{'label':'Start Date', 'type':'date'},
            'start_time':{'label':'Start Time', 'type':'text'},
            'end_date':{'label':'End Date', 'type':'date'},
            'end_time':{'label':'End Time', 'type':'text'},
            }},
        '_buttons':{'label':'', 'aside':'yes', 'buttons':{
            'refresh':{'label':'Refresh', 'fn':'M.qruqsp_qsn_main.menu.loadHeatmap();'},
            }},
        'heatmap':{'label':'', 'type':'heatmap'},
    }
/*    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            M.api.getJSONBgCb('qruqsp.qsn.rtlpowersampleSearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.qruqsp_qsn_main.menu.liveSearchShow('search',null,M.gE(M.qruqsp_qsn_main.menu.panelUID + '_' + s), rsp.rtlpowersamples);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        return d.name;
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'M.qruqsp_qsn_main.rtlpowersample.open(\'M.qruqsp_qsn_main.menu.open();\',\'' + d.id + '\');';
    } */
    this.menu.fieldValue = function(s, i, d) {
        if( this.data.heatmap != null ) {
            return this.data.heatmap[i];
        }
        return '';
/*        var dt = new Date();
        switch (i) {
            case 'start_frequency': return '144360';
            case 'end_frequency': return '144420';
            case 'start_date': return dt.getFullYear() + '-' + (dt.getMonth()+1) + '-' + dt.getDate();;
            case 'start_time': return '1600';
            case 'end_date': return dt.getFullYear() + '-' + (dt.getMonth()+1) + '-' + dt.getDate();;
            case 'end_time': return '1610';
        }
        return null; */
    }
    this.menu.heatmapData = function(s) {
        if( this.data[s] != null ) {
            return this.data[s];
        }
        return {'data':[]};
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'selector' ) {
            switch(j) {
                case 0: return 'Start';
                case 1: return 'Start';
            }
        }
        if( s == 'rtlpowersamples' ) {
            switch(j) {
                case 0: return d.name;
            }
        }
    }
/*    this.menu.rowFn = function(s, i, d) {
        if( s == 'rtlpowersamples' ) {
            return 'M.qruqsp_qsn_main.rtlpowersample.open(\'M.qruqsp_qsn_main.menu.open();\',\'' + d.id + '\',M.qruqsp_qsn_main.rtlpowersample.nplist);';
        }
    } */
    this.menu.open = function(cb) {
        M.api.getJSONCb('qruqsp.qsn.rtlpowerHeatmap', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qsn_main.menu;
            p.data = rsp;
            p.refresh();
            p.show();
        }); 
    } 
    this.menu.loadHeatmap = function() {
        var args = {
            'tnid':M.curTenantID,
            'start_frequency':this.formValue('start_frequency'),
            'end_frequency':this.formValue('end_frequency'),
            'start_date':this.formValue('start_date'),
            'start_time':this.formValue('start_time'),
            'end_date':this.formValue('end_date'),
            'end_time':this.formValue('end_time'),
            };
        M.api.getJSONCb('qruqsp.qsn.rtlpowerHeatmap', args, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qsn_main.menu;
            p.data = rsp;
            p.refresh();
            p.show();
        }); 
    }
    this.menu.addClose('Back');

    //
    // The panel to edit RTL Power Sample
    //
    this.rtlpowersample = new M.panel('RTL Power Sample', 'qruqsp_qsn_main', 'rtlpowersample', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.qsn.main.rtlpowersample');
    this.rtlpowersample.data = null;
    this.rtlpowersample.rtlpowersample_id = 0;
    this.rtlpowersample.nplist = [];
    this.rtlpowersample.sections = {
        'general':{'label':'', 'fields':{
            'sample_date':{'label':'Sample UTC Date', 'type':'date'},
            'gain':{'label':'Gain', 'type':'text'},
            'frequency_start':{'label':'Start Frequency', 'type':'text'},
            'frequency_step':{'label':'Step Frequency', 'type':'text'},
            'frequency_end':{'label':'End Frequency', 'type':'text'},
            'dbm_lowest':{'label':'Lowest DB', 'type':'text'},
            'dbm_highest':{'label':'Highest DB', 'type':'text'},
            'dbm_qty':{'label':'Number of Data Points', 'type':'text'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.qruqsp_qsn_main.rtlpowersample.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.qruqsp_qsn_main.rtlpowersample.rtlpowersample_id > 0 ? 'yes' : 'no'; },
                'fn':'M.qruqsp_qsn_main.rtlpowersample.remove();'},
            }},
        };
    this.rtlpowersample.fieldValue = function(s, i, d) { return this.data[i]; }
    this.rtlpowersample.fieldHistoryArgs = function(s, i) {
        return {'method':'qruqsp.qsn.rtlpowersampleHistory', 'args':{'tnid':M.curTenantID, 'rtlpowersample_id':this.rtlpowersample_id, 'field':i}};
    }
    this.rtlpowersample.open = function(cb, rid, list) {
        if( rid != null ) { this.rtlpowersample_id = rid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('qruqsp.qsn.rtlpowersampleGet', {'tnid':M.curTenantID, 'rtlpowersample_id':this.rtlpowersample_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qsn_main.rtlpowersample;
            p.data = rsp.rtlpowersample;
            p.refresh();
            p.show(cb);
        });
    }
    this.rtlpowersample.save = function(cb) {
        if( cb == null ) { cb = 'M.qruqsp_qsn_main.rtlpowersample.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.rtlpowersample_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('qruqsp.qsn.rtlpowersampleUpdate', {'tnid':M.curTenantID, 'rtlpowersample_id':this.rtlpowersample_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('qruqsp.qsn.rtlpowersampleAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qsn_main.rtlpowersample.rtlpowersample_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.rtlpowersample.remove = function() {
        if( confirm('Are you sure you want to remove rtlpowersample?') ) {
            M.api.getJSONCb('qruqsp.qsn.rtlpowersampleDelete', {'tnid':M.curTenantID, 'rtlpowersample_id':this.rtlpowersample_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qsn_main.rtlpowersample.close();
            });
        }
    }
    this.rtlpowersample.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.rtlpowersample_id) < (this.nplist.length - 1) ) {
            return 'M.qruqsp_qsn_main.rtlpowersample.save(\'M.qruqsp_qsn_main.rtlpowersample.open(null,' + this.nplist[this.nplist.indexOf('' + this.rtlpowersample_id) + 1] + ');\');';
        }
        return null;
    }
    this.rtlpowersample.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.rtlpowersample_id) > 0 ) {
            return 'M.qruqsp_qsn_main.rtlpowersample.save(\'M.qruqsp_qsn_main.rtlpowersample.open(null,' + this.nplist[this.nplist.indexOf('' + this.rtlpowersample_id) - 1] + ');\');';
        }
        return null;
    }
    this.rtlpowersample.addButton('save', 'Save', 'M.qruqsp_qsn_main.rtlpowersample.save();');
    this.rtlpowersample.addClose('Cancel');
    this.rtlpowersample.addButton('next', 'Next');
    this.rtlpowersample.addLeftButton('prev', 'Prev');

    //
    // Start the app
    // cb - The callback to run when the user leaves the main panel in the app.
    // ap - The application prefix.
    // ag - The app arguments.
    //
    this.start = function(cb, ap, ag) {
        args = {};
        if( ag != null ) {
            args = eval(ag);
        }
        
        //
        // Create the app container
        //
        var ac = M.createContainer(ap, 'qruqsp_qsn_main', 'yes');
        if( ac == null ) {
            alert('App Error');
            return false;
        }
        
        this.menu.open(cb);
    }
}
