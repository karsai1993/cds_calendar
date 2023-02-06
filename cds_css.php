<?php

function searchContainerStyle() {
    return
        'display: flex;
         align-items: center;
         justify-content: center;
        ';
}

function searchInputContainerStyle() {
    return
        'width: 100%;
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

function btnStyle($shouldBeHidden) {
    return
        'width: 70px;
         padding: 5px;
         border: 1px solid black;
         border-radius: 5px;
         cursor: pointer;
         text-align: center;
         margin: 0px 10px;
         '.($shouldBeHidden ? 'display: none;' : '').'
        ';
}

function searchBtnStyle() {
    return
        'width: 80px;
         border-radius: 10px;
         text-align: center;
         padding: 10px;
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