<?php

function resolveDescriptionData($description) {
    if (is_null($description)) {
        return null;
    }

    $descriptionData = null;

    $pattern = '(#+[a-zA-Z0-9(_)]{1,})';
    if (preg_match_all($pattern, $description, $matches)) {
        $descriptionData['eventCategories'] = $matches[0];
    }

    return $descriptionData;
}

function composeOrganizationEmailAddressesMap() {
    return array(
        'Laszlo' => array('karsai1993@gmail.com'),
        'Chalmers Dance Society' => array('frank@dance.chs.chalmers.se')
    );
}

function getOrganizationBasedOnEmailAddress($emailAddress) {
    $organizationEmailAddressesMap = composeOrganizationEmailAddressesMap();
    foreach($organizationEmailAddressesMap as $organization => $emailAddresses) {
      if (in_array($emailAddress, $emailAddresses)) {
        return $organization;
      }
    }

    return $emailAddress;
}

?>