
var AdriverKeyWords = new Class({

    current_profession_dbid: null,
    current_profession_column: 0,

    initialize: function()
    {
        var _this = this;
        
        var fprofession = ComboboxManager.getInput("fprofession");
        fprofession.b_input.addEvent('bcombochange',function(){
            _this.renderKeyword('f');
        });  
        
        var uprofession = ComboboxManager.getInput("uprofession");
        uprofession.b_input.addEvent('bcombochange',function(){
            _this.renderKeyword('u');
        });
        
        var tprofession = ComboboxManager.getInput("tprofession");
        tprofession.b_input.addEvent('bcombochange',function(){
            _this.renderKeyword('t');
        });
        
        
        var pprofession = ComboboxManager.getInput("pprofession");
        
        pprofession.b_input.addEvent('bcombochange',function(){
            _this.renderKeyword('p');
        });        
    },

    
    renderKeyword: function(type)
    {
        var dbid = parseInt($(type + 'profession_db_id').get("value"));
        var column = parseInt($(type + 'profession_column_id').get("value"));        
        
        if (dbid > 0) {
            var kw = null;
            var prefix = column === 0?'g':'s';
            
            kw = type + prefix + dbid;
            $(type +'profession_keyword').set('html', kw);
        }
    }        
    
});

window.addEvent('domready', function() {
    window.adriverkeywords = new AdriverKeyWords();
});