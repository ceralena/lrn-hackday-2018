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
        var $assess = $('.hackday-assess');

        if (question) {
            function checkVisibility() {
                if ($assess.is(':visible')) {
                    clearInterval(interval);
                    question.playLabelAudio();
                }
            }
            var interval = setInterval(checkVisibility, 200);
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

        init.events.on('validate', this.onValidate.bind(this));
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
        },

        setupDomEvents(e) {
            var $el = this.init.$el;
            var events = this.init.events;
            var facade = this.facade;

            var self = this;

            $el.find('input.response').on('keyup', function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);

                self.response = e.currentTarget.value;
                events.trigger('changed', self.response);

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

            var message = valid ? 'Correct! ' : "Sorry, " + this.response + " is wrong. The answer was: ";

            this.playAudio('en', message + this.init.question.valid_response);
        },

        clearValidation() {
             this.init.$el.find('.card')
                .removeClass('correct incorrect')
                .toggleClass('flipped');
        },

        playLabelAudio() {
            this.playAudio('ja', this.init.question.front_title);
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