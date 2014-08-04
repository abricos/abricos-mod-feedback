/*!
 * Copyright 2008-2014 Alexander Kuzmin <roosit@abricos.org>
 * Licensed under the MIT license
 */

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

    var FeedbackBaseWidget = function(){
    };
    FeedbackBaseWidget.ATTRS = {
        feedback: {
            value: null,
            setter: function(val){
                if (val){
                    this.renderFeedback.call(this, val);
                }
                return val;
            }
        },
        feedbackId: {
            value: 0
        }
    };
    FeedbackBaseWidget.prototype = {
        onInitAppWidget: function(err, appInstance){
            var feedback = this.get('feedback');
            if (feedback){
                this.renderFeedback.call(this, feedback);
                return;
            }
            this.set('waiting', true);

            var feedbackId = this.get('feedbackId');

            this.get('appInstance').feedbackLoad(feedbackId, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('feedback', result.feedback);
                }
            }, this);
        },
        renderFeedback: function(){
        }
    };
    NS.FeedbackBaseWidget = FeedbackBaseWidget;

    NS.FeedbackViewWidget = Y.Base.create('feedbackViewWidget', NS.AppWidget, [
        SYS.Form,
        SYS.FormAction,
        NS.FeedbackBaseWidget
    ], {
        renderFeedback: function(feedback){
            feedback = feedback || this.get('feedback');
            if (!feedback){
                return;
            }

            var tp = this.template,
                attrs = feedback.toJSON();

            for (var n in attrs){
                var node = Y.one(tp.gel('fld' + n));
                if (node){
                    node.setHTML(attrs[n]);
                }
            }

            var isReply = false, lst = "";
            feedback.replyList.each(function(reply){
                isReply = true;
                lst += tp.replace('reply', reply.toJSON());
            });


            Y.one(tp.gel('replylist')).setHTML(lst);

            var model = new NS.Reply({
                id: attrs.id,
                body: ''
            });
            this.set('model', model);

            if (isReply){
                Y.one(tp.gel('bshowreply')).hide();
            } else {
                this.showFeedbackReply();
            }
        },
        onClick: function(e){
            switch (e.dataClick) {
                case 'feedback-reply':
                    this.showFeedbackReply();
                    return true;
                case 'reply-cancel':
                    this.hideFeedbackReply();
                    return true;
                case 'feedback-showdelete':
                    this.showFeedbackDeleteButtons();
                    return true;
                case 'feedback-delete-cancel':
                    this.hideFeedbackDeleteButtons();
                    return true;
                case 'feedback-delete':
                    this.feedbackDelete();
                    return true;
            }
        },
        showFeedbackReply: function(){
            var tp = this.template;
            Y.one(tp.gel('reply')).show();
            Y.one(tp.gel('bgroup')).hide();
        },
        hideFeedbackReply: function(){
            var tp = this.template;
            Y.one(tp.gel('reply')).hide();
            Y.one(tp.gel('bgroup')).show();
        },
        onSubmitFormAction: function(){
            this.set('waiting', true);

            var model = this.get('model'),
                feedbackId = this.get('feedbackId');

            this.get('appInstance').replySend(feedbackId, model, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('feedback', result.feedback);
                }
            }, this);
        },
        showFeedbackDeleteButtons: function(){
            var tp = this.template;
            Y.one(tp.gel('bshowfbdelete')).hide();
            Y.one(tp.gel('fbdelete')).show();
        },
        hideFeedbackDeleteButtons: function(){
            var tp = this.template;
            Y.one(tp.gel('bshowfbdelete')).show();
            Y.one(tp.gel('fbdelete')).hide();
        },
        feedbackDelete: function(){
            this.set('waiting', true);

            var feedbackId = this.get('feedbackId');

            this.get('appInstance').feedbackDelete(feedbackId, function(err, result){
                this.set('waiting', false);
                if (!err){
                    console.log('delete');
                }
            }, this);
        }
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            templateBlockName: {
                value: 'widget,reply'
            }
        }
    });

    NS.FeedbackViewWidget.parseURLParam = function(args){
        args = Y.merge({
            p1: 0
        }, args || {});

        return {
            feedbackId: args.p1
        };
    };

};