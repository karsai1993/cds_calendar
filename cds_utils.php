<?php

$lineBreakUrlEncoded = urlencode('<br>');

function removeTrailingHtmlLineBreaks($text) {
    global $lineBreakUrlEncoded;
    return urldecode(trim(urlencode($text), $lineBreakUrlEncoded));
}

function resolveDescriptionExtraInfo($extraInfo, $key) {
    global $lineBreakUrlEncoded;
    $extraInfoParts = explode($lineBreakUrlEncoded, urlencode($extraInfo));

    foreach ($extraInfoParts as $extraInfoPart) {
        $decodedExtraInfoPart = urldecode($extraInfoPart);
        if (str_starts_with($decodedExtraInfoPart, $key)) {
            $separatorIndex = strpos($decodedExtraInfoPart, '=');
            return substr($decodedExtraInfoPart, $separatorIndex + 1);
        }
    }

    return null;
}

function resolveDescriptionData($originalDescription) {
    if (is_null($originalDescription)) {
        return null;
    }

    $descriptionData = null;

    $originalDescriptionSeparator = 'EXTRA INFORMATION';
    $separatorIndex = strpos($originalDescription, $originalDescriptionSeparator);

    if($separatorIndex === false){
        $descriptionData['description'] = $originalDescription;
    } else {
        $description = removeTrailingHtmlLineBreaks(substr($originalDescription, 0, $separatorIndex));
        $descriptionData['description'] = $description;

        $extraInfo = removeTrailingHtmlLineBreaks(
            substr($originalDescription, $separatorIndex + strlen($originalDescriptionSeparator))
        );

        $categoriesExtraInfo = resolveDescriptionExtraInfo($extraInfo, 'categories');
        $descriptionData['eventCategories'] = explode(',', $categoriesExtraInfo);

        $titleUrlExtraInfo = resolveDescriptionExtraInfo($extraInfo, 'title_url');
        $descriptionData['eventTitleUrl'] = str_starts_with($titleUrlExtraInfo, '<a')
            ?
                (string)(new SimpleXMLElement($titleUrlExtraInfo))['href']
            :
                $titleUrlExtraInfo;
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