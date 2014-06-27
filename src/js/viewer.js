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

            this.get('appInstance').feedbackLoad(function(err, result){
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

            var model = new NS.Reply({
                messageid: attrs.id,
                body: ''
            });
            this.set('model', model);
        },
        onClick: function(e){
            switch (e.dataClick) {
                case 'feedback-reply':
                    this.showFeedbackReply();
                    return true;
            }
        },
        showFeedbackReply: function(){
            new NS.FeedbackReplyWidget({
                'boundingBox': this.template.gel('reply'),
                'feedback': this.get('feedback')
            });
        }
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            templateBlockName: {
                value: 'widget'
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