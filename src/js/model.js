/*!
 * Copyright 2014 Alexander Kuzmin <roosit@abricos.org>
 * Licensed under the MIT license
 */

var Component = new Brick.Component();
Component.requires = {
    yui: ['model', 'model-list']
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI;

    NS.Feedback = Y.Base.create('feedback', Y.Model, [ ], {
        initializer: function(){
            this.replyList = null;
        }
    }, {
        ATTRS: {
            fio: {
                value: ''
            },
            phone: {
                value: ''
            },
            email: {
                value: ''
            },
            message: {
                value: ''
            }
        }
    });

    NS.FeedbackList = Y.Base.create('feedbackList', Y.ModelList, [], {
        model: NS.Feedback
    });

    NS.Reply = Y.Base.create('reply', Y.Model, [ ], {
    }, {
        ATTRS: {
            userid: {
                value: 0
            },
            messageid: {
                value: 0
            },
            body: {
                value: ''
            },
            dateline: {
                value: 0
            }
        }
    });


};
