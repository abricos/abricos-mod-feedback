var Component = new Brick.Component();
Component.requires = {
    yui: ['aui-form-validator'],
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
        Y.FormValidator,
        SYS.Form,
        SYS.FormAction
    ], {
        initializer: function(){
            this.publish('feedbackSended', {
                defaultFn: this._defFeedbackSended
            });
        },
        onSubmitFormAction: function(){
            if (this.hasErrors()){
                var errorText = this.get('errorText');
                if (!errorText || errorText.length === 0){
                    errorText = this.language.get('form.error.all');
                }
                Brick.mod.widget.notice.show(errorText);
                return;
            }

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
            errorText: {
                value: ''
            },
            validateOnInput: {
                value: false
            },
            showMessages: {
                value: false
            },
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