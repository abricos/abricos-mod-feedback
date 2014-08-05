/*!
 * Module for Abricos Platform (http://abricos.org)
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

        BOUNDING_BOX = 'boundingBox',

        SYS = Brick.mod.sys;

    NS.FeedbackWidget = Y.Base.create('feedbackWidget', NS.AppWidget, [
        SYS.Form,
        SYS.FormAction
    ], {
        initializer: function(){
            this.publish('feedbackSended', {
                defaultFn: this._defFeedbackSended
            });
        },
        onSubmitFormAction: function(){
            this.set('waiting', true);

            var model = this.get('model'),
                instance = this;

            this.get('appInstance').feedbackSend(model, function(err, result){
                instance.set('waiting', false);
                if (!err){
                    instance.onSubmitCompleteFormAction(result);
                    instance.fire('feedbackSended');
                }
            });
        },
        onSubmitCompleteFormAction: function(){
            var bbox = this.get(BOUNDING_BOX);
            bbox.replaceClass('feedback-status-input', 'feedback-status-complete');
        },
        _defFeedbackSended: function(){
        }
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            useExistingWidget: {
                value: true
            },
            model: {
                value: new NS.Feedback()
            }
        }
    });
};