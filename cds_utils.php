<?php

$lineBreakUrlEncoded = urlencode('<br>');
$equalsUrlEncoded = urlencode('=');

function resolveHtmlAsText($text, $length, $startPoint = 0) {
    $text = html_entity_decode(htmlspecialchars_decode($text));
    $text = strip_tags($text, '');
    return $text = substr($text, $startPoint, $length);
}

function removeTrailingHtmlLineBreaks($text) {
    global $lineBreakUrlEncoded;
    return urldecode(trim(urlencode($text), $lineBreakUrlEncoded));
}

function resolveDescriptionExtraInfo($extraInfo, $key) {
    global $lineBreakUrlEncoded;
    $extraInfoParts = explode($lineBreakUrlEncoded, urlencode($extraInfo));

    foreach ($extraInfoParts as $extraInfoPart) {
        if (str_starts_with($extraInfoPart, $key)) {
            global $equalsUrlEncoded;
            $separatorIndex = strpos($extraInfoPart, $equalsUrlEncoded);
            return urldecode(substr($extraInfoPart, $separatorIndex + strlen($equalsUrlEncoded)));
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
        preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $titleUrlExtraInfo, $matches);
        $matchedValues = $matches[2];
        $descriptionData['eventTitleUrl'] = empty($matchedValues) ? $titleUrlExtraInfo : $matchedValues[0];
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