<?php

function resolveEventTypes($eventValue) {
    if (is_null($eventValue)) {
        return null;
    }

    $pattern = '(#+[a-zA-Z0-9(_)]{1,})';

    if (preg_match_all($pattern, $eventValue, $matches)) {
        return $matches[0];
    } else {
        return null;
    }
}

?>