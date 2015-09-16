var Component = new Brick.Component();
Component.requires = {
    yui: ['model', 'model-list'],
    mod: [
        {name: 'sys', files: ['appModel.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        SYS = Brick.mod.sys;

    NS.Feedback = Y.Base.create('feedback', Y.Model, [], {}, {
        ATTRS: {
            fio: {value: ''},
            phone: {value: ''},
            email: {value: ''},
            message: {value: ''},
            overfields: {value: ''},
            dateline: {value: 0}
        }
    });

    NS.Message = Y.Base.create('message', SYS.AppModel, [], {
        structureName: 'Message'
    });

    NS.MessageList = Y.Base.create('messageList', SYS.AppModelList, [], {
        appItem: NS.Message
    });

    NS.Reply = Y.Base.create('reply', SYS.AppModel, [], {
        structureName: 'Reply'
    });

    NS.ReplyList = Y.Base.create('replyList', SYS.AppModelList, [], {
        appItem: NS.Reply
    });

    NS.Config = Y.Base.create('config', SYS.AppModel, [], {
        structureName: 'Config'
    });
};
