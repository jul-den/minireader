<?php
$disclamerText = "<!-- ===== ДИСКЛЕЙМЕР ===== -->\n\n";

function getDisclaimerFile() {
	$disclaimerFile = __DIR__ . '/locals/disclaimer_'.LOCAL_LANG.'.json';
	if (file_exists($disclaimerFile)) $disclaimerBase = json_decode(file_get_contents($disclaimerFile), true);
	return isset($disclaimerBase)? $disclaimerBase : [];
}

// Функция для получения секции по id из базы
function getBaseSection($id) {
global $disclaimerBase;
    return isset($disclaimerBase[$id])? $disclaimerBase[$id] : null;
}

function getSectionsToShow($disclaimer) {
global $disclaimerBase;
    $sectionsToShow = [];
    // Если true — показываем все секции из базы
    if ($disclaimer === true) {
        $sectionsToShow = $disclaimerBase;
    }
    // Если массив — обрабатываем
    elseif (is_array($disclaimer)) {
        foreach ($disclaimerBase as $id => $item) {
            if (isset($item['show']) && $item['show'] !== false)
                $sectionsToShow[$id] = $item;
        }
        foreach ($disclaimer as $id => $item) {
            // Если это boolean - показываем секцию
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
            // Если это строка — id секции
            elseif (is_string($item)) {
                $base = getBaseSection($item);
                if ($base) {
                    $sectionsToShow[$item] = $base;
                } else { // подмена text ?
                    $base = getBaseSection($id);
                    if ($base) {
                        $sectionsToShow[$id] = $base;
                        $sectionsToShow[$id]['text'] = $item;
                    }
                }
                unset($base);
            }
            // Если это объект/массив с id
            elseif (is_array($item)) {
                // Проверяем, нужно ли показывать
                if (isset($item['show']) && $item['show'] === false) {
                    unset($sectionsToShow[$id]);
                    continue;
                }
                // Ищем базовую секцию
                $section = getBaseSection($id);
                if ($section) {
                    // Если есть переопределения — применяем
                    if (isset($item['title'])) {
                        $section['title'] = $item['title'];
                    }
                    if (isset($item['text'])) {
                        $section['text'] = $item['text'];
                    }
                    $sectionsToShow[$id] = $section;
                } else {
                    // Если секции с таким id нет в базе — создаём из данных
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

// Загружаем базу секций
$disclaimerBase = getDisclaimerFile();

// Проверяем, есть ли в storyData поле disclaimer
if (isset($storyData['disclaimer'])) {
    $ChapterDisclaimer = getChapterDisclaimer();
    if (is_null($ChapterDisclaimer)) {
        $ChapterDisclaimer = $storyData['disclaimer'];
    } else $ChapterDisclaimer = mergeDisclaimer($storyData['disclaimer'], $ChapterDisclaimer);
    $sectionsToShow = getSectionsToShow($ChapterDisclaimer);
    unset($ChapterDisclaimer);

    // Выводим секции
    if (!empty($sectionsToShow)) {
        $disclamerText .= '<div class="disclaimer">'."\n";
        foreach ($sectionsToShow as $section) {
            if (empty($section['text']) && empty($section['title'])) continue;
            // Открываем <details>
            $disclamerText .= '<details class="disclaimer-section">'."\n";
            if (!empty($section['title'])) {
                // Заголовок в <summary>
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

