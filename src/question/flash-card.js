LearnosityAmd.define([
    'underscore',
    'jquery',
    '/question/flash-card-template.js'
],
function (_, $, template) {

    var questions = {};

    var playPromises = [];

    const parenRgx = /\s?[\(\[].+?[\)\]]$/;

    var messages = {
        success: [
            "Sweet as!",
            "Too easy!",
            "Well done mate.",
            "Easy peazy lemon squeezy!",
            "Grouse!",
            "Bonza mate!",
            "That's awesome ay!"
        ],
        error: [
            '<phoneme alphabet="ipa" ph="jɜːr">yeah</phoneme> <phoneme alphabet="ipa" ph="nɑːr">nah</phoneme> mate.',
            "Nah mate.",
            "Nope.",
            "Na ha.",
            "Try again.",
            "That's not it.",
            "I don't like it."
        ]
    };

    const languageNames = {
        'fr': 'French',
        'ja': 'Japanese',
        'es': 'Spanish',
        'en': 'English'
    };

    // wrap event stuff inside a check for assessApp so that we can still execute successfully in server-side custom scoring
    if (typeof assessApp !== 'undefined') {
        assessApp.on('item:changed', function () {
            var ids = assessApp.getCurrentItem().response_ids;
            var question = questions[ids && ids[0]];

            if (question) {
                question.onShow();
            }
        });

        assessApp.on('item:changing', updateAttemptedCount);

        $(document).on('keyup', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);

            if (code === 39) {
                assessApp.items().next();
            }

            if (code === 37) {
                assessApp.items().previous();
            }
        });
    }


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

    CustomQuestion.prototype.render = function() {
        var question = this.init.question;
        var lang = assessApp.flashcardState.lang;

        this.init.$el.html(template({
            front: question.front_title,
            back: question.valid_response,
            attempted: assessApp.attemptedItems(),
            lang: lang,
            language: languageNames[lang]
        }));
    };

    CustomQuestion.prototype.setupDomEvents = function(e) {
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
    };

    CustomQuestion.prototype.onValidate = function () {
        var valid = this.facade.isValid(), message;
        var $card = this.init.$el.find('.card');
        var correct = this.init.question.valid_response.replace(parenRgx, '');

        updateAttemptedCount();

        assessApp.submit();

        $card
            .toggleClass('correct', valid)
            .toggleClass('incorrect', !valid)
            .toggleClass('flipped');

        if (valid) {
            message = _.shuffle(messages.success)[0]
                + " '"
                + correct
                + "' is the correct answer";

            $card.find('.validation-message').html('Correct!');
            $card.find('.validation-icon').css({ 'background': 'url("/images/icon_tick.png")' });
        } else {
            message = _.shuffle(messages.error)[0]
                + " '"
                + this.response
                + "' is wrong. The answer was: "
                + correct;

            $card.find('.validation-message').html('Incorrect');
            $card.find('.validation-icon').css({ 'background': 'url("/images/icon_cross.png")' });
        }

        if (assessApp.flashcardState.speech) {
            this.playAudio('en', message);
        }
    };

    CustomQuestion.prototype.clearValidation = function () {
         this.init.$el.find('.card')
            .removeClass('correct incorrect')
            .toggleClass('flipped');
    };

    CustomQuestion.prototype.playLabelAudio = function () {
        var lang = assessApp.flashcardState.lang;
        var label = this.init.question.front_title.replace(parenRgx, '');

        this.playAudio(lang, label);
    };

    CustomQuestion.prototype.playAudio = function(lang, text) {
        var ssml = '';

        this.stopAllAudio();

        if (text.indexOf('<') > -1) {
            text = `<speak>${text}</speak>`;
            ssml = 'ssml=true&';
        }

        var audio = new Audio([`/speech.php?${ssml}lang=${lang}&text=${text}`]);

        playPromises.push({
            element: audio,
            promise: audio.play()
        });
    };

    CustomQuestion.prototype.stopAllAudio = function() {
        _(playPromises).each(function (obj, index) {
            obj.promise && obj.promise.then(function () {
                obj.element.pause();
                playPromises.splice(index, 1);
            });
        });
    };

    CustomQuestion.prototype.onShow = function () {
        if (assessApp.flashcardState.speech) {
            this.playLabelAudio();
        }
    };

    /**
     * Remove parenthesized elements from the response as we can't expect the user to input them correctly.
     * @param response
     * @returns {string}
     */
    function normalizeResponse(response) {
        return response.toLowerCase().replace(parenRgx, '');
    }

    function CustomQuestionScorer(question, response) {
        this.question = question;
        this.response = response;
    }

    CustomQuestionScorer.prototype.isValid = function () {
        if (!this.response) {
            return false;
        }
        let validResponses = this.question.valid_response.split(', ');
        let i;

        for (i = 0; i < validResponses.length; i++) {
            if (normalizeResponse(this.response) === normalizeResponse(validResponses[i])) {
                return true;
            }
        }
        return false;
    };

    CustomQuestionScorer.prototype.score = function () {
        return this.isValid() ? this.maxScore() : 0;
    };

    CustomQuestionScorer.prototype.maxScore = function () {
        return this.question.score || 1;
    };

    return {
        Question: CustomQuestion,
        Scorer: CustomQuestionScorer
    };
});
