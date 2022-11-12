<?php
/**
* Plugin Name: Chalmers Dance Society Shortcode Plugin
* Description: This plugin create a shortcode that can be utilised for creating the calendar.
* Version: 1.0
* Author: CDS Admin
**/

require 'cds_css.php';

add_shortcode('cds_calendar', 'resolveCdsCalendar');

function resolveCdsCalendar($original_attributes) {
    $attributes = getActualAttributes($original_attributes);

	$calendarId = $attributes['calendar_id'];
	$apiKey = $attributes['api_key'];

	$eventsResult = loadEvents($calendarId, $apiKey);

	return convertEventsToHtml($eventsResult['items']);
}

function getActualAttributes($original_attributes) {
	$default = array(
        'calendar_id' => 'undefined',
        'api_key' => 'undefined'
    );
    return shortcode_atts($default, $original_attributes);
}

function loadEvents($calendarId, $apiKey) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/calendar/v3/calendars/'.$calendarId.'/events?key='.$apiKey.'&singleEvents=true&orderBy=startTime');
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);

	if (curl_errno($ch)) {
	    echo curl_error($ch);
	} else {
	  $decodedResult = json_decode($result, true);
	}

	curl_close($ch);

	return $decodedResult;
}

function convertEventsToHtml($events) {
    $numItems = count($events);
    $i = 0;
    $content = '<div>';
    foreach ($events as $event) {
        $content = $content.convertEventToHtml($event, ++$i === $numItems);
    }
    $content = $content.'</div>';

    // TODO: delete when no need for logging
    $content = $content.'<div>'.$consoleLog.'</div>';
    // TODO: delete when no need for events response
    //$content = $content.'<div>'.json_encode($events).'</div>';

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
    return
        '
        <div style="'.eventTitleStyle().contentPartContainerStyle().'">
            <a style="'.eventTitleLinkStyle().'" href="'.$event['htmlLink'].'" target="_blank">
                '.resolveEventContentValue($event['summary'], 'title').'
            </a>
        </div>
        <div style="'.contentPartContainerStyle().'">'.resolveEventContentValue($event['location'], 'location').'</div>
        <div style="'.contentPartContainerStyle().'">'.resolveEventContentValue($event['description'], 'description').'</div>
        <div>'.composeEventStartTime($event).'</div>
        ';
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