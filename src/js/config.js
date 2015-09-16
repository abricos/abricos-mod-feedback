var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.ConfigWidget = Y.Base.create('configWidget', SYS.AppWidget, [
        SYS.Form,
        SYS.FormAction
    ], {
        onInitAppWidget: function(err, appInstance, options){
            this.reloadConfig();
        },
        reloadConfig: function(){
            this.set('waiting', true);

            this.get('appInstance').config(function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('config', result.config);
                }
                this.renderConfig();
            }, this);
        },
        renderConfig: function(){
            var config = this.get('config');
            this.set('model', config);
        },
        onSubmitFormAction: function(){
            this.set('waiting', true);

            var model = this.get('model'),
                instance = this;

            this.get('appInstance').configSave(model, function(err, result){
                instance.set('waiting', false);
                if (!err){
                    // instance.fire('editorSaved');
                }
            });
        },
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            templateBlockName: {
                value: 'widget'
            },
            config: {
                value: null
            }
        }
    });

};