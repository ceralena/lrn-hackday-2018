LearnosityAmd.define([
    'underscore',
    'jquery',
    '/question/flash-card-template.js'
],
function (_, $, template) {

    var questions = {};

    assessApp.on('item:changed', function () {
        var ids = assessApp.getCurrentItem().response_ids;
        var question = questions[ids && ids[0]];

        if (question) {
            question.playLabelAudio()
        }
    });


    $(document).on('keyup', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);

        if (code === 39) {
            assessApp.items().next();
        }

        if (code === 37) {
            assessApp.items().previous();
        }
    });

    function CustomQuestion(init, utils) {
        this.init = init;
        this.facade = init.getFacade();

        this.render();
        this.setupDomEvents();

        init.events.on('validate', this.onValidate, this);
        init.events.trigger('ready');

        questions[init.question.response_id] = this;
    }

    _.extend(CustomQuestion.prototype, {
        render() {
            var question = this.init.question;

            this.init.$el.html(template({
                front: question.front_title,
                back: question.valid_response
            }));

            // this.facade.on('all', function (g) {
            //     console.log(g);
            // });
            // debugger;

            // if (this.init.$el.is(':visible')) {
            //     playAudio('ja', question.front_title);
            // }
        },

        setupDomEvents(e) {
            var $el = this.init.$el;
            var events = this.init.events;
            var facade = this.facade;

            $el.find('input.response').on('keyup', function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);

                events.trigger('changed', e.currentTarget.value);

                if (code === 13) {
                    facade.validate();
                }
            });

            $el.find('.back').on('click', function () {
                assessApp.items().next();
            });
        },

        onValidate: function () {
            var valid = this.facade.isValid();

            this.init.$el.find('.card')
                .toggleClass('correct', valid)
                .toggleClass('incorrect', !valid)
                .toggleClass('flipped');

            this.playAudio('en', this.init.question.valid_response);
        },

        clearValidation() {
             this.init.$el.find('.card')
                .removeClass('correct incorrect')
                .toggleClass('flipped');
        },

        playAudio(lang, text) {
            var sound = new Audio(['/speech.php?lang=' + lang + '&text=' + text]);
            sound.play();
        }
    });

    function CustomQuestionScorer(question, response) {
        this.question = question;
        this.response = response;
    }

    _.extend(CustomQuestionScorer.prototype, {
        isValid: function () {
            return this.response && this.response.toLowerCase() === this.question.valid_response.toLowerCase();
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