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

    NS.ManagerWidget = Y.Base.create('managerWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance, options){
            this.reloadMessageList();
            this.get('appInstance').on('appResponses', this._onAppResponses, this);
        },
        destructor: function(){
            this.get('appInstance').detach('appResponses', this._onAppResponses, this);
        },
        _onAppResponses: function(e){
            if (e.err || !e.result.messageRemove){
                return;
            }
            this.renderMessageList();
        },
        reloadMessageList: function(){
            this.set('waiting', true);

            this.get('appInstance').messageList(function(err, result){
                this.set('waiting', false);
                this.renderMessageList();
            }, this);
        },
        renderMessageList: function(){
            var messageList = this.get('appInstance').get('messageList');
            if (!messageList){
                return;
            }

            var tp = this.template, lst = "";

            messageList.each(function(feedback){
                var attrs = feedback.toJSON();
                lst += tp.replace('row', [
                    attrs, {
                        date: Brick.dateExt.convert(attrs.dateline)
                    }
                ]);
            });
            tp.gel('list').innerHTML = tp.replace('list', {
                'rows': lst
            });
        }
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            templateBlockName: {
                value: 'widget,list,row'
            }
        },
        CLICKS: {
            'feedback-open': {
                event: function(e){
                    var feedbackId = e.target.getData('id') | 0;
                    this.go('message.view', feedbackId);
                }
            }
        }
    });

};