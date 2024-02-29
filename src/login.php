<?php
// login.php
// =========
// handle OIDC login request for LTI
//
//  AUTHOR: Song Ho Ahn (song.ahn@gmail.com)
// CREATED: 2024-02-08
// UPDATED: 2024-02-29
///////////////////////////////////////////////////////////////////////////

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/LtiDatabase.php";
require_once __DIR__ . "/utils.php";
use IMSGlobal\LTI;

if($_SERVER["REQUEST_METHOD"] != "POST")
{
    echo "<p><b>[ERROR]</b> Incorrect request method.</p>\n";
    return;
}

// check params
if(!isset($_REQUEST["iss"]) ||
   !isset($_REQUEST["client_id"]) ||
   !isset($_REQUEST["lti_deployment_id"]) ||
   !isset($_REQUEST["login_hint"]) ||
   !isset($_REQUEST["target_link_uri"]) ||
   !isset($_REQUEST["lti_message_hint"]))
{
    echo "<p><b>[ERROR]</b> Missing required params.</p>\n";
    return;
}

try {
$config = getConfig(__DIR__ . "/cfg/config.json");
$dbName = __DIR__ . $config->database;
LTI\LTI_OIDC_Login::new(new LtiDatabase($dbName))
    ->do_oidc_login_redirect($config->redirectUrl)
    ->do_redirect();
} catch(LTI\OIDC_Exception $e) {
    echo "<p><b>[ERROR]</b>" . $e->getMessage() . "</p>";
    flog($e->getMessage(), "ERROR");
}
exit;

/*
// load config ini file with sections
$config = parse_ini_file("../config.ini.php", true);
//print_r($config);

// load public/private keys
$publicKey = file_get_contents("../" . $config["publicKey"]);
$privateKey = file_get_contents("../" . $config["privateKey"]);

// create registration
$reg = LTI\LTI_Registration::new()
        -> set_auth_login_url($config["slatedev"]["authUrl"])
        ->set_auth_token_url($config["slatedev"]["accessToken"])
        ->set_client_id($config["slatedev"]["clientId"])
        ->set_key_set_url($config["slatedev"]["keyset"])
        ->set_kid($config["uuid"])
        ->set_issuer($config["slatedev"]["issuer"])
        ->set_tool_private_key($privateKey);

// create deployment
$deploy = LTI\LTI_Deployment::new()->set_deployment_id($config["slatedev"]["deployId"]);

// handle OIDC login request
class LtiDatabase implements LTI\Database
{
    public $registration;
    public $deployment;
    function __construct($reg, $deploy)
    {
        $this->registration = $reg;
        $this->deployment = $deploy;
    }
    function find_registration_by_issuer($iss)
    {
        return $this->registration;
    }
    function find_deployment($iss, $deploymentId)
    {
        return $this->deployment;
    }
}
$database = new LtiDatabase($reg, $deploy);

$login = LTI\LTI_OIDC_Login::new($database);
$redirect = $login->do_oidc_login_redirect("https://ejd.songho.ca/ltitool/launch/");
$redirect->do_redirect();
exit;
*/


/*
//phpinfo();

// get request
$headers = apache_request_headers();
$request = $_REQUEST;
print_r($headers);
print_r($request);

if($_SERVER["REQUEST_METHOD"] != "POST")
{
    echo "Only accept POST requet.";
    return;
}

// check params
if(!isset($_REQUEST["iss"]) ||
   !isset($_REQUEST["client_id"]) ||
   !isset($_REQUEST["lti_deployment_id"]) ||
   !isset($_REQUEST["login_hint"]) ||
   !isset($_REQUEST["target_link_uri"]) ||
   !isset($_REQUEST["lti_message_hint"]))
{
    echo "Missing required params.";
    return;
}

$iss = $_REQUEST["iss"];
$clientId = $_REQUEST["client_id"];
$deployId = $_REQUEST["lti_deployment_id"];
$login = $_REQUEST["login_hint"];
$redirectUri = $_REQUEST["target_link_uri"];
$message = $_REQUEST["lti_message_hint"];

// generate nonce

// construct response
"client_id" => $_REQUEST["client_id"]
"login_hint" => $_REQUEST["login_hint"]
"redirect_uri" => $_REQUEST["target_link_uri"]
"nonce"
"state"
"lti_message_hint" => $_REQUEST["lti_message_hint"]
"scope" => "openid"
"response_type" => "id_token"
"response_mode" => "form_post"
"prompt" => "none"

// send back to platform
*/
?>
