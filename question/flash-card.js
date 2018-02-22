LearnosityAmd.define(['underscore', 'jquery', '/question/flash-card-template.js'], function (_, $, template) {
    function CustomQuestion(initOptions, lrnUtils) {
        this.events = initOptions.events;

        this.events.trigger('ready');
    }

    function CustomQuestionScorer() {
        
    }

    _.extend(CustomQuestionScorer.prototype, {
        isValid: function () {},

        score: function () {},

        maxScore: function () {}
    });

    return {
        Question: CustomQuestion
    };
});