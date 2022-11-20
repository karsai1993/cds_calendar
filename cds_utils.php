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

function composeOrganizationEmailAddressesMap() {
    return array(
        'Dance Society' => array('karsai1993@gmail.com'),
        'Sport Community' => array('jenei.kinga.s@gmail.com')
    );
}

function getOrganizationBasedOnEmailAddress($emailAddress) {
    $organizationEmailAddressesMap = composeOrganizationEmailAddressesMap();
    foreach($organizationEmailAddressesMap as $organization => $emailAddresses) {
      if (in_array($emailAddress, $emailAddresses)) {
        return $organization;
      }
    }

    return 'unknown organization';
}

?>