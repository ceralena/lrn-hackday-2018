LearnosityAmd.define([
    'underscore',
    'jquery',
    '/question/flash-card-template.js'
],
function (_, $, template) {

    var questions = {};

    var audios = {};

    var playPromises = [];

    const parenRgx = /\s?[\(\[].+?[\)\]]$/;

    var messages = {
        success: ["Sweet as!", "Too easy!", "Well done mate."],
        error: [
            '<phoneme alphabet="ipa" ph="jɜːr, noʊ">yeah, nah</phoneme>.',
            "Nah mate.",
            "Nope.",
            "Na ha."
        ]
    };

    assessApp.on('item:changed', function () {
        var ids = assessApp.getCurrentItem().response_ids;
        var question = questions[ids && ids[0]];
        var $assess = $('.hackday-assess');

        if (question && assessApp.flashcardState.speech) {
            function checkVisibility() {
                if ($assess.is(':visible')) {
                    clearInterval(interval);
                    question.playLabelAudio();
                }
            }
            var interval = setInterval(checkVisibility, 200);
        }
    });

    assessApp.on('item:changing', updateAttemptedCount);

    $(document).on('keyup', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);

        if (code === 39) {
            assessApp.save();
            assessApp.items().next();
        }

        if (code === 37) {
            assessApp.items().previous();
        }
    });

    function updateAttemptedCount() {
        var attemptedCount = assessApp.attemptedItems().length;
        $('.cards-left').html('Cards attempted: ' + attemptedCount);
    }

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
                back: question.valid_response,
                attempted: assessApp.attemptedItems()
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

            $el.find('.button.check').on('click', function (e) {
                facade.validate();
                e.preventDefault();
            });

            $el.find('.button.skip, .back.face, .button.next').on('click', function (e) {
                assessApp.items().next();
                e.preventDefault();
            });
        },

        onValidate: function () {
            var valid = this.facade.isValid(), message;
            var $card = this.init.$el.find('.card');
            var correct = this.init.question.valid_response.replace(parenRgx, '');

            updateAttemptedCount();

            $card
                .toggleClass('correct', valid)
                .toggleClass('incorrect', !valid)
                .toggleClass('flipped');

            if (valid) {
                message = _.shuffle(messages.success)[0]
                    + correct
                    + " is the correct answer";

                $card.find('.validation-message').html('Correct!');
                $card.find('.validation-icon').css({ 'background': 'url("/images/icon_tick.png")' });
            } else {
                message = _.shuffle(messages.error)[0]
                    + " "
                    + this.response
                    + " is wrong. The answer was: "
                    + correct;

                $card.find('.validation-message').html('Incorrect');
                $card.find('.validation-icon').css({ 'background': 'url("/images/icon_cross.png")' });
            }

            if (assessApp.flashcardState.speech) {
                this.playAudio('en', message);
            }
        },

        clearValidation() {
             this.init.$el.find('.card')
                .removeClass('correct incorrect')
                .toggleClass('flipped');
        },

        playLabelAudio() {
            var lang = assessApp.flashcardState.lang;
            var label = this.init.question.front_title.replace(parenRgx, '');

            this.playAudio(lang, label);
        },

        playAudio(lang, text) {
            var key = lang + text;
            var ssml = '';

            this.stopAllAudio();

            if (!audios[key]) {
                if (text.indexOf('<') > -1) {
                    text = `<speak>${text}</speak>`;
                    ssml = 'ssml=true&';
                }

                audios[key] = new Audio([`/speech.php?${ssml}lang=${lang}&text=${text}`]);
            }
            playPromises.push({
                element: audios[key],
                promise: audios[key].play()
            });
        },

        stopAllAudio() {
            _(playPromises).each(function (obj, index) {
                obj.promise && obj.promise.then(function () {
                    obj.element.pause();
                    playPromises.splice(index, 1);
                });
            });
        }
    });

    function CustomQuestionScorer(question, response) {
        this.question = question;
        this.response = response;
    }

    /**
     * Remove parenthesized elements from the response as we can't expect the user to input them correctly.
     * @param response
     * @returns {string}
     */
    function normalizeResponse(response) {
        return response.toLowerCase().replace(parenRgx, '');
    }

    _.extend(CustomQuestionScorer.prototype, {
        isValid: function () {
            if (!this.response) {
                return false;
            }
            let validResponses = this.question.valid_response.split(', ');
            let isValid = false;

            _.forEach(validResponses, (validResponse) => {
                if (normalizeResponse(this.response) === normalizeResponse(validResponse)) {
                    isValid = true;
                    // returning false breaks the loop
                    return false;
                }
            });

            return isValid;
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
