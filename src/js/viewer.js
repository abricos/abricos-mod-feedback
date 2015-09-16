var Component = new Brick.Component();
Component.requires = {
    yui: ['json-parse'],
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    var MessageBaseWidget = function(){
    };
    MessageBaseWidget.ATTRS = {
        message: {
            value: null,
            setter: function(val){
                if (val){
                    this.renderMessage.call(this, val);
                }
                return val;
            }
        },
        messageid: {
            value: 0
        }
    };
    MessageBaseWidget.prototype = {
        onInitAppWidget: function(err, appInstance){
            var message = this.get('message');
            if (message){
                this.renderMessage(this, message);
                return;
            }
            this.set('waiting', true);

            var messageid = this.get('messageid');

            this.get('appInstance').message(messageid, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('message', result.message);
                }
            }, this);
        },
        renderMessage: function(){
        }
    };
    NS.MessageBaseWidget = MessageBaseWidget;

    NS.MessageViewWidget = Y.Base.create('messageViewWidget', SYS.AppWidget, [
        SYS.Form,
        SYS.FormAction,
        NS.MessageBaseWidget
    ], {
        renderMessage: function(message){
            message = message || this.get('message');
            if (!message){
                return;
            }

            var tp = this.template,
                attrs = message.toJSON();

            for (var n in attrs){
                tp.setHTML('fld' + n, attrs[n])
            }

            if (!(Y.Lang.isString(attrs.email) && attrs.email.length > 0)){
                tp.hide('replywrap');
            }

            var overFieldsJSON = message.get('overfields'),
                overFields = {}, lstOverFields = "";
            try {
                overFields = Y.JSON.parse(overFieldsJSON);
            } catch (e) {
            }

            for (var n in overFields){
                lstOverFields += tp.replace('overfield', {
                    key: n,
                    value: overFields[n]
                });
            }
            if (lstOverFields !== ""){
                Y.one(tp.gel('overs')).setHTML(tp.replace('overfields', {
                    rows: lstOverFields
                }));
            }

            var isReply = false, lst = "";
            message.get('replyList').each(function(reply){
                isReply = true;
                lst += tp.replace('replyRow', reply.toJSON());
            });

            tp.setHTML('replylist', lst);

            var model = new NS.Reply({
                appInstance: this.get('appInstance'),
                id: attrs.id,
                body: ''
            });
            this.set('model', model);

            if (isReply){
                tp.hide('bshowreply');
            } else {
                this.showMessageReply();
            }
        },
        showMessageReply: function(){
            this.template.toggleView(true, 'reply', 'bshowreply');
        },
        hideMessageReply: function(){
            this.template.toggleView(false, 'reply', 'bshowreply');
        },
        onSubmitFormAction: function(){
            this.set('waiting', true);

            var model = this.get('model'),
                messageid = this.get('messageid');

            this.get('appInstance').replySend(messageid, model, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('message', result.message);
                    this.renderMessage();
                }
            }, this);
        },
        showMessageDeleteButtons: function(){
            this.template.toggleView(false, 'bshowfbdelete', 'fbdelete');
        },
        hideMessageDeleteButtons: function(){
            this.template.toggleView(true, 'bshowfbdelete', 'fbdelete');
        },
        messageRemove: function(){
            this.set('waiting', true);

            var appInstance = this.get('appInstance'),
                messageid = this.get('messageid');

            appInstance.messageRemove(messageid, function(err, result){
                this.set('waiting', false);
                if (!err){
                    var messageList = this.get('appInstance').get('messageList');
                    if (messageList){
                        messageList.removeById(result.messageRemove.messageid);
                    }
                    this.go('manager.view');
                }
            }, this);
        }
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            templateBlockName: {
                value: 'widget,replyRow,overfields,overfield'
            }
        },
        CLICKS: {
            'message-reply': 'showMessageReply',
            'reply-cancel': 'hideMessageReply',
            'message-showdelete': 'showMessageDeleteButtons',
            'message-delete-cancel': 'hideMessageDeleteButtons',
            'message-delete': 'messageRemove'
        }
    });

    NS.MessageViewWidget.parseURLParam = function(args){
        return {
            messageid: args[0] | 0
        };
    };

};