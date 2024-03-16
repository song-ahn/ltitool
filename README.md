Libs & Dependencies
-------------------
1. IMS lti-1-3-php-library
2. Firebase PHP-JWT
3. Composer

Infos for LTI Platform
----------------------
- Client ID: 
- Keyset URL: 
- OAuth2 Access Token URL: 
- OAuth2 Audience: 
- OIDC Authentication Edndpoint:
- Issuer:

Infos for LTI Tool
------------------
- Domain:
- Redirect URL: launch url
- OIDC Init: login url
- Target link URI:
- Keyset URL: jwks.json

SheridanH5P
-----------------------
- Domain: https://ltsa.sheridancollege.ca/sheridanh5p/
- Redirect URL: https://ltsa.sheridancollege.ca/apps/ltitool/launch
- OIDC Init: https://ltsa.sheridancollege.ca/apps/ltitool/login
- Target link URI:
- Keyset URL: https://ltsa.sheridancollege.ca/sheridanh5p/ltitool/ltsa_jwks.json

SLATE-DEV Registration
----------------------
- Client ID:
- Keyset URL: https://slatedev.sheridancollege.ca/d2l/.well-known/jwks
- OAuth2 Access Token URL: https://auth.brightspace.com/core/connect/token
- OIDC Authentication Endpoint: https://slatedev.sheridancollege.ca/d2l/lti/authenticate
- Brightspace OAuth Audience: https://api.brightspace.com/auth/token
- Issure: https://slatedev.sheridancollege.ca
- Development IDs:

To Generate UUID
----------------
    apt install uuid-runtime
    uuidgen

To generate keys
----------------
    openssl rand -base64
    ssh-keygen -t rsa -b 4096 -m PEM -f jwtRS256.key
    openssl rsa -in jwtRS256.key -pubout -outform PEM -out jwtRS256.key.pub
**NOTE**: Use private Key to encode(sign) JWT, and use public key to verify (decode) JWT

JWT Structure
-------------
- encoded with Base64URL
- 111.222.333
1. header: alg, type
2. payload: claim
3. signature: signed token by server
- JWKS: https://github.com/oat-sa/lib-lti1p3-core/blob/master/doc/quickstart/jwks.md

OAuth 2.0
---------
- authorization framework to share data between application
- OpenID Connect: Authenticate into multiple website
- Flow:
    1. Platform requests login to the tool
    2. Tool sends authorization request back to the platform
    3. Platform sends JWT


Handle Deep Link Request
------------------------
1. Validate launch request
2. Retrieve DL settings claims; https://purl.imsglobal.org/spec/lti-dl/claim/deep_linking_settings
    - deep_link_return_url_
    - accept_types_
    - accept_media_types
    - accept_presentation_document_targets
    - title, text, data, etc.
3. Generate DL response
4. Send the response as POST


SLATE-DEV JWT Payload
---------------------
- nbf
- exp
- iss = https://slatedev.sheridancollege.ca
- aud
- iat
- sub
- given_name
- family_name
- name
- email
- nonce = nonce-#####
- https://purl.imsglobal.org/spec/lti/claim/message_type = LtiResourceLinkRequest
- https://purl.imsglobal.org/spec/lti/claim/version = 1.3.0
- https://purl.imsglobal.org/spec/lti/claim/deployment_id
- https://purl.imsglobal.org/spec/lti/claim/target_link_uri
- https://purl.imsglobal.org/spec/lti/claim/resource_link = [ id, title, description ]
- https://purl.imsglobal.org/spec/lti/claim/roles = [...]
- https://purl.imsglobal.org/spec/lti/claim/context = [ id, label, title, [type] ]
- https://purl.imsglobal.org/spec/lti/claim/lis = [ course_offering_sourcedid, course_section_sourcedid, person_sourcedid ]
- https://purl.imsglobal.org/spec/lti/claim/launch_presentation = [ locale ]
- http://www.brightspace.com = [ tenant_id, org_defined_id, user_id, username, username, content_topic_id, Context.id.history, ResourceLink.id.history, link_id ]
- https://purl.imsglobal.org/spec/lti-ags/claim/endpoint = [ [scope], lineitem, lineitems ]
- https://purl.imsglobal.org/spec/lti/claim/tool_platform = [ guid, product_family_code=desire2learn ]

- https://purl.imsglobal.org/spec/lti-nrps/claim/namesroleservice = [ context_memberships_url, [service_versions] ]

- https://purl.imsglobal.org/spec/lti-dl/claim/deep_linking_settings = [ [accept_types], accept_media_types, [accept_presentation_document_targets], accept_multiple, auto_create, deep_link_return_url, data ] 


LtiDeepLinkingResponse message
------------------------------
- aud = Must be **iss** of LtiDeepLinkingRequest
- https://purl.imsglobal.org/spec/lti/claim/message_type
- https://purl.imsglobal.org/spec/lti/claim/version
- https://purl.imsglobal.org/spec/lti/claim/deployment_id
- https://purl.imsglobal.org/spec/lti-dl/claim/data
- https://purl.imsglobal.org/spec/lti-dl/claim/content_items (optional)
- https://purl.imsglobal.org/spec/lti-dl/claim/msg (optional)
- https://purl.imsglobal.org/spec/lti-dl/claim/log (optional)
- https://purl.imsglobal.org/spec/lti-dl/claim/errormsg (optional)
- https://purl.imsglobal.org/spec/lti-dl/claim/errorlog (optional)

LTI References
--------------
- [IMS github](https://github.com/1EdTech/lti-1-3-php-library)
- [IMS Youtube](https://youtu.be/fI-rhSSDU8M?feature=shared)
- [TAO github](https://oat-sa.github.io/doc-lti1p3/)
- [Firebase php-jwt](https://github.com/firebase/php-jwt)
- [OAuth2](https://blog.postman.com/what-is-oauth-2-0/)
- [D2L LTI authentication](https://community.d2l.com/brightspace/kb/articles/23730-about-lti-1-3-launch-and-authentication)
- [OAuth2 Illustration](https://developer.okta.com/blog/2019/10/21/illustrated-guide-to-oauth-and-oidc)
- [wordpress-lti](https://github.com/3iPunt/wordpress-lti-1-3)
- [Setup H5P + D2L](https://help.h5p.com/hc/en-us/articles/7506404914845-Setting-up-H5P-com-in-Brightspace-LTI-1-3)


D2L Reference for settings
--------------------------
- https://help.h5p.com/hc/en-us/articles/7506404914845-Setting-up-H5P-com-in-Brightspace-LTI-1-3-
- https://success.vitalsource.com/hc/en-us/articles/360051480074-D2L-Brightspace-LTI-1-3-Tool-Setup-and-Link-Placements
- https://learn.microsoft.com/en-us/linkedin/learning/sso-auth/sso-docs/sso-lti-13-d2l
- https://community.d2l.com/brightspace/kb/articles/23660-lti-advantage-v1-3
