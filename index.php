<?php // require("mvc.php") ?>
<html>
    <head>
        <title>Libinkle</title>
        <script src="assets/libinkle.js"></script>
        <script src="https://code.jquery.com/jquery-3.2.1.js"></script>
        <link href="assets/style.css" media="all" rel="stylesheet" />
    </head>
    <body id="body">
        <div class="modal">
            <h1>Press ESC any time to return to this menu</h1>
            <div class="">
                <div id="speedMenu" class="menu">
                    <h2>Speed</h2>
                    <div class="form-group"><input name="speed" type="radio" value="1"> <label>      Speed of Light</label>                    </div>
                    <div class="form-group"><input name="speed" type="radio" value="5"> <label>     Ultra fast</label>                    </div>
                    <div class="form-group"><input name="speed" type="radio" value="15"> <label>     Xtra fast</label>                    </div>
                    <div class="form-group"><input name="speed" type="radio" value="30"> <label>     Fast</label>                    </div>
                    <div class="form-group"><input name="speed" type="radio" value="35" checked="checked"> <label>     Medium </label>                    </div>
                    <div class="form-group"><input name="speed" type="radio" value="40"> <label>     Normal</label>                    </div>
                    <div class="form-group"><input name="speed" type="radio" value="50"> <label>     Slower</label>                    </div>
                    <div class="form-group"><input name="speed" type="radio" value="60"> <label>     Quite slow</label>                    </div>
                    <div class="form-group"><input name="speed" type="radio" value="90"> <label>     Xtra slow</label>                    </div>
                    <div class="form-group"><input name="speed" type="radio" value="250"> <label>    Slooooooow</label>                    </div>
                </div>
            </div>
            <div class="">
                <div class="menu" id="menu">
                    <div style="display:flex;flex-flow: row wrap;">
<!--
                            <div style="width:280px;height:200px;">
                                <dl>
                                    <dd>
                                        <a rel="8v99">test</a>
                                    </dd>
                                    <dd><?= $story["authorName"] ?></dd> 
                                    <dd><?= $story["updated_at"] ?></dd> 

                                </dl>

                            </div>
-->
                        <?php foreach ($storyList as $story) : ?>
                            <div style="width:280px;height:200px;">
                                <dl>
                                    <dd>
                                        <a rel="<?= $story['url_key'] ?>"><?= $story['title'] ?></a>
                                    </dd>
                                    <dd><?= $story["authorName"] ?></dd> 
                                    <dd><?= $story["updated_at"] ?></dd> 

                                </dl>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-container">
            <div class="right" id="out">
            </div>
        </div>

        <script>
            (function () {
                
                var outEl = document.getElementById('out');

                function toggleModal(doHide) {
                    doHide = doHide || false;
                    var modal = document.getElementsByClassName("modal")[0];
                    var style = modal.style.visibility;
                    newstyle = "hidden";
                    if (!doHide && style == "" || style == "hidden") {
                        newstyle = "visible";
                    }
                    modal.style.visibility = newstyle;
                }
                document.onkeyup = function (e) {
                    if (e.keyCode === 27) {
                        toggleModal();
                    }
                }

                // Select a speed on click
                var speedList = document.getElementsByName('speed');
                for (var i = 0; i < speedList.length; i++) {
                    var el = speedList[i];
                    el.onclick = function (event) {
                        var target = event.target;
                        var speed = target.value;
                        queue.msPerChar = speed;
                    }
                }

                // Select a story on click
                var linkList = document.getElementsByTagName('a');
                for (var i = 0; i < linkList.length; i++) {
                    var el = linkList[i];
                    el.onclick = function (event) {
                        outEl.innerHTML = '';
                        var target = event.target;
                        var story = target.getAttribute('rel');
                        if (!story) {
                            return;
                        }
                        runStory(story);
                        toggleModal(true);
                    };
                }


                var printQueue = function (options) {
                    $.extend( this, options)
                    this.readDelay = 0;
                    this.msPerChar = 0.1;
                    this.tickSpeed = 30;
                    this.paragraphsList = [];
                    this.lastSend = new Date().getTime();
                    var instance = this;
                    this.setTick = function () {
                        setInterval(this.tick, this.tickSpeed);
                    };
                    this.tick = function () {
                        if (instance.paragraphsList.length === 0) {
                            return;
                        }
                        var expected = instance.lastSend + instance.readDelay;
                        var now = new Date().getTime();
                        if (expected - now <= 0) {
                            instance.setReadDelay();
                            instance.print();
                        }
                    }
                    this.print = function (bold) {
                        var item = this.paragraphsList.shift();
                        var content = item[0];
                        var bold = item[1];
                        var choice = item[2];
                        var p = document.createElement("p");
                        var t = document.createTextNode(content);
                        if (bold) {
                            var b = document.createElement("b");
                            b.appendChild(t);
                            p.appendChild(b);
                        } else {
                            p.appendChild(t);
                        }
                        if (choice) {
                            el = b ? b : p;
                            el.setAttribute("rel",choice);
                            el.classList.add("choice");
                            
                            // Rollback a choice on clik
                            el.onclick = function (event) {
                                var target = event.target;
                                var choice = target.getAttribute('rel');
                                if (!choice) {
                                    return;
                                }
                                var index = instance.storyMaster.stitchesPath.indexOf( choice );
                                var prevStitch = instance.storyMaster.stitchesPath[ index - 1 ];
                                
                                // It should remove all paragraphs after this one 
                                $(p).nextAll().remove();
                                
                                // It should rollback the story
                                var storyMaster = instance.storyMaster;
                                storyMaster.rollbackByName( prevStitch );
                                var choices = storyMaster.getChoices()
                                
                                instance.reset();
                                while (storyMaster.isNotFinished()) {
                                    var paragraphs = storyMaster.getText();
                                    instance.add(paragraphs)
                                    var choice = storyMaster.chooseRandom();
                                    var currentStitch = storyMaster.choicesPath[storyMaster.choicesPath.length - 1];
                                    instance.add([choice], 'bold', currentStitch)
                                }
                                
                                
                            }

                        }
                        outEl.appendChild(p);
                        p.classList.add('fade');
                        p.scrollTop = p.scrollHeight;
                        outEl.scrollTop = outEl.scrollHeight - outEl.clientHeight;
                    }
                    this.reset = function () {
                        this.paragraphsList = [];
                        this.readDelay = 0;
                    }

                    this.add = function (paragraphsList, format, currentStitch) {
                        format = format || '';
                        paragraphsList.forEach((item) => {
                            this.paragraphsList.push([item, format, currentStitch]);
                        });
                    }
                    this.setSpeed = function (msPerChar) {
                        this.msPerChar = msPerChar;
                        this.setReadDelay();
                    }

                    this.setReadDelay = function ( ) {
                        var current = instance.paragraphsList[0][0];
                        var newDelay = current.length * instance.msPerChar;
                        instance.readDelay = newDelay;
                        instance.lastSend = new Date().getTime();
                    }

                    this.setTick();
                    return this;
                };

                var queue = new printQueue();
                function runStory(story) {
                    $.ajax("/get.php?q=" + story, {
                        success: function (data, textStatus, jqXHR) {
                            var storyMaster = new inkle({source: data});
                            storyMaster.start();
                            queue.reset();
                            queue.storyMaster = storyMaster;
                            queue.add([storyMaster.story.stats.title], 'title')
                            while (storyMaster.isNotFinished()) {
                                var paragraphs = storyMaster.getText();
                                queue.add(paragraphs)
                                var choice = storyMaster.chooseRandom();
                                var currentStitch = storyMaster.choicesPath[storyMaster.choicesPath.length - 1];
                                queue.add([choice], 'bold', currentStitch)
                            }
                            queue.add(storyMaster.getText())
                            queue.add(["The end"], 'bold')
                        }
                    });
                }
            })()
        </script>
    </body>
</html>
