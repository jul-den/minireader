<?php
require_once "getimg.php";
getImg();
require_once "getstory.php";
require_once "disclaimer.php";
 ?><!doctype html>
<html>

<head>
    <title>
        <?php
        if ($storyData) echo $storyData["title"];
        if(isset($thisChapter["headline"])) echo ": " . $thisChapter["headline"];
    ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta
        name="description"
        content="<?= isset($storyData["description"]) ? $storyData["description"] : LOCAL_APP_DESCRIPTION ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://fonts.googleapis.com/css?family=Old+Standard+TT' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
    <script src="/js/jquery-3.3.1.min.js"></script>
    <script src="/js/lightbox.js"></script>
    <link rel="stylesheet" href="/css/style.css?<?= filemtime("css/style.css"); ?>">
    <link rel="stylesheet" href="/css/style_ai.css?<?= filemtime("css/style_ai.css"); ?>">
    <link rel="stylesheet" href="/css/flag-icons.min.css?<?= filemtime("css/flag-icons.min.css"); ?>">

    <script>
        const storyID = (new URLSearchParams(location.search)).get('story') || '';
        const thisCStr = (new URLSearchParams(location.search)).get('c') || '';
        let thisCInt = parseInt(thisCStr);

        $(document).ready(function() {
            localStorage.darkmode = localStorage.darkmode || "false";
            if (localStorage.darkmode == "true") toggleDark();
            $(".nav-bar").clone().insertAfter(".story-container");
            $('#fontInc').on('click', function() { changeFontSize(1); });
            $('#fontDec').on('click', function() { changeFontSize(-1); });
        });

        function toggleDark() {
            $("#container").toggleClass("dark");
            localStorage.darkmode = $("#container").hasClass("dark");
        }

        function gotoURL(caller) {
            var newpage;
            if (caller.value != "" + thisCInt) {
                location.search = "?story=" + storyID + "&c=" + caller.value;
            }
        }

        function changeFontSize(delta) {
            $('.story-container p, .story-container h1, .story-container h2').css('font-size', function() {
                const val = $(this).css('font-size');
                const current = parseFloat(val);
                const dem = val.split(current)[1];
                var newval = current + delta;
                if (newval < 8) newval = 8;
                if (newval > 30) newval = 30;
                return newval+dem;
            });
        }
    </script>
</head>

<body>
    <div id="container">
        <div id="image-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; cursor:zoom-out;">
          <img id="overlay-img" src="" alt="" style="max-width:90%; max-height:90%; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);">
        </div>
        <?php if(!$storyID): ?>
            <div id='main'>
                <h1>MiniReader</h1>
                <h2><?=LOCAL_CHOOSE_A_STORY?></h2>
                <div class='story-index center'>
                    <?php foreach ($StorysData as $thisStoryData): ?>
                    <p>
                        <a href='?story=<?= $thisStoryData["storyID"] ?>'>
                            <?= $thisStoryData["title"]?>
                        </a>
                        <?php if(isset($thisStoryData["homepage"])): ?>
                            &bull; <a href='<?= $thisStoryData["homepage"]?>'><?=LOCAL_MORE_INFO ?></a>
                        <?php endif ?>
                    </p>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endif ?>
        <div class="links">
            <a href="/"><?= LOCAL_ALL_STORIES ?></a>
            &bull;
            <?php if(isset($storyData, $storyData["homepage"])): ?>
                <a href='<?= $storyData["homepage"]; ?>' target='_blank' rel='noopener'>
                    <?= LOCAL_STORY_WEBSITE ?>
                </a> &bull;
            <?php endif; ?>
            <a href="#" onclick="toggleDark()"><?= LOCAL_TOGGLE_BW ?></a>

           <span class="fi fi-<?= LOCAL_LANG ?>"></span>

            <?php if(isset($storyData) && !empty($storyData)):
            ?><button id="fontDec" title="<?= LOCAL_FONT_DECREASE ?>"><?= LOCAL_FONT_DECREASE_TXT ?></button>
            <button id="fontInc" title="<?= LOCAL_FONT_INCREASE ?>"><?= LOCAL_FONT_INCREASE_TXT ?></button><?php
            endif; ?>
        </div>
        
        
        <?php if($storyData): ?>
        <div id="main">
            <div class="nav-bar">
                <?php if ($lastCInt > 1 && $storyData && $storyID && sizeof($chaptersReturned)): ?>
                <?php 
					if ($thisCStr == "all") {
						$thisCInt = 2;
					}
                    ?><a class="nav-button first" <?php if ($thisCInt > 1) {
                                                    echo 'href="?story=' . $storyID . '&c=1"';
                                                } ?>>
                        <?= LOCAL_FIRST ?>
                    </a>
                    <?php if ($thisCStr != "all") :
                    ?><a class="nav-button prev" <?php if ($prevCInt > 0) {
                                                    echo 'href="?story=' . $storyID . '&c=' . strval($prevCInt) . '"';
                                                } ?>>
                        <?= LOCAL_PREVIOUS ?>
                    </a>
                    <a class="nav-button next" <?php if ($nextCInt <= $lastCInt) {
                                                    echo 'href="?story=' . $storyID . '&c=' . strval($nextCInt) . '"';
                                                } ?>>
                        <?= LOCAL_NEXT ?>
                    </a><?php endif ?>
                    <a class="nav-button last" <?php if ($lastCInt != $thisCInt) {
                                                    echo 'href="?story=' . $storyID . '&c=' . strval($lastCInt) . '"';
                                                } ?>><?= LOCAL_LAST ?></a>
                    <?php if ($thisCStr != "all") :
                    ?><a class="nav-button" href="?story=<?= $storyID; ?>&c=all"><?= LOCAL_ALL_CHAPTERS ?></a><?php endif ?>
                    <select class="chapselect" onchange="gotoURL(this)">
                        <option value="" disabled<?= ($thisCStr != "all")? '': ' selected' ?>><?= LOCAL_CHAPTER_GO_TO ?></option>
                        <?php foreach ($storyData["chapters"] as $k => $chapter): ?>
                            <?php
                                $chapterIndex = $k + 1;
                                $selected = ($thisCStr != "all") && $thisCInt === $chapterIndex ? "selected" : "";
                            ?>
                            <option value='<?= $chapterIndex ?>' <?= $selected ?>>
                                <?= $chapter["headline"]; ?>
                            </option>
                        <?php endforeach ?>
                    </select>

                <?php endif ?>
            </div>

            <div class="story-container">
                <?php
                    if (isset($disclamerText)) echo $disclamerText;

                    if($_GET["story"] && !$storyData): ?>
                    <div class='center'><?= LOCAL_STORY_NOT_FOUND ?></div>
                <?php elseif($chaptersReturned && count($chaptersReturned)): ?>
                    <?php $chapter_exists = true; ?>
                    <h1><?= $storyData["title"] ?></h1>
                    <?php foreach ($chaptersReturned as $chapternumber => $content): ?>
                        <?= $content ?>
                        <hr>
                    <?php endforeach ?>
                <?php else: ?>
                    <div class='center'><?= LOCAL_CHAPTER_NOT_EXIST ?></div>
                <?php endif?>
            </div>

            <?php if ($chapter_exists): ?>

            <?php endif ?>
            
        </div>
        <?php endif; ?>

    </div>

</body>
</html>