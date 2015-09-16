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

    NS.ManagerWidget = Y.Base.create('managerWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance, options){
            this.reloadFeedbackList();
        },
        reloadFeedbackList: function(){
            this.set('waiting', true);

            this.get('appInstance').feedbackListLoad(function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('feedbackList', result.feedbackList);
                }
                this.renderFeedbackList();
            }, this);
        },
        renderFeedbackList: function(){
            var feedbackList = this.get('feedbackList');
            if (!feedbackList){
                return;
            }

            var tp = this.template, lst = "";

            feedbackList.each(function(feedback){
                var attrs = feedback.toJSON();
                lst += tp.replace('row', [
                    attrs, {
                        date: Brick.dateExt.convert(attrs.dateline)
                    }
                ]);
            });
            tp.gel('list').innerHTML = tp.replace('list', {
                'rows': lst
            });
        },
        onClick: function(e){
            var feedbackId = e.target.getData('id') | 0;
            if (feedbackId === 0){
                return;
            }

            switch (e.dataClick) {
                case 'feedback-open':
                    this.showFeedback(feedbackId);
                    return true;
            }
        },
        showFeedback: function(feedbackId){
            Brick.Page.reload(NS.URL.feedback.view(feedbackId));
        }
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            templateBlockName: {
                value: 'widget,list,row'
            },
            feedbackList: {
                value: null
            }
        }
    });

};