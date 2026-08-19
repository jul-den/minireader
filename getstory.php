<?php
$sep = DIRECTORY_SEPARATOR;
$StorysData = [];

function getStoryJson($storyID){
    $filepath = __DIR__.DIRECTORY_SEPARATOR.'stories'.DIRECTORY_SEPARATOR.$storyID.DIRECTORY_SEPARATOR.'story.json';
    if(!file_exists($filepath)) return false;
    return json_decode( file_get_contents( $filepath ), true );
}

function getStoryContent($storyID, $chapter = 1) {
    $filepath = __DIR__.DIRECTORY_SEPARATOR.'stories'.DIRECTORY_SEPARATOR.$storyID.DIRECTORY_SEPARATOR.$chapter.'.html';
    if(!file_exists($filepath)) return '';
	return file_get_contents( $filepath );
}

$storyID = isset($_GET["story"]) ? $_GET["story"] : false;
$storyData = false;

if($storyID && ($storyData = getStoryJson($storyID))) {
    
    $thisCStr = isset($_GET["c"]) ? $_GET["c"] : "1";
    if(!isset($storyData['chapterNomenclature'])) $storyData['chapterNomenclature'] = "Chapter";
    $storyIsEnumerated = (!isset($storyData["enumerated"]) || $storyData["enumerated"]);
    $lastCInt = count( $storyData["chapters"] );
    $chaptersReturned = array();
    
    $chapterEnumerator = 1;
    foreach($storyData["chapters"] as $k => $chapter) {
        $chapterIsEnumerated = (!isset($chapter["enumerated"]) || $chapter["enumerated"]);
        if($storyIsEnumerated && $chapterIsEnumerated) {
            $storyData["chapters"][$k]["headline"] = $storyData['chapterNomenclature'] . " " . $chapterEnumerator . ": " . $chapter["title"];
            $chapterEnumerator++;
        } else $storyData["chapters"][$k]["headline"] = $chapter["title"];
    }
    
    if($thisCStr == "all") {
        foreach($storyData["chapters"] as $k => $chapter) {
            $chaptersReturned[$k] = 
                ($chapter["headline"] ? "<h2>{$chapter["headline"]}</h2>" : "").
                getStoryContent($storyID, $k+1);
                //file_get_contents( __DIR__.$sep. $storyID . $sep . ($k+1) . ".html" );
        }
    } else {
        $thisCInt = intval($thisCStr);
        $prevCInt = $thisCInt - 1;
        $nextCInt = $thisCInt + 1;
        $thisChapter = $storyData["chapters"][$thisCInt-1];

        //$thisCHTML = file_get_contents( __DIR__.$sep. $storyID . $sep . $thisCInt . ".html")
        $thisCHTML = getStoryContent($storyID, $thisCInt);
        if($thisCHTML) {
            $chaptersReturned[$thisCInt] = "<h2>{$thisChapter["headline"]}</h2>" . $thisCHTML;
        }

        $chapterEnumerator = 1;
    }
    
}

if (is_dir(__DIR__.DIRECTORY_SEPARATOR.'stories'))
	foreach (scandir(__DIR__.DIRECTORY_SEPARATOR.'stories') as $_storyID) {
		$dir = __DIR__.DIRECTORY_SEPARATOR.'stories'.DIRECTORY_SEPARATOR.$_storyID;
		if (!is_dir($dir) || !file_exists($dir.'/story.json')) continue;
		$StorysData[$_storyID] = getStoryJson($_storyID);
	}
unset($_storyID);

