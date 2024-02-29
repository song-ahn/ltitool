<?php
// LtiDeepLink.php
// ===============
// Modifed IMS\Global\LTI_Deep_Link class functions to fix php-jwt 
// related errors
//
//  AUTHOR: Song Ho Ahn (song.ahn@gmail.com)
// CREATED: 2024-02-21
// UPDATED: 2024-02-28
///////////////////////////////////////////////////////////////////////////////

namespace IMSGlobal\LTI;

use Firebase\JWT\JWT;

class LtiDeepLink
{
    private $registration;
    private $deployment_id;
    private $deep_link_settings;

    public function __construct($registration,
                                $deployment_id,
                                $deep_link_settings)
    {
        $this->registration = $registration;
        $this->deployment_id = $deployment_id;
        $this->deep_link_settings = $deep_link_settings;
    }

    public function get_response_jwt($resources)
    {
        // JWT payload
        $message_jwt = [
            "iss" => $this->registration->get_client_id(),
            //@@"aud" => [$this->registration->get_issuer()],
            "aud" => $this->registration->get_issuer(),
            "exp" => time() + 600,
            "iat" => time(),
            "nonce" => 'nonce' . hash('sha256', random_bytes(64)),
            "https://purl.imsglobal.org/spec/lti/claim/deployment_id" => $this->deployment_id,
            "https://purl.imsglobal.org/spec/lti/claim/message_type" => "LtiDeepLinkingResponse",
            "https://purl.imsglobal.org/spec/lti/claim/version" => "1.3.0",
            "https://purl.imsglobal.org/spec/lti-dl/claim/content_items" => array_map(function($resource) { return $resource->to_array(); }, $resources),
            "https://purl.imsglobal.org/spec/lti-dl/claim/data" => $this->deep_link_settings['data'],
        ];
        return JWT::encode($message_jwt, $this->registration->get_tool_private_key(), 'RS256', $this->registration->get_kid());
    }

    public function output_response_form($resources)
    {
        $jwt = $this->get_response_jwt($resources);
        ?>
        <h1>Deep Link Form</h1>
        <form
            id="auto_submit"
            action="<?= $this->deep_link_settings['deep_link_return_url']; ?>"
            method="POST">
            <input type="hidden" name="jwt" value="<?= $jwt ?>" />
            <input type="submit" value="Go" />
        </form>
        <script>
            //document.getElementById('auto_submit').submit();
        </script>
        <?php
    }
}
/* from TAO LtiMessage.php
    public function toHtmlRedirectForm(bool $autoSubmit = true): string
    {
        $formInputs = [];
        $parameters = array_filter($this->getParameters()->all());
        $formId = sprintf('launch_%s', md5($this->url . implode('-', $parameters)));

        foreach ($parameters as $name => $value) {
            $formInputs[] = sprintf('<input type="hidden" name="%s" value="%s"/>', $name, $value);
        }

        $autoSubmitScript = sprintf(
            '<script>window.onload=function(){document.getElementById("%s").submit()}</script>',
            $formId
        );

        return sprintf(
            '<form id="%s" action="%s" method="POST">%s</form>%s',
            $formId,
            $this->url,
            implode('', $formInputs),
            $autoSubmit ? $autoSubmitScript : ''
        );
    }

*/
?>