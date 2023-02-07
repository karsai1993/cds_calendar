<?php
/**
* Plugin Name: Chalmers Dance Society Shortcode Plugin
* Description: This plugin create a shortcode that can be utilised for creating the calendar.
* Version: 1.0
* Author: CDS Admin
**/

/*
* Questions:
* 1. Should we display the length of an event? E.g.: 1 hour, 2 days, etc
*/

require 'cds_css.php';
require 'cds_utils.php';

$page = null;
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

function loadEvents($calendarId, $apiKey, $queryString) {
	$ch = curl_init();

    $requestUrl = 'https://www.googleapis.com/calendar/v3/calendars/';
    $requestUrl = $requestUrl.$calendarId;
    $requestUrl = $requestUrl.'/events?key=';
    $requestUrl = $requestUrl.$apiKey;
    $requestUrl = $requestUrl.'&singleEvents=true&orderBy=startTime';

    if ($queryString !== '') {
        parse_str($queryString, $queryParams);
        if (!is_null($queryParams['q'])) {
            $requestUrl = $requestUrl.'&q='.urlencode($queryParams['q']);
            global $page;
            global $query;
            $page = null;
            $query = $queryParams['q'];
        } else if (!is_null($queryParams['p'])) {
            $requestUrl = $requestUrl.'&maxResults=5&pageToken='.urlencode($queryParams['p']);
            global $page;
            global $query;
            $page = $queryParams['p'];
            $query = null;
        } else {
            $requestUrl = $requestUrl.'&maxResults=5';
            global $page;
            global $query;
            $page = null;
            $query = null;
        }
    } else {
        $requestUrl = $requestUrl.'&maxResults=5';
        global $page;
        global $query;
        $page = null;
        $query = null;
    }

	curl_setopt($ch, CURLOPT_URL, $requestUrl);
	//.($queryString !== '' ? '&q='.$queryString : '')
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
                <button style="'.searchBtnStyle(20).'" onclick="window.open(\''.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'\', \'_self\');">Remove filter</button>
            </div>
        ';
    } else {
        $content = '
            <form action="" method="post" style="'.searchContainerStyle().'">
                <input style="'.searchInputContainerStyle().'" type="text" name="search_query" placeholder="Filter in all content">
                <button style="'.searchBtnStyle(0).'" name="search_apply">Apply filter</button>
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
    foreach ($events as $event) {
        $content = $content.convertEventToHtml($event, ++$i === $numItems);
    }
    $content = $content.'</div>';

    $pageToken = $eventsResult['nextPageToken'];

    global $page;
    $content = $content.'
        <div style="'.navigationBtnContainerStyle().'">
            <div
                style="'.btnStyle(is_null($page)).'"
                onclick="
                    function onPreviousClicked() {
                        let storedTokensAsString = sessionStorage.getItem(\'cds_navigation_tokens\');
                        if (!storedTokensAsString) {
                            alert(\'Inconsistency occurred during filtering! We will reload the page for you so that you could continue/try again.\');

                            window.open(\''.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'\', \'_self\');
                        } else {
                            let storedTokens = JSON.parse(storedTokensAsString);
                            let previousToken = \'\';
                            if (!!storedTokens.length) {
                                storedTokens.pop();
                                if (!!storedTokens.length) {
                                    previousToken = storedTokens.pop();
                                }
                            }
                            sessionStorage.setItem(\'cds_navigation_tokens\', JSON.stringify(storedTokens));

                            window.open(\''.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'?p=\' + previousToken, \'_self\');
                        }
                    };
                    onPreviousClicked();
                "
            >
                Previous
            </div>
            <div style="'.navigationBtnPlaceholderStyle(!is_null($page)).'"></div>
            <div
                style="'.btnStyle(is_null($pageToken)).'"
                onclick="
                    function onNextClicked() {
                        let storedTokensAsString = sessionStorage.getItem(\'cds_navigation_tokens\');
                        let storedTokens;

                        if (!storedTokensAsString) {
                            storedTokens = [];
                        } else {
                            storedTokens = JSON.parse(storedTokensAsString);
                        }

                        storedTokens.push(\''.$pageToken.'\');
                        sessionStorage.setItem(\'cds_navigation_tokens\', JSON.stringify(storedTokens));

                        window.open(
                            \''.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'?p='.urlencode($pageToken).'\',
                            \'_self\'
                        );
                    };
                    onNextClicked();
                "
            >
                Next
            </div>
        </div>
    ';

    // TODO: delete when no need for logging
    //global $baseRequestUri;
    //$content = $content.'<div>'.$baseRequestUri.'</div>';
    //$content = $content.'<pre>'.json_encode($_SERVER).'</pre>';
    //$content = $content.'<div>'.urldecode($_SERVER['QUERY_STRING']).'</div>';
    //$content = $content.'<div>'.urldecode(home_url( $_SERVER['REQUEST_URI'] )).'</div>';
    //$content = $content.'<div>'.urldecode(esc_url(home_url( $_SERVER['REQUEST_URI'] ))).'</div>';
    // TODO: delete when no need for events response
    //$content = $content.'<pre>'.json_encode($events).'</pre>';
    //$content = $content.'<pre>'.json_encode($eventsResult).'</pre>';

	return $content;
}

function convertEventToHtml($event, $isLastItem) {
    $mainContainerExtraStyle = $isLastItem ? '' : mainContainerDividerStyle();
    return
        '<div style="'.mainContainerStyle().$mainContainerExtraStyle.'">
            <div style="'.startDateContainerStyle().'">'.composeEventStartContainer($event).'</div>
            <div style="'.contentContainerStyle().'">'.composeContentContainer($event).'</div>
            <div>By '.getOrganizationBasedOnEmailAddress($event['creator']['email']).'</div>
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
    $formattedDate = date('M d Y', $timestamp);
    list($month, $day, $year) = explode(' ', $formattedDate);
    return
        '<div style="'.startDateMonthStyle().'">'.$month.'</div>
        <div style="'.startDateDayStyle().'">'.$day.'</div>
        <div style="'.startDateYearStyle().'">'.$year.'</div>';
}

function composeContentContainer($event) {
    $description = $event['description'];
    $eventTypes = resolveEventTypes($description);

    $content = $content.(
        is_null($eventTypes)
            ?
                ''
            :
                ''.composeEventTypeTags($eventTypes).''
    );
    $content = $content.(
        is_null($event['summary'])
            ?
                '
                    <div style="'.eventTitleStyle().'">No title specified</div>
                '
            :
                '
                    <div style="'.eventTitleStyle().contentPartContainerStyle().'">
                        <a style="'.eventTitleLinkStyle().'" href="'.$event['htmlLink'].'" target="_blank">
                            '.resolveEventContentValue($event['summary'], 'title').'
                        </a>
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
                        '.resolveEventContentValue($event['location'], 'location').'
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
                        '.resolveEventContentValue($description, 'description').'
                    </div>
                '
    );
    $content = $content.(
        is_null($event['start']['dateTime'])
            ?
                ''
            :
                '
                    <div>'.composeEventStartTime($event).'</div>
                '
    );
    return $content;
}

function composeEventTypeTags($eventTypes) {
    $numItems = count($eventTypes);
    $i = 0;

    $content = '<div style="'.eventTypesParentContainer().'">';
    foreach ($eventTypes as $eventType) {
        $content = $content.'<div style="'.eventTypeStyle($i === 0, $i === $numItems - 1).'">'.substr($eventType, 1).'</div>';
        $i++;
    }
    $content = $content.'</div>';
    return $content;
}

function resolveEventContentValue($eventValue, $name) {
    if (is_null($eventValue)) {
        return 'No '.$name.' specified';
    } else {
        $eventValue = trim($eventValue);
        $length = strlen($eventValue);
        if ($length > 100) {
            return substr($eventValue, 0, 100).'...';
        } else {
            return $eventValue;
        }
    }
}

function composeEventStartTime($event) {
   $eventStartTimeValue = resolveEventContentValue($event['start']['dateTime'], 'time');

   if ($eventStartTimeValue === 'No time specified') {
        return $eventStartTimeValue;
   } else {
        $timestamp = strtotime($event['start']['dateTime']);
        return
            '<div style="'.clockContainerStyle().'">
                <img style="'.clockIconStyle().'" border="0" src="https://cdn.pixabay.com/photo/2017/06/26/00/46/flat-2442462_960_720.png">
                <div>'.date('H:s', $timestamp).'</div>
            </div>';
   }
}

?>