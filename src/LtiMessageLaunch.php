<?php
// LtiMessageLaunch.php
// ====================
// Modifed IMS\Global\LTI_Message_Launch class functions to fix php-jwt 
// related errors
//
//  AUTHOR: Song Ho Ahn (song.ahn@gmail.com)
// CREATED: 2024-02-16
// UPDATED: 2024-02-21
///////////////////////////////////////////////////////////////////////////////

namespace IMSGlobal\LTI;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;   //@@

JWT::$leeway = 5;

class LtiMessageLaunch
{
    private $db;
    private $cache;
    private $request;
    private $cookie;
    private $jwt;
    private $registration;
    private $launch_id;

    function __construct(Database $database, Cache $cache = null, Cookie $cookie = null)
    {
        $this->db = $database;

        $this->launch_id = uniqid("lti1p3_launch_", true);

        if ($cache === null) {
            $cache = new Cache();
        }
        $this->cache = $cache;

        if ($cookie === null) {
            $cookie = new Cookie();
        }
        $this->cookie = $cookie;
    }

    public static function new(Database $database,
                               Cache $cache = null,
                               Cookie $cookie = null)
    {
        return new LtiMessageLaunch($database, $cache, $cookie);
    }

    public static function from_cache($launch_id, Database $database, Cache $cache = null)
    {
        $new = new LtiMessageLaunch($database, $cache, null);
        $new->launch_id = $launch_id;
        $new->jwt = [ 'body' => $new->cache->get_launch_data($launch_id) ];
        return $new->validate_registration();
    }

    public function has_nrps() {
        return !empty($this->jwt['body']['https://purl.imsglobal.org/spec/lti-nrps/claim/namesroleservice']['context_memberships_url']);
    }

    public function get_nrps() {
        return new LTI_Names_Roles_Provisioning_Service(
            new LTI_Service_Connector($this->registration),
            $this->jwt['body']['https://purl.imsglobal.org/spec/lti-nrps/claim/namesroleservice']);
    }

    public function has_gs() {
        return !empty($this->jwt['body']['https://purl.imsglobal.org/spec/lti-gs/claim/groupsservice']['context_groups_url']);
    }

    public function get_gs() {
        return new LTI_Course_Groups_Service(
            new LTI_Service_Connector($this->registration),
            $this->jwt['body']['https://purl.imsglobal.org/spec/lti-gs/claim/groupsservice']);
    }

    public function has_ags() {
        return !empty($this->jwt['body']['https://purl.imsglobal.org/spec/lti-ags/claim/endpoint']);
    }

    public function get_ags() {
        return new LTI_Assignments_Grades_Service(
            new LTI_Service_Connector($this->registration),
            $this->jwt['body']['https://purl.imsglobal.org/spec/lti-ags/claim/endpoint']);
    }

    public function get_deep_link() {
        //@@SONG:
        return new LtiDeepLink(
            $this->registration,
            $this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/deployment_id'],
            $this->jwt['body']['https://purl.imsglobal.org/spec/lti-dl/claim/deep_linking_settings']);
    }

    public function is_deep_link_launch() {
        return $this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/message_type'] === 'LtiDeepLinkingRequest';
    }

    public function is_submission_review_launch() {
        return $this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/message_type'] === 'LtiSubmissionReviewRequest';
    }

    public function is_resource_launch() {
        return $this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/message_type'] === 'LtiResourceLinkRequest';
    }

    public function get_launch_data() {
        return $this->jwt['body'];
    }

    public function get_launch_id() {
        return $this->launch_id;
    }

    public function validate(array $request = null)
    {
        if ($request === null) {
            $request = $_POST;
        }
        $this->request = $request;

        return $this->validate_state()
            ->validate_jwt_format()
            ->validate_nonce()
            ->validate_registration()
            ->validate_jwt_signature()
            ->validate_deployment()
            ->validate_message()
            ->cache_launch_data();
    }

    private function cache_launch_data() {
        $this->cache->cache_launch_data($this->launch_id, $this->jwt['body']);
        return $this;
    }

    private function get_public_key()
    {
        $key_set_url = $this->registration->get_key_set_url();

        // Download key set
        $public_key_set = json_decode(file_get_contents($key_set_url), true);

        if (empty($public_key_set)) {
            // Failed to fetch public keyset from URL.
            throw new LTI_Exception("Failed to fetch public key", 1);
        }

        // Find key used to sign the JWT (matches the KID in the header)
        foreach ($public_key_set['keys'] as $key) {
            if ($key['kid'] == $this->jwt['header']['kid']) {
                try {
                    //@@SONG: get public key as OpenSSLAsymmetricKey obj
                    $defaultAlg = "RS256";
                    $publicKey = JWK::parseKey($key, $defaultAlg)->getKeyMaterial();
                    //@@SONG: return public key detail array (bits, key, type...)
                    return openssl_pkey_get_details($publicKey);
                } catch(\Exception $e) {
                    return false;
                }
            }
        }

        // Could not find public key with a matching kid and alg.
        throw new LTI_Exception("Unable to find public key", 1);
    }

    private function validate_state()
    {
        // Check State for OIDC.
        if ($this->cookie->get_cookie('lti1p3_' . $this->request['state']) !== $this->request['state']) {
            // Error if state doesn't match
            throw new LTI_Exception("State not found", 1);
        }
        return $this;
    }

    private function validate_jwt_format()
    {
        $jwt = $this->request['id_token'];

        if (empty($jwt)) {
            throw new LTI_Exception("Missing id_token", 1);
        }

        // Get parts of JWT.
        $jwt_parts = explode('.', $jwt);

        if (count($jwt_parts) !== 3) {
            // Invalid number of parts in JWT.
            throw new LTI_Exception("Invalid id_token, JWT must contain 3 parts", 1);
        }

        // Decode JWT headers.
        $this->jwt['header'] = json_decode(JWT::urlsafeB64Decode($jwt_parts[0]), true);
        // Decode JWT Body.
        $this->jwt['body'] = json_decode(JWT::urlsafeB64Decode($jwt_parts[1]), true);

        return $this;
    }

    private function validate_nonce()
    {
        if (!$this->cache->check_nonce($this->jwt['body']['nonce'])) {
            //throw new LTI_Exception("Invalid Nonce");
        }
        return $this;
    }

    private function validate_registration()
    {
        // Find registration.
        $this->registration = $this->db->find_registration_by_issuer($this->jwt['body']['iss']);

        if (empty($this->registration)) {
            throw new LTI_Exception("Registration not found.", 1);
        }

        // Check client id.
        $client_id = is_array($this->jwt['body']['aud']) ? $this->jwt['body']['aud'][0] : $this->jwt['body']['aud'];
        if ( $client_id !== $this->registration->get_client_id()) {
            // Client not registered.
            throw new LTI_Exception("Client id not registered for this issuer", 1);
        }

        return $this;
    }

    private function validate_jwt_signature()
    {
        // Fetch public key.
        $public_key = $this->get_public_key();

        // Validate JWT signature
        try {
            //@@SONG: use Key() with RS256 to decode JWT
            JWT::decode($this->request["id_token"], new Key($public_key["key"], "RS256"));
        } catch(\Exception $e) {
            echo "jwt_signature error";
            var_dump($e);
            // Error validating signature.
            throw new LTI_Exception("Invalid signature on id_token", 1);
        }

        return $this;
    }

    private function validate_deployment()
    {
        // Find deployment.
        $deployment = $this->db->find_deployment($this->jwt['body']['iss'], $this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/deployment_id']);

        if (empty($deployment)) {
            // deployment not recognized.
            throw new LTI_Exception("Unable to find deployment", 1);
        }

        return $this;
    }

    private function validate_message()
    {
        if (empty($this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/message_type'])) {
            // Unable to identify message type.
            throw new LTI_Exception("Invalid message type", 1);
        }

        // Do message type validation

        // Import all validators
        //@@SONG change to vendor subdir
        //foreach (glob(__DIR__ . "/message_validators/*.php") as $filename) {
        foreach (glob(__DIR__ . "/../vendor/imsglobal/lti-1p3-tool/src/lti/message_validators/*.php") as $filename) {
            include_once $filename;
        }

        // Create instances of all validators
        $classes = get_declared_classes();
        $validators = array();
        foreach ($classes as $class_name) {
            // Check the class implements message validator
            $reflect = new \ReflectionClass($class_name);
            if ($reflect->implementsInterface('\IMSGlobal\LTI\Message_Validator')) {
                // Create instance of class
                $validators[] = new $class_name();
            }
        }

        $message_validator = false;
        foreach ($validators as $validator) {
            if ($validator->can_validate($this->jwt['body'])) {
                if ($message_validator !== false) {
                    // Can't have more than one validator apply at a time.
                    throw new LTI_Exception("Validator conflict", 1);
                }
                $message_validator = $validator;
            }
        }

        if ($message_validator === false) {
            throw new LTI_Exception("Unrecognized message type.", 1);
        }

        if (!$message_validator->validate($this->jwt['body'])) {
            throw new LTI_Exception("Message validation failed.", 1);
        }

        return $this;

    }

}
?>