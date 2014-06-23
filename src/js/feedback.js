/*!
 * Copyright 2008-2014 Alexander Kuzmin <roosit@abricos.org>
 * Licensed under the MIT license
 */

var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['panel.js', 'form.js']},
        {name: 'widget', files: ['notice.js']},
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,

        COMPONENT = this,

        SYS = Brick.mod.sys;

    var FeedbackForm = function(){
    };
    FeedbackForm.NAME = 'feedbackForm';
    FeedbackForm.ATTRS = {
        model: {
            value: new NS.Feedback()
        }
    };
    FeedbackForm.prototype = {
        initializer: function(){
            var instance = this;
            NS.initApp({
                initCallback: function(){
                    instance._onLoadManager();
                }
            });
        },
        _onLoadManager: function(){
            this.after('submitForm', this._submitFeedbackForm);
        },
        _submitFeedbackForm: function(e){
            this.set('waiting', true);
            var model = this.get('model'),
                instance = this;

            NS.appInstance.feedbackSend(model, function(err, result){
                instance.set('waiting', false);
            }, this);

            e.halt();
        }
    };
    NS.FeedbackForm = FeedbackForm;

    NS.FeedbackFormWidget = Y.Base.create('loginFormWidget', Y.Widget, [
        SYS.Template,
        SYS.Language,
        SYS.Form,
        SYS.FormAction,
        SYS.WidgetWaiting,
        NS.FeedbackForm
    ], {
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            templateBlockName: {
                value: 'error'
            },
            render: {
                value: true
            }
        }
    });

};