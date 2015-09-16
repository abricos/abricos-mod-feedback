var Component = new Brick.Component();
Component.requires = {
    yui: ['base'],
    mod: [
        {name: 'sys', files: ['application.js', 'widget.js', 'form.js']},
        {name: 'widget', files: ['notice.js']},
        {name: '{C#MODNAME}', files: ['model.js']}
    ]
};
Component.entryPoint = function(NS){

    NS.roles = new Brick.AppRoles('{C#MODNAME}', {
        isAdmin: 50,
        isWrite: 30,
        isView: 10
    });

    var Y = Brick.YUI,

        COMPONENT = this,

        SYS = Brick.mod.sys;

    /**
     * @deprecated
     */
    NS.AppWidget = SYS.AppWidget;

    NS.URL = {
        ws: "#app={C#MODNAMEURI}/wspace/ws/",
        manager: {
            view: function(){
                return NS.URL.ws + 'manager/ManagerWidget/'
            }
        },
        config: {
            view: function(){
                return NS.URL.ws + 'config/ConfigWidget/'
            }
        },
        feedback: {
            view: function(feedbackId){
                return NS.URL.ws + 'viewer/FeedbackViewWidget/' + feedbackId + '/';
            }
        }
    };

    SYS.Application.build(COMPONENT, {
        config: {
            response: function(d){
                return new NS.Config(d);
            }
        }
    }, {
        initializer: function(){
            this._cacheFeedbackList = null;
            this.initCallbackFire();
        },
        ajaxParseResponse: function(data, ret){

            if (data.feedbacks){
                var feedbackListClass = this.get('feedbackListClass');
                ret.feedbackList = new feedbackListClass({
                    items: data.feedbacks.list
                });
                this._cacheFeedbackList = ret.feedbackList;
            }
            var feedbackList = this._cacheFeedbackList;
            if (feedbackList){
                ret.feedbackList = feedbackList;
            }

            var feedback;
            if (data.feedback){

                if (feedbackList){
                    feedback = feedbackList.getById(data.feedback.id);
                }

                if (feedback){
                    feedback.setAttrs(data.feedback);
                } else {
                    var feedbackClass = this.get('feedbackClass');
                    feedback = new feedbackClass(data.feedback);
                }
                ret.feedback = feedback;

                if (data.feedback.replies){
                    var replyListClass = this.get('replyListClass');
                    feedback.replyList = new replyListClass({
                        items: data.feedback.replies.list
                    });
                }
            }
        },
        feedbackSend: function(feedback, callback, context){
            this.ajax({
                'do': 'feedbacksend',
                'savedata': feedback.toJSON()
            }, this._defaultAJAXCallback, {
                arguments: {callback: callback, context: context}
            });
        },
        feedbackListLoad: function(callback, context){
            if (this._cacheFeedbackList){
                callback.apply(context, [null, {
                    feedbackList: this._cacheFeedbackList
                }]);
                return;
            }
            this.ajax({
                'do': 'feedbacklist'
            }, this._defaultAJAXCallback, {
                arguments: {callback: callback, context: context}
            });
        },
        feedbackLoad: function(feedbackId, callback, context){
            this.ajax({
                'do': 'feedback',
                'feedbackid': feedbackId
            }, this._defaultAJAXCallback, {
                arguments: {callback: callback, context: context}
            });
        },
        feedbackDelete: function(feedbackId, callback, context){
            this.ajax({
                'do': 'feedbackdelete',
                'feedbackid': feedbackId
            }, this._defaultAJAXCallback, {
                arguments: {callback: callback, context: context}
            });
        },
        replySend: function(feedbackId, reply, callback, context){
            this.ajax({
                'do': 'replysend',
                'feedbackid': feedbackId,
                'savedata': reply.toJSON()
            }, this._defaultAJAXCallback, {
                arguments: {callback: callback, context: context}
            });
        },
        configSave: function(config, callback, context){
            this.ajax({
                'do': 'configsave',
                'savedata': config.toJSON()
            }, this._defaultAJAXCallback, {
                arguments: {callback: callback, context: context}
            });
        }
    }, [], {
        ATTRS: {
            feedbackListClass: {
                value: NS.FeedbackList
            },
            feedbackClass: {
                value: NS.Feedback
            },
            replyClass: {
                value: NS.Reply
            },
            replyListClass: {
                value: NS.ReplyList
            }
        }
    });


};
