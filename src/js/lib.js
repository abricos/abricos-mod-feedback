var Component = new Brick.Component();
Component.requires = {
    yui: ['base'],
    mod: [
        {name: 'sys', files: ['application.js', 'form.js']},
        {name: '{C#MODNAME}', files: ['model.js']}
    ]
};
Component.entryPoint = function(NS){

    NS.roles = new Brick.AppRoles('{C#MODNAME}', {
        isAdmin: 50,
        isWrite: 30,
        isView: 10
    });

    var COMPONENT = this,
        SYS = Brick.mod.sys;

    SYS.Application.build(COMPONENT, {}, {
        initializer: function(){
            NS.roles.load(function(){
                this.initCallbackFire();
            }, this);
        },
    }, [], {
        REQS: {
            feedbackSend: {
                args: ['feedback']
            },
            messageList: {
                attribute: true,
                type: 'modelList:MessageList'
            },
            message: {
                args: ['messageid'],
                type: 'model:Message',
                attribute: false
            },
            messageRemove: {
                args: ['messageid']
            },
            config: {
                attribute: true,
                type: 'model:Config'
            },
            configSave: {
                args: ['config']
            },
            replySend: {
                args: ['messageid', 'reply']
            },
        },
        ATTRS: {
            isLoadAppStructure: {value: true},
            Config: {value: NS.Config},
            Feedback: {value: NS.Feedback},
            Message: {value: NS.Message},
            MessageList: {value: NS.MessageList},
            Reply: {value: NS.Reply},
            ReplyList: {value: NS.ReplyList},
        },
        URLS: {
            ws: "#app={C#MODNAMEURI}/wspace/ws/",
            manager: {
                view: function(){
                    return this.getURL('ws') + 'manager/ManagerWidget/'
                }
            },
            config: {
                view: function(){
                    return this.getURL('ws') + 'config/ConfigWidget/'
                }
            },
            message: {
                view: function(feedbackId){
                    return this.getURL('ws') + 'viewer/MessageViewWidget/' + feedbackId + '/';
                }
            }
        }
    });
};
