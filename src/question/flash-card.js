LearnosityAmd.define([
    'underscore',
    'jquery',
    '/question/flash-card-template.js'
],
function (_, $, template) {

    function CustomQuestion(init, utils) {
        var facade = init.getFacade();

        this.events = init.events;

        // this.showingAnswer = false;

        init.$el.html(template({
            front: init.question.front_title,
            back: init.question.valid_response
        }));

        init.$el.on('click', function (e) {
            init.$el.find('.card').toggleClass('flipped');
            // this.showingAnswer = !this.showingAnswer;
        });

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