'use strict';

/**
 * Story Model
 * Encapsulates an inkle history metadata and stitches list
 * @param {string} string   Stringified JSON object
 * @class
 */
var storyModel = function storyModel(string, inkle) {
    this.inkle = inkle;
    this.story = JSON.parse(string);
    this.stitches = this.story.data.stitches;
    /**
     * A simple object to access the critical story informations
     */
    this.stats = {
        updatedAt: this.story.updated_at,
        title: this.story.title,
        initial: this.story.data.initial,
        stitchesCount: _.size(this.stitches)
    };
    var stitches = this.story.data.stitches;
    var stitchesKeys = _.keys(this.story.data.stitches).sort();
    return this;
};
/*
 * Retrieve a single "stitch" by string
 * @method getStitch
 * @param {string} stitch_name
 * @returns {nm$_libinkle.stitchModel}
 */
storyModel.prototype.getStitch = function (stitch_name) {
    if (_.has(this.story.data.stitches, stitch_name)) {
        return new stitchModel(this.story.data.stitches[stitch_name], stitch_name, this.inkle);
    }
    msg = 'No stitch found for ' + stitch_name;
    throw msg;
};

/**
 * Encapsulates a single stitch
 * @class
 * @param {object} stitch A serialized stitch to be hydrated
 * @param {string} name the ID of the stitch
 */
var stitchModel = function stitchModel(stitch, name, inkle) {
    var _this = this;

    var flagList = inkle.flagList;
    var content = stitch.content;
    this.name = name || 'unknown';
    this.choices = {};
    var _a = content;

    var _f = function _f(value, key) {
        // is it a message
        if (_.isString(value)) {
            _this.message = value;
        }
        // is it a divert
        else if (_.has(value, 'divert')) {
                _this.divert = value.divert;
            }
            // is it an image
            else if (_.has(value, 'image')) {
                    _this.image = value.image;
                }
                // is it a flag
                else if (_.has(value, 'flagName')) {
                        flagList.push(value['flagName']);
                    }
                    // it it a choice
                    else if (_.has(value, 'linkPath')) {
                            // it should check conditions
                            var absentFlagErrors = [];
                            var presentFlagErrors = [];
                            debugger;
                            // has negate conditions
                            if (value['notIfConditions'] && value['notIfConditions'].length > 0) {
                                var _a2 = value['notIfConditions'];

                                var _f2 = function _f2(flagContainer) {
                                    if (flagList.indexOf(flagContainer['notIfCondition']) !== -1) {
                                        absentFlagErrors.push(flagContainer['notIfCondition']);
                                    }
                                };

                                for (var _i2 = 0; _i2 < _a2.length; _i2++) {
                                    _f2(_a2[_i2], _i2, _a2);
                                }

                                undefined;
                            }
                            // has positive conditions
                            if (value['ifConditions'] && value['ifConditions'].length > 0) {
                                var _a3 = value['ifConditions'];

                                var _f3 = function _f3(flagContainer) {
                                    if (flagList.indexOf(flagContainer['ifCondition']) === -1) {
                                        presentFlagErrors.push(flagContainer['ifCondition']);
                                    }
                                };

                                for (var _i3 = 0; _i3 < _a3.length; _i3++) {
                                    _f3(_a3[_i3], _i3, _a3);
                                }

                                undefined;
                            }
                            if (absentFlagErrors.length === 0 && presentFlagErrors.length === 0) {
                                _this.choices[value['linkPath']] = value['option'];
                            }
                        }
    };

    for (var _i = 0; _i < _a.length; _i++) {
        _f(_a[_i], _i, _a);
    }

    undefined;
    this.isFinal = function () {
        return content.length === 1;
    };
    this.isChoice = function () {
        return _.keys(this.choices).length > 0;
    };
    this.nextStitch = function () {
        return this.divert;
    };
    this.getText = function () {
        return this.message;
    };
    this.getChoices = function () {
        return this.choices;
    };

    return this;
};

/**
 *
 * @param {object} options
 * @returns {nm$_libinkle.inkle}
 * @class
 */
var inkle = function inkle(options) {
    _.assign(this, options);
    this.flagList = [];
    this.paragraphList = null;
    this.choicesList = null;
    var story = this.story || null;
    if (_.has(this, 'source')) {
        this.story = new storyModel(this.source, this);
    }
};

/**
 * Static story models factory
 *
 * @param {string} string a JSON file
 * @returns {nm$_libinkle.storyModel|Object.prototype.parse.story}
 */
inkle.prototype.parse = function (string) {
    var story = new storyModel(string, this);
    return story;
};

/**
 * Static story model stats builder
 *
 * @param {string} story
 * @returns {inkle.prototype.stats.story.stats|.inkle.prototype@call;parse.stats|Object.prototype.stats.stats}
 */
inkle.prototype.stats = function (story) {
    var story = typeof story === 'string' ? inkle.prototype.parse(story) : story;
    var stats = story.stats;
    return stats;
};

/**
 * Retrieves from a single stich all connected stitches
 *
 * @param {string} stitch_name  A stitch key
 * @returns {Boolean}
 */
inkle.prototype.start = function (stitch_name) {
    stitch_name = stitch_name || this.story.stats.initial;
    var initial = this.story.getStitch(stitch_name);
    this.currentStitches = this.getAllStitches(initial);
    return this;
};

/**
 * From a single Stitch Model, retrieves all related stitches until choice occurs
 *
 * @param {type} currentStitch
 * @returns {Boolean}
 */
inkle.prototype.getAllStitches = function (currentStitch) {
    var final = false;
    var choice = false;
    this.paragraphList = [];
    this.choicesList = [];
    var nextStitch = '';
    while (final === false && choice === false) {
        this.paragraphList.push(currentStitch.getText());
        if (currentStitch.isChoice()) {
            choice = true;
            this.choicesList = currentStitch.getChoices();
        } else if (currentStitch.isFinal()) {
            final = true;
        } else {
            nextStitch = currentStitch.nextStitch();
            currentStitch = this.story.getStitch(nextStitch);
        }
    }
    return true;
};

/**
 * Return text paragraphs at current stage
 *
 * @returns {Array}
 */
inkle.prototype.getText = function () {
    return this.paragraphList;
};

/**
 * Return choices paragraphs at current stage in the form
 *  stitch_link => question paragraph
 *
 * @returns {Array}
 */
inkle.prototype.getChoices = function () {
    return this.choicesList;
};

/**
 * Returns a list of current choices only by stitch name
 *
 * @returns {Boolean}
 */
inkle.prototype.getChoicesByName = function () {
    return _.keys(this.choicesList);
};

/**
 * Decide to progress based on a given choice, by stitch_name
 *
 * @param {type} stitch_name
 * @returns {Boolean}
 */
inkle.prototype.choose = function (stitch_name) {
    return this.start(stitch_name);
};

/**
 * Let chance decide the next story move
 *
 * @returns {Array|nm$_libinkle.inkle.prototype.chooseRandom.choices}
 */
inkle.prototype.chooseRandom = function () {
    var choices = this.getChoices();
    var choicesList = this.getChoicesByName();
    var choice = Math.floor(Math.random() * choicesList.length);
    this.choose(choicesList[choice]);
    return choices[choicesList[choice]];
};

/**
 * Simple helper : is story not fnished?
 *
 * @returns {Boolean}
 */
inkle.prototype.isNotFinished = function () {
    return this.getChoicesByName().length !== 0;
};

/**
 * Simple helper : is story fnished?
 *
 * @returns {Boolean}
 */
inkle.prototype.isFinished = function () {
    return this.getChoicesByName().length === 0;
};
