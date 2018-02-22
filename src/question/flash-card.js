LearnosityAmd.define([
    'underscore',
    'jquery',
    '/question/flash-card-template.js'
],
function (_, $, template) {

    function CustomQuestion(init, utils) {
        this.events = init.events;

        init.$el.html(template({
            front: init.question.front_title,
            back: init.question.valid_response
        }));

        this.events.trigger('ready');
    }

    function CustomQuestionScorer() {}

    _.extend(CustomQuestionScorer.prototype, {
        isValid: function () {
            return this.response.toLowerCase() === this.question.valid_response.toLowerCase();
        },

        score: function () {
            return this.isValid() ? this.maxScore() : 0;
        },

        maxScore: function () {
            return this.question.score || 1;
        }
    });

    return {
        Question: CustomQuestion,
        Scorer: CustomQuestionScorer
    };
});