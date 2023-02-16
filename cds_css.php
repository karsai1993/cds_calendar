<?php

function showMoreLessBtnStyle() {
    return
        'padding: 0px;
         background-color: unset;
         color: unset;
         text-decoration: underline;
         font-family: inherit;
         font-size: 12px;
        ';
}

function noEventsFoundStyle() {
    return
        '
         text-align: center;
         font-size: 20px;
         font-weight: bold;
        ';
}

function showSearchOutputHeaderContainerStyle() {
    return
        '
         padding: 20px;
         border-right: 1px solid;
         text-align: center;
        ';
}

function showSearchOutputValueContainerStyle() {
    return
        '
         font-weight: bold;
         font-style: italic;
         font-size: 18px;
         flex: 1;
         text-align: center;
         margin-left: 10px;
         margin-right: 10px;
        ';
}

function showSearchContainerStyle() {
    return
        '
         display: flex;
         align-items: center;
         border: 1px solid;
         border-radius: 20px;
         margin-bottom: 10px;
        ';
}

function showSearchOutputContainerStyle() {
    return
        '
         flex: 1;
         display: flex;
         align-items: center;
        ';
}

function searchContainerStyle() {
    return
        'display: flex;
         align-items: center;
         justify-content: center;
         margin-bottom: 10px;
        ';
}

function searchInputContainerStyle() {
    return
        '
         flex: 1;
         border-radius: 10px;
         padding: 5px 10px;
         border: 1px solid black;
         margin-right: 10px;
        ';
}

function searchLoadingContainer() {
    return
        '
         text-align: center;
         font-size: 18px;
         font-style: italic;
        ';
}

function navigationBtnContainerStyle() {
    return
        'display: flex;
         align-items: center;
         justify-content: center;
        ';
}

function pageNavigationBtnStyle($shouldBeHidden) {
    return
        '
         width: 140px;
         border-radius: 10px;
         text-align: center;
         padding: 10px;
         margin: 0px 5px;
         '.($shouldBeHidden ? 'display: none;' : '').'
        ';
}

function searchBtnStyle($marginRight) {
    return
        '
         border-radius: 10px;
         text-align: center;
         padding: 10px;
         margin-right: '.$marginRight.'px;
        ';
}

function navigationBtnPlaceholderStyle($shouldBeHidden) {
    return
        'width: 1px;
         height: 1px;
         '.($shouldBeHidden ? 'display: none;' : '').'
        ';
}

function mainContainerDividerStyle() {
    return
        'border-bottom: 1px solid black;
         padding-bottom: 5px;
         padding-top: 5px;';
}

function mainContainerStyle() {
    return
        'display: flex;
         align-items: center;
         width: 100%;';
}

function startDateContainerStyle() {
    return
        'width: 60px;';
}

function contentContainerStyle() {
    return
        'flex: 1;
         margin-left: 10px;
         margin-right: 10px;';
}

function extraContainerStyle() {
    return
        'display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         cursor: pointer;';
}

function calendarIconStyle() {
    return
        'height: 20px;
         width: 20px;';
}

function calendarIconTextStyle() {
    return
        'font-size: 10px;
         width: 60px;
         text-align: center;';
}

function startDateWeekDayStyle() {
    return
        '
         font-size: 20px;
         font-weight: bold;
         height: 20px;
         text-align: right;
         ';
}

function startDateMonthStyle() {
    return
        'font-size: 24px;
         height: 20px;
         text-align: right;';
}

function startDateDayStyle() {
    return
        'font-size: 30px;
         font-weight: bold;
         height: 35px;
         text-align: right;';
}

function startDateYearStyle() {
    return
        'font-size: 20px;
         text-align: right;';
}

function eventTitleStyle() {
    return
        'font-weight: bold;';
}

function eventTypesParentContainer() {
    return
        'display: flex;
         align-items: center;
        ';
}

function eventTypeStyle($isFirstItem, $isLastItem) {
    return
        'font-size: 10px;
         background-color: grey;
         color: white;
         padding: 2px 5px;
         '.(!$isFirstItem ? 'margin-left: 5px;' : '').'
         '.(!$isLastItem ? 'margin-right: 5px;' : '').'
         border-radius: 5px;
        ';
}

function eventTitleLinkStyle() {
    return
        'color: black;';
}

function contentPartContainerStyle() {
    return
        'word-break: break-word;';
}

function clockContainerStyle() {
    return
        'display: flex;
         align-items: center;';
}

function clockIconStyle() {
    return
        'height: 20px;
         width: 20px;
         margin-right: 5px;';
}

?>