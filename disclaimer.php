<?php
$disclamerText = "<!-- ===== DISCLAIMER ===== -->\n\n";

function getDisclaimerFile() {
	$disclaimerFile = __DIR__ . '/locals/disclaimer_'.LOCAL_LANG.'.json';
	if (file_exists($disclaimerFile)) $disclaimerBase = json_decode(file_get_contents($disclaimerFile), true);
	return isset($disclaimerBase)? $disclaimerBase : [];
}

function getBaseSection($id) {
global $disclaimerBase;
    return isset($disclaimerBase[$id])? $disclaimerBase[$id] : null;
}

function getSectionsToShow($disclaimer) {
global $disclaimerBase;
    $sectionsToShow = [];
    if ($disclaimer === true) {
        $sectionsToShow = $disclaimerBase;
    }
    elseif (is_array($disclaimer)) {
        foreach ($disclaimerBase as $id => $item) {
            if (isset($item['show']) && $item['show'] !== false)
                $sectionsToShow[$id] = $item;
        }
        foreach ($disclaimer as $id => $item) {
            if (is_bool($item)) {
                if ($item === true) {
                    $base = getBaseSection($id);
                    if ($base) {
                        $sectionsToShow[$id] = $base;
                    }
                    unset($base);
                } else {
                    unset($sectionsToShow[$id]);
                    continue;
                }
            }
            elseif (is_string($item)) {
                $base = getBaseSection($item);
                if ($base) {
                    $sectionsToShow[$item] = $base;
                } else {
                    $base = getBaseSection($id);
                    if ($base) {
                        $sectionsToShow[$id] = $base;
                        $sectionsToShow[$id]['text'] = $item;
                    }
                }
                unset($base);
            }
            elseif (is_array($item)) {
                if (isset($item['show']) && $item['show'] === false) {
                    unset($sectionsToShow[$id]);
                    continue;
                }
                $section = getBaseSection($id);
                if ($section) {
                    if (isset($item['title'])) {
                        $section['title'] = $item['title'];
                    }
                    if (isset($item['text'])) {
                        $section['text'] = $item['text'];
                    }
                    $sectionsToShow[$id] = $section;
                } else {
                    if (isset($item['title']) || isset($item['text'])) {
                        $sectionsToShow[$id] = [
                            'title' => isset($item['title'])? $item['title'] : $item['id'],
                            'text' => isset($item['text']) ? $item['text']: ''
                        ];
                    }
                }
                unset($section);
            }
        }
    }
    return $sectionsToShow;
}

function mergeDisclaimer($base, $override) {
	if ($override === false) return false;
	if (!is_array($override)) return $base;
	return array_replace_recursive($base, $override);
}

function getChapterDisclaimer(){
	global $thisCStr, $storyData;
	if (!isset($thisCStr, $storyData, $storyData['chapters'])) return null;
	if ($thisCStr == 'all') return null;
	$chapterIndex = intval($thisCStr) - 1;
	if (!isset($storyData['chapters'][$chapterIndex], $storyData['chapters'][$chapterIndex]['disclaimer'])) return null;
	return $storyData['chapters'][$chapterIndex]['disclaimer'];
}

$disclaimerBase = getDisclaimerFile();

if (isset($storyData['disclaimer'])) {
    $ChapterDisclaimer = getChapterDisclaimer();
    if (is_null($ChapterDisclaimer)) {
        $ChapterDisclaimer = $storyData['disclaimer'];
    } else $ChapterDisclaimer = mergeDisclaimer($storyData['disclaimer'], $ChapterDisclaimer);
    $sectionsToShow = getSectionsToShow($ChapterDisclaimer);
    unset($ChapterDisclaimer);

    if (!empty($sectionsToShow)) {
        $disclamerText .= '<div class="disclaimer">'."\n";
        foreach ($sectionsToShow as $section) {
            if (empty($section['text']) && empty($section['title'])) continue;
            $disclamerText .= '<details class="disclaimer-section">'."\n";
            if (!empty($section['title'])) {
                $disclamerText .= '<summary class="disclaimer-summary">' . htmlspecialchars($section['title']) . '</summary>'."\n";
            }
            if (!empty($section['text'])) {
                $disclamerText .= '<p>' . nl2br(strip_tags($section['text'], '<strong><em><br><p>')) . "</p>\n";
            }
            $disclamerText .= "</details>\n";
        }
        $disclamerText .= "</div>\n";
    }
}
