<?php
// LtiDatabase.php
// ===============
// Database implementing IMSGlobal\LTI\Database class
//
//  AUTHOR: Song Ho Ahn (song.ahn@gmail.com)
// CREATED: 2024-02-15
// UPDATED: 2024-02-29
///////////////////////////////////////////////////////////////////////////////

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/utils.php";
use IMSGlobal\LTI;

session_start();
$_SESSION["lti"] = [];

class LtiDatabase implements LTI\Database
{
    private $db;
    private $privateKey;
    function __construct($dbName)
    {
        $config = getConfig(__DIR__ . "/cfg/config.json");
        if(!$dbName)
            $dbName = __DIR__ . $config->database;

        $this->db = json_decode(file_get_contents($dbName));
        if(!$this->db)
            flog("Failed to load database json.", "ERROR");
        $keyFile = __DIR__ . $config->privateKey;
        $this->privateKey = file_get_contents($keyFile);
        if(!$this->privateKey)
            flog("Failed to load private key.", "ERROR");
        //print_r($this->db[0]->deployments);
    }

    function find_registration_by_issuer($iss)
    {
        $data = $this->getRegistrationData($iss);
        if(!$data)
        {
            flog("Cannot find the issuer in database: " . $iss, "ERROR");
            return false;
        }
        //print_r($data);

        // set session data
        $_SESSION["lti"]["issuer"] = $data->issuer;
        $_SESSION["lti"]["clientId"] = $data->clientId;
        $_SESSION["lti"]["authUrl"] = $data->authUrl;
        $_SESSION["lti"]["accessToken"] = $data->accessToken;
        $_SESSION["lti"]["keysetUrl"] = $data->keysetUrl;
        $_SESSION["lti"]["audience"] = $data->audience;

        return LTI\LTI_Registration::new()
            ->set_issuer($data->issuer)
            ->set_client_id($data->clientId)
            ->set_auth_login_url($data->authUrl)
            ->set_auth_token_url($data->accessToken)
            ->set_key_set_url($data->keysetUrl)
            ->set_tool_private_key($this->privateKey);
    }

    function find_deployment($iss, $deploymentId)
    {
        $data = $this->getRegistrationData($iss);
        if(!$data)
        {
            flog("Cannot find the issuer in database: " . $iss, "ERROR");
            return false;
        }
        if(in_array($deploymentId, $data->deployments))
        {
            return LTI\LTI_Deployment::new()
                ->set_deployment_id($deploymentId);
        }
        else
        {
            flog("Cannot find the deployment ID for the issuer: " . $iss, "ERROR");
            return false;
        }
    }

    // return registration info from DB
    private function getRegistrationData($iss)
    {
        foreach($this->db as $data)
        {
            if($data->issuer == $iss)
                return $data;
        }
        return null;
    }

    private function getPrivateKey($iss)
    {
        return $this->privateKey;
    }
}

// debug
//$db = new LtiDatabase();
//$reg = $db->find_registration_by_issuer("https://slatedev.sheridancollege.ca");
//print_r($reg);
//$deploy = $db->find_deployment("https://slatedev.sheridancollege.ca", "aefdcc3d-1c64-4b27-8772-2a9e01b03dcf");
//print_r($deploy);

?>
