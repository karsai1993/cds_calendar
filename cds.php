<?php
/**
* Plugin Name: Chalmers Dance Society Shortcode Plugin
* Description: This plugin create a shortcode that can be utilised for creating the calendar.
* Version: 1.0
* Author: CDS Admin
**/

require 'cds_css.php';
require 'cds_utils.php';
require 'cds_functions.php';

$page = null;
$pageSize = null;
$query = null;

add_shortcode('cds_calendar', 'resolveCdsCalendar');

function resolveCdsCalendar($original_attributes) {
    $attributes = getActualAttributes($original_attributes);

	$calendarId = $attributes['calendar_id'];
	$apiKey = $attributes['api_key'];

    $queryString = urldecode($_SERVER['QUERY_STRING']);

	$eventsResult = loadEvents($calendarId, $apiKey, $queryString);

	return convertEventsToHtml($eventsResult);
}

function getActualAttributes($original_attributes) {
	$default = array(
        'calendar_id' => 'undefined',
        'api_key' => 'undefined'
    );
    return shortcode_atts($default, $original_attributes);
}

function identifyMaxResults($queryParams) {
    global $pageSize;
    $pageSize = is_null($queryParams['ms']) ? 10 : intval($queryParams['ms']);
    return $pageSize;
}

function loadEvents($calendarId, $apiKey, $queryString) {
	$ch = curl_init();

    $requestUrl = 'https://www.googleapis.com/calendar/v3/calendars/';
    $requestUrl = $requestUrl.$calendarId;
    $requestUrl = $requestUrl.'/events?key=';
    $requestUrl = $requestUrl.$apiKey;
    $requestUrl = $requestUrl.'&singleEvents=true&orderBy=startTime';

    $defaultTimeMin = date("Y-m-d\TH:i:s\Z");

    parse_str($queryString, $queryParams);
    if (!is_null($queryParams['q']) && $queryParams['q'] != '') {
        $requestUrl = $requestUrl.'&timeMin='.$defaultTimeMin.'&q='.urlencode($queryParams['q']);
        global $page;
        global $query;
        $page = null;
        $query = $queryParams['q'];
    } else if (!is_null($queryParams['p'])) {
        $requestUrl = $requestUrl.'&timeMin='.$defaultTimeMin.'&maxResults='.identifyMaxResults($queryParams).'&pageToken='.urlencode($queryParams['p']);
        global $page;
        global $query;
        $page = $queryParams['p'];
        $query = null;
    } else {
        $requestUrl = $requestUrl.'&timeMin='.$defaultTimeMin.'&maxResults='.identifyMaxResults($queryParams);
        global $page;
        global $query;
        $page = null;
        $query = null;
    }

	curl_setopt($ch, CURLOPT_URL, $requestUrl);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);

	if (curl_errno($ch)) {
	    echo '<div>Problem occurred during composing the request!</div>';
	    echo '<div>'.curl_error($ch).'</div>';
	} else {
	  $decodedResult = json_decode($result, true);

	  if (!is_null($decodedResult['error'])) {
        echo '<div>We got an error from the server! It probably means that the request url has been corrupted.</div>';
        echo '<pre>'.json_encode($decodedResult).'</pre>';
      }
	}

	curl_close($ch);

	return $decodedResult;
}

function convertEventsToHtml($eventsResult) {
    $events = $eventsResult['items'];

    $numItems = count($events);
    $i = 0;

    global $query;
    if (!is_null($query)) {
        $content = '
            <div style="'.showSearchContainerStyle().'">
                <div style="'.showSearchOutputContainerStyle().'">
                    <div style="'.showSearchOutputHeaderContainerStyle().'">Applied filter</div>
                    <div style="'.showSearchOutputValueContainerStyle().'">'.$query.'</div>
                </div>
                <button style="'.btnStyle(20).'" onclick="window.open(\''.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'\', \'_self\');">Remove filter</button>
            </div>
        ';
    } else {
        $content = '
            <form action="" method="post" style="'.searchContainerStyle().'">
                <input style="'.searchInputContainerStyle().'" type="text" name="search_query" placeholder="Filter in all content">
                <button style="'.btnStyle(0).'" name="search_apply">Apply filter</button>
            </form>
        ';
    }

    if (isset($_POST['search_apply'])) {
        $content = '<div style="'.searchLoadingContainer().'">Please, wait! We are loading your search results.</div>';
        $content = $content.'
            <script type="text/javascript">
                window.open(
                    "'.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'?q='.urlencode($_POST['search_query']).'",
                    "_self"
                );
            </script>
        ';
    }

    $content = $content.'<div>';
    if ($numItems == 0) {
        $content = $content.'<div style="'.noEventsFoundStyle().'">We could not find any events.</div>';
    } else {
        foreach ($events as $event) {
            $content = $content.convertEventToHtml($event, ++$i === $numItems);
        }
    }
    $content = $content.'</div>';

    $pageToken = $eventsResult['nextPageToken'];

    global $pageSize;

    global $page;
    $content = $content.'
        <div style="'.navigationAndPageContainerStyle().'">
            <div style="'.navigationBtnContainerStyle().'">
                <button
                    style="'.pageNavigationBtnStyle(is_null($page)).'"
                    onclick="onPreviousClicked(\''.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'\')"
                >
                    Previous page
                </button>
                <div style="'.navigationBtnPlaceholderStyle(!is_null($page)).'"></div>
                <button
                    style="'.pageNavigationBtnStyle(is_null($pageToken)).'"
                    onclick="onNextPageClicked(
                        \''.$pageToken.'\',
                        \''.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'?p='.urlencode($pageToken).'\'
                    )"
                >
                    Next page
                </button>
            </div>
            <form action="" method="post">
                <div style="'.pageSizeContainer().'">
                    <div for="page_size_options" style="'.pageSizeLabelContainer().'">Events per page</div>
                    <select name="page_size_options" id="page_size_options" style="'.pageSizeSelectContainer().'">
                        <option value="5"'.($pageSize === 5 ? ' selected' : '').'>5</option>
                        <option value="10"'.($pageSize === 10 ? ' selected' : '').'>10</option>
                        <option value="30"'.($pageSize === 30 ? ' selected' : '').'>30</option>
                    </select>
                </div>
                <button name="page_size_option_btn" style="'.btnStyle(0).'">Apply page size</button>
            </form>
        </div>
    ';

    if (isset($_POST['page_size_option_btn'])) {
        $content = '<div style="'.searchLoadingContainer().'">Please, wait! We are loading the page with the desired size.</div>';
        $content = $content.'
            <script type="text/javascript">
                window.open(
                    "'.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'?ms='.urlencode($_POST['page_size_options']).'",
                    "_self"
                );
            </script>
        ';
    }

    // TODO: delete when no need for logging
    //global $baseRequestUri;
    //$content = $content.'<div>'.$baseRequestUri.'</div>';
    //$content = $content.'<pre>'.json_encode($_SERVER).'</pre>';
    //$content = $content.'<div>'.urldecode($_SERVER['QUERY_STRING']).'</div>';
    //$content = $content.'<div>'.urldecode(home_url( $_SERVER['REQUEST_URI'] )).'</div>';
    //$content = $content.'<div>'.urldecode(esc_url(home_url( $_SERVER['REQUEST_URI'] ))).'</div>';
    // TODO: delete when no need for events response
    //$content = $content.'<pre>'.json_encode($events).'</pre>';
    //$content = $content.'<div>'.date("Y").'</div>';
    //$content = $content.'<div>'.date("m").'</div>';
    //$content = $content.'<div>'.identifyTimeMin(null, null).'</div>';
    //$content = $content.'<div>'.identifyTimeMax(null, null).'</div>';

	return $content.'<script>'.fetchFunctions().'</script>';
}

function convertEventToHtml($event, $isLastItem) {
    $mainContainerExtraStyle = $isLastItem ? '' : mainContainerDividerStyle();
    return
        '<div style="'.mainContainerStyle().$mainContainerExtraStyle.'">
            <div style="'.startDateContainerStyle().'">'.composeEventStartContainer($event).'</div>
            <div style="'.contentContainerStyle().'">'.composeContentContainer($event).'</div>
            <div>'.composeExtrasContainer($event).'</div>
        </div>';
}

function composeExtrasContainer($event) {
    $eid = urlencode(explode('eid=', $event['htmlLink'])[1]);
    $source = urlencode($event['organizer']['email']);
    return
        '<div style="'.extraContainerStyle().'" onclick="window.open(\'https://calendar.google.com/calendar/event?action=TEMPLATE&amp;tmeid='.$eid.'&amp;tmsrc='.$source.'\', \'_blank\');">
            <img style="'.calendarIconStyle().'" border="0" src="https://cdn.pixabay.com/photo/2016/07/31/20/54/calendar-1559935_960_720.png">
            <div style="'.calendarIconTextStyle().'">Add to Google Calendar</div>
        </div>';
}

function composeEventStartContainer($event) {
    $originalDate = is_null($event['start']['dateTime']) ? $event['start']['date'] : $event['start']['dateTime'];
    $timestamp = strtotime($originalDate);
    $formattedDate = date('D M d Y', $timestamp);
    list($weekDay, $month, $day, $year) = explode(' ', $formattedDate);
    return
        '
         <div style="'.startDateMonthStyle().'">'.$month.'</div>
         <div style="'.startDateDayStyle().'">'.$day.'</div>
         <div style="'.startDateWeekDayStyle().'">'.$weekDay.'</div>
         <div style="'.startDateYearStyle().'">'.$year.'</div>
        ';
}

function composeContentContainer($event) {
    $originalDescription = $event['description'];
    $descriptionData = resolveDescriptionData($originalDescription);
    $description = $descriptionData['description'];
    $eventCategories = $descriptionData['eventCategories'];
    $eventTitleUrl = $descriptionData['eventTitleUrl'];

    $eventId = $event['id'];

    $content = $content.(
        is_null($eventCategories)
            ?
                ''
            :
                ''.composeEventCategoryTags($eventCategories).''
    );
    $content = $content.(
        is_null($event['summary'])
            ?
                '
                    <div style="'.eventTitleStyle().'">No title specified</div>
                '
            :
                '
                    <div style="'.eventTitleStyle().contentPartContainerStyle().'">'.
                        (
                            is_null($eventTitleUrl)
                                ?
                                    ''.resolveEventContentValue($event['summary'], 'title', $eventId)
                                :
                                    '
                                        <a style="'.eventTitleLinkStyle().'" href="'.$eventTitleUrl.'" target="_blank">
                                            '.resolveEventContentValue($event['summary'], 'title', $eventId).'
                                        </a>
                                    '
                        )
                    .'
                    </div>
                '
    );
    $content = $content.(
        is_null($event['creator']['email'])
            ?
                ''
            :
                '
                    <div style="'.contentPartContainerStyle().'">
                        By '.getOrganizationBasedOnEmailAddress($event['creator']['email']).'
                    </div>
                '
    );
    $content = $content.(
        is_null($event['location'])
            ?
                ''
            :
                '
                    <div style="'.contentPartContainerStyle().'">
                        '.resolveEventContentValue($event['location'], 'location', $eventId).'
                    </div>
                '
    );
    $content = $content.(
        is_null($description)
            ?
                ''
            :
                '
                    <div style="'.contentPartContainerStyle().'">
                        '.resolveEventContentValue($description, 'description', $eventId).'
                    </div>
                '
    );
    $content = $content.(
        is_null($event['start']['dateTime'])
            ?
                ''
            :
                '
                    <div>'.composeEventStartAndEndTime($event).'</div>
                '
    );
    return $content;
}

function composeEventCategoryTags($eventCategories) {
    $numItems = count($eventCategories);
    $i = 0;

    $content = '<div style="'.eventCategoriesParentContainer().'">';
    foreach ($eventCategories as $eventCategory) {
        $content = $content.'<div style="'.eventCategoriesStyle($i === 0, $i === $numItems - 1).'">'.$eventCategory.'</div>';
        $i++;
    }
    $content = $content.'</div>';
    return $content;
}

function resolveEventContentValue($eventValue, $name, $eventId) {
    if (is_null($eventValue)) {
        return 'No '.$name.' specified';
    } else {
        $eventValue = trim($eventValue);
        $length = strlen($eventValue);
        if ($length > 100) {
            $shortVersionEventValue = str_starts_with($eventValue, '<')
                ?
                    resolveHtmlAsText($eventValue, 100)
                :
                    substr($eventValue, 0, 100);

            return '
                <div>
                    <label id="show_more_'.$eventId.'_'.$name.'_label_id">'.$shortVersionEventValue.'...</label>
                    <label id="show_less_'.$eventId.'_'.$name.'_label_id" style="display: none;">'.$eventValue.'</label>
                    <button id="show_more_less_'.$eventId.'_'.$name.'_btn_id" onclick="onShowMoreLessClicked(\''.$eventId.'\', \''.$name.'\');" style="'.showMoreLessBtnStyle('unset').'">Show more</button>
                </div>
            ';
        } else {
            return $eventValue;
        }
    }
}

function composeEventStartAndEndTime($event) {
   $eventStartTimeValue = resolveEventContentValue($event['start']['dateTime'], 'time', $event['id']);

   if ($eventStartTimeValue === 'No time specified') {
        return $eventStartTimeValue;
   } else {
        $startDateTime = new DateTime($event['start']['dateTime'], new DateTimeZone($event['start']['timeZone']));
        $endDateTime = new DateTime($event['end']['dateTime'], new DateTimeZone($event['end']['timeZone']));

        $startAndEndTimeText = $startDateTime->format('H:i');
        if (!is_null($endDateTime)) {
            $startAndEndTimeText = $startAndEndTimeText.' - '.$endDateTime->format('H:i');
        }

        return
            '<div style="'.clockContainerStyle().'">
                <img style="'.clockIconStyle().'" border="0" src="https://cdn.pixabay.com/photo/2017/06/26/00/46/flat-2442462_960_720.png">
                <div>'.$startAndEndTimeText.'</div>
            </div>';
   }
}

?>