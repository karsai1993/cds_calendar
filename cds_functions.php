<?php

function fetchFunctions() {
    return
        '
            function onPreviousClicked(url) {
                let storedTokensAsString = sessionStorage.getItem(\'cds_navigation_tokens\');
                if (!storedTokensAsString) {
                    alert(\'Inconsistency occurred during filtering! We will reload the page for you so that you could continue/try again.\');

                    window.open(url, \'_self\');
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

                    window.open(url + \'?p=\' + previousToken, \'_self\');
                }
            }

            function onNextPageClicked(pageToken, url) {
                let storedTokensAsString = sessionStorage.getItem(\'cds_navigation_tokens\');
                let storedTokens;

                if (!storedTokensAsString) {
                    storedTokens = [];
                } else {
                    storedTokens = JSON.parse(storedTokensAsString);
                }

                storedTokens.push(pageToken);
                sessionStorage.setItem(\'cds_navigation_tokens\', JSON.stringify(storedTokens));

                window.open(url, \'_self\');
            }

            function onShowMoreLessClicked(eventId, name) {
                let show_more_label = document.getElementById(`show_more_${eventId}_${name}_label_id`);
                let show_less_label = document.getElementById(`show_less_${eventId}_${name}_label_id`);
                let btn = document.getElementById(`show_more_less_${eventId}_${name}_btn_id`);

                if ((!show_more_label && !show_less_label) || !btn) {
                    return;
                }

                if (show_less_label?.style?.display === \'none\') {
                    show_less_label.style.display = \'inline\';
                    btn.innerHTML = \'Show less\';
                    btn.style.width = \'100%\';
                    show_more_label.style.display = \'none\';
                } else {
                    show_less_label.style.display = \'none\';
                    btn.innerHTML = \'Show more\';
                    btn.style.width = \'unset\';
                    show_more_label.style.display = \'inline\';
                }
            }
        ';
}

?>