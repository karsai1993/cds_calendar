<?php
/**
* Plugin Name: Chalmers Dance Society Shortcode Plugin
* Description: This plugin create a shortcode that can be utilised for creating the calendar.
* Version: 1.0
* Author: CDS Admin
**/

require 'cds_css.php';

//$baseRequestUri = "student-calendar-php/";

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
            $requestUrl = $requestUrl.'&q='.$queryParams['q'];
        } else if (!is_null($queryParams['p'])) {
            $requestUrl = $requestUrl.'&maxResults=5&pageToken='.$queryParams['p'];
        } else {
            $requestUrl = $requestUrl.'&maxResults=5';
        }
    } else {
        $requestUrl = $requestUrl.'&maxResults=5';
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
    $pageToken = $eventsResult['nextPageToken'];

    $numItems = count($events);
    $i = 0;
    // TODO: refactor pagination
    $content = '
        <div
            style="'.navigationNextBtnStyle(is_null($pageToken)).'"
            onclick="window.open(\''.home_url(strtok($_SERVER["REQUEST_URI"], '?')).'?p='.urlencode($pageToken).'\', \'_self\');"
        >
            Next
        </div>';
    $content = $content.'<div>';
    foreach ($events as $event) {
        $content = $content.convertEventToHtml($event, ++$i === $numItems);
    }
    $content = $content.'</div>';

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
        is_null($event['description'])
            ?
                ''
            :
                '
                    <div style="'.contentPartContainerStyle().'">
                        '.resolveEventContentValue($event['description'], 'description').'
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