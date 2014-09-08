/*
 @package Abricos
 @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this;

    NS.ConfigWidget = Y.Base.create('configWidget', NS.AppWidget, [
    ], {
        onInitAppWidget: function(err, appInstance, options){
            this.reloadConfig();

        },
        reloadConfig: function(){
            this.set('waiting', true);

            this.get('appInstance').configLoad(function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('config', result.config);
                }
                this.renderConfig();
            }, this);
        },
        renderConfig: function(){
            var config = this.get('config');
            console.log(config);
            if (!feedbackList){
                return;
            }
            /*
            var tp = this.template, lst = "";

            feedbackList.each(function(feedback){
                var attrs = feedback.toJSON();
                lst += tp.replace('row', [
                    attrs
                ]);
            });
            tp.gel('list').innerHTML = tp.replace('list', {
                'rows': lst
            });
            /**/
        },
        onClick: function(e){
            /*
            var feedbackId = e.target.getData('id') | 0;
            if (feedbackId === 0){
                return;
            }

            switch (e.dataClick) {
                case 'feedback-open':
                    this.showFeedback(feedbackId);
                    return true;
            }
            /**/
        }
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            templateBlockName: {
                value: 'widget'
            },
            config: {
                value: null
            }
        }
    });

};