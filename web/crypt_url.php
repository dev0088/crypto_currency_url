<?php

/**
 * FOLLOWING DEFINES CAN BE MODFIIED
 */

// End-user required data
define('CLIENT_PREF_LANG', 'fr_FR'); // Ex: fr_FR
define('CLIENT_BIRTHDATE', 1524002400); // Birthdate as timestamp
define('CLIENT_GENDER', 0); // 0 = MAN / 1 = WOMAN
define('CLIENT_SHORTNAME', 'abc'); // 3 letters
define('CLIENT_HEIGHT_VALUE', 185); // Height value as integer not float
define('CLIENT_HEIGHT_EXPONENT', -2); // 10^EXP to get height valu in meters
define('CLIENT_WEIGHT_VALUE', 7525); // Weight value as integer not float
define('CLIENT_WEIGHT_EXPONENT', -2); // 10^EXP to get weight valu in meters

// End-user optional data
define('OPTIONAL_CLIENT_FIRSTNAME', 'Théo'); // Optional
define('OPTIONAL_CLIENT_LASTNAME', 'Hudry'); // Optional
define('OPTIONAL_CLIENT_EMAIL', 'theo@agenceminuit.com'); // Optional
define('OPTIONAL_CLIENT_UNIT_PREFERENCES', '{"weight":1,"distance":8,"temperature":13,"height":7}'); // Optional

// Partner credentials
define('PARTNER_CLIENT_ID', 'eee545283019ba62b1a80b9ebc2f434017abdc94e0f4d70848344eb9eabfa81e');
define('PARTNER_CLIENT_SECRET', '204824aac946587cd055592d97dd22ace214e2e61a1b6135b1bd7b6e1e951e6a');
define('PARTNER_REDIRECT_URI', 'https://dev.avis2sante.net/joomla-dev-mrehab-2/?option=com_ajax&plugin=Withings_Callback&format=json'); // URL where Withings server will send the accesstoken and the refreshtoken with the end-user Withings userid

// Additional information
define('OPTIONAL_MODEL', 7); // Withings device model to be installed. If not provided, the end-user will be prompted to choose the model manually among available models.
define('EXTERNAL_ID', '1109'); // End-user identifier specified by partner so that partner can identify the end-user

// Withings base url
define('WITHINGS_URL_PREFIX', 'https://account.withings.com');

/**
 * CODE AFTER THIS LINE SHOULD NOT BE MODIFIED
 */

// Prepare height and weight measures
$measures = array(
    array(
        "value" => CLIENT_HEIGHT_VALUE,
        "unit"  => CLIENT_HEIGHT_EXPONENT,
        "type"  => 4, // Withings height type value
    ),
    array(
        "value" => CLIENT_WEIGHT_VALUE,
        "unit"  => CLIENT_WEIGHT_EXPONENT,
        "type"  => 1, // Withings weight type value
    )
);
$json_measures = json_encode($measures);

// Declare critical param tha will be encrypted with client secret
$encrypted_params = array(
    'cryptfirstname' => OPTIONAL_CLIENT_FIRSTNAME,
    'cryptlastname'  => OPTIONAL_CLIENT_LASTNAME,
    'cryptbirthdate' => CLIENT_BIRTHDATE,
    'cryptmeasures'  => $json_measures,
);
$params = array(
    'client_id'    => PARTNER_CLIENT_ID,
    'redirect_uri' => PARTNER_REDIRECT_URI,
    'shortname'    => CLIENT_SHORTNAME,
    'gender'       => CLIENT_GENDER,
    'preflang'     => CLIENT_PREF_LANG,
    'external_id'  => EXTERNAL_ID,
    'unit_pref'    => OPTIONAL_CLIENT_UNIT_PREFERENCES
);

// Encrypt critical params
$cipher_method = 'aes-256-ctr';
$iv            = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
$params['iv']  = $iv;
foreach ($encrypted_params as $key => $value) {
    $encrypted_value = openssl_encrypt($value, $cipher_method, PARTNER_CLIENT_SECRET, 0, $iv); // The 0 value is the 4th parameter default value
    $params[$key]    = $encrypted_value;
}

// Add signature hash
ksort($params);
$params_str          = implode(',', $params);
$signature_hash      = hash_hmac('sha256', $params_str, PARTNER_CLIENT_SECRET);
$params['signature'] = $signature_hash;

// Add params that must not be used for generating signature
$params['model'] = OPTIONAL_MODEL;
$params['email'] = OPTIONAL_CLIENT_EMAIL; // Optional

echo json_encode(WITHINGS_URL_PREFIX.'/sdk/sdk_init?'.http_build_query($params));
echo "\n\n";

?>
