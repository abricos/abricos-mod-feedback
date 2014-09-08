/*!
 * Copyright 2014 Alexander Kuzmin <roosit@abricos.org>
 * Licensed under the MIT license
 */

var Component = new Brick.Component();
Component.requires = {
    yui: ['base'],
    mod: [
        {name: 'sys', files: ['application.js', 'widget.js', 'form.js']},
        {name: 'widget', files: ['notice.js']},
        {name: '{C#MODNAME}', files: ['roles.js', 'model.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,

        WAITING = 'waiting',
        BOUNDING_BOX = 'boundingBox',

        COMPONENT = this,

        SYS = Brick.mod.sys;

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
    NS.AppWidget = Y.Base.create('appWidget', Y.Widget, [
        SYS.Language,
        SYS.Template,
        SYS.WidgetClick,
        SYS.WidgetWaiting
    ], {
        initializer: function(){
            this._appWidgetArguments = Y.Array(arguments);

            Y.after(this._syncUIAppWidget, this, 'syncUI');
        },
        _syncUIAppWidget: function(){
            if (!this.get('useExistingWidget')){
                var args = this._appWidgetArguments,
                    tData = {};

                if (Y.Lang.isFunction(this.buildTData)){
                    tData = this.buildTData.apply(this, args);
                }

                var bBox = this.get(BOUNDING_BOX),
                    defTName = this.template.cfg.defTName;

                bBox.setHTML(this.template.replace(defTName, tData));
            }
            this.set(WAITING, true);

            var instance = this;
            NS.initApp({
                initCallback: function(err, appInstance){
                    instance._initAppWidget(err, appInstance);
                }
            });
        },
        _initAppWidget: function(err, appInstance){
            this.set('appInstance', appInstance);
            this.set(WAITING, false);
            var args = this._appWidgetArguments
            this.onInitAppWidget.apply(this, [err, appInstance, {
                arguments: args
            }]);
        },
        onInitAppWidget: function(){
        }
    }, {
        ATTRS: {
            render: {
                value: true
            },
            appInstance: {
                values: null
            },
            useExistingWidget: {
                value: false
            }
        }
    });

    var AppBase = function(){
    };
    AppBase.ATTRS = {
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
        },
        initCallback: {
            value: function(){
            }
        }
    };
    AppBase.prototype = {
        initializer: function(){
            this.cacheClear();
            this.get('initCallback')(null, this);
        },
        cacheClear: function(){
            this._cacheFeedbackList = null;
        },
        onAJAXError: function(err){
            Brick.mod.widget.notice.show(err.msg);
        },
        _treatAJAXResult: function(data){
            data = data || {};
            var ret = {};

            if (data.config){
                ret.config = new NS.Config(data.config);
            }

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

            return ret;
        },
        _defaultAJAXCallback: function(err, res, details){
            var tRes = this._treatAJAXResult(res.data);

            details.callback.apply(details.context, [err, tRes]);
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
                callback.apply(context, [null, this._cacheFeedbackList]);
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
        configLoad: function(callback, context){
            this.ajax({'do': 'config'}, this._defaultAJAXCallback, {
                arguments: {callback: callback, context: context}
            });
        }
    };
    NS.AppBase = AppBase;

    var App = Y.Base.create('feedbackApp', Y.Base, [
        SYS.AJAX,
        SYS.Language,
        NS.AppBase
    ], {
        initializer: function(){
            NS.appInstance = this;
        }
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            initCallback: {
                value: null
            },
            moduleName: {
                value: '{C#MODNAME}'
            }
        }
    });
    NS.App = App;

    NS.appInstance = null;
    NS.initApp = function(options){
        options = Y.merge({
            initCallback: function(){
            }
        }, options || {});

        if (NS.appInstance){
            return options.initCallback(null, NS.appInstance);
        }
        new NS.App(options);
    };
};
