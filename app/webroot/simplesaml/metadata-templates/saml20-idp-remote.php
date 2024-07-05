<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */

/*
 * Guest IdP. allows users to sign up and register. Great for testing!
 */
$metadata['https://openidp.feide.no'] = array(
	'name' => array(
		'en' => 'Feide OpenIdP - guest users',
		'no' => 'Feide Gjestebrukere',
	),
	'description'          => 'Here you can login with your account on Feide RnD OpenID. If you do not already have an account on this identity provider, you can create a new one by following the create new account link and follow the instructions.',

	'SingleSignOnService'  => 'https://openidp.feide.no/simplesaml/saml2/idp/SSOService.php',
	'SingleLogoutService'  => 'https://openidp.feide.no/simplesaml/saml2/idp/SingleLogoutService.php',
	'certFingerprint'      => 'c9ed4dfb07caf13fc21e0fec1572047eb8a7a4cb'
);


/*
 * Feide, the norwegian federation. Test and production metadata.
 */
$metadata['https://idp-test.feide.no'] = array(
	'name' => array(
		'en' => 'Feide Test environment',
		'no' => 'Feide testmiljø',
	),
	'description'                  => 'Feide test environment (idp-test.feide.no). Authenticate with your identity from a school or university in Norway.',

	'SingleSignOnService'          => 'https://idp-test.feide.no/simplesaml/saml2/idp/SSOService.php',
	'SingleLogoutService'          => 'https://idp-test.feide.no/simplesaml/saml2/idp/SingleLogoutService.php',

	'certFingerprint'              => 'fa982efdb69f26e8073c8f815a82a0c5885960a2',
	'hint.cidr'                    => '158.38.0.0/16',
);

$metadata['https://idp.feide.no'] = array(
	'name' => 'Feide',
	'description' => array(
		'en' => 'Authenticate with your identity from a school or university in Norway.',
		'no' => 'Logg inn med din identitet fra skolen eller universitetet du er tilknyttet (i Norge).',
	),
	'SingleSignOnService'          => 'https://idp.feide.no/simplesaml/saml2/idp/SSOService.php',
	'SingleLogoutService'          => 'https://idp.feide.no/simplesaml/saml2/idp/SingleLogoutService.php',
	'certFingerprint'              => 'cde69e332fa7dd0eaa99ee0ddf06916e8942ac53',
	'hint.cidr'                    => '158.38.0.0/16',
);



/*
 * Wayf, the danish federation metadata.
 */
$metadata['https://wayf.wayf.dk'] = array(
	'name' => array(
		'en' => 'DK-WAYF Production server',
		'da' => 'DK-WAYF Produktionsmiljøet',
	),
	'description'          => 'Login with your identity from a danish school, university or library.',
	'SingleSignOnService'  => 'https://wayf.wayf.dk/saml2/idp/SSOService.php',
	'SingleLogoutService'  => 'https://wayf.wayf.dk/saml2/idp/SingleLogoutService.php',
	'certFingerprint'      => 'c215d7bf9d51c7805055239f66b957d9a72ff44b'
);

$metadata['https://betawayf.wayf.dk'] = array(
	'name' => array(
		'en' => 'DK-WAYF Quality Assurance',
		'da' => 'DK-WAYF Quality Assurance miljøet',
	),
	'description'          => 'Login with your identity from a danish school, university or library.',
	'SingleSignOnService'  => 'https://betawayf.wayf.dk/saml2/idp/SSOService.php',
	'SingleLogoutService'  => 'https://betawayf.wayf.dk/saml2/idp/SingleLogoutService.php',
	'certFingerprint'      => 'c215d7bf9d51c7805055239f66b957d9a72ff44b'
);

$metadata['https://testidp.wayf.dk'] = array(
	'name' => array(
		'en' => 'DK-WAYF Test Server',
		'da' => 'DK-WAYF Test Miljøet',
	),
	'description'          => 'Login with your identity from a danish school, university or library.',
	'SingleSignOnService'  => 'https://testidp.wayf.dk/saml2/idp/SSOService.php',
	'SingleLogoutService'  => 'https://testidp.wayf.dk/saml2/idp/SingleLogoutService.php',
	'certFingerprint'      => '04b3b08bce004c27458b3e85b125273e67ef062b'
);

$metadata['https://sts.windows.net/aee59970-35c3-452a-8340-88feb049cb13/'] = array (
	'entityid' => 'https://sts.windows.net/aee59970-35c3-452a-8340-88feb049cb13/',
	'contacts' => 
	array (
	),
	'metadata-set' => 'saml20-idp-remote',
	'SingleSignOnService' => 
	array (
	  0 => 
	  array (
		'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
		'Location' => 'https://login.microsoftonline.com/aee59970-35c3-452a-8340-88feb049cb13/saml2',
	  ),
	  1 => 
	  array (
		'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
		'Location' => 'https://login.microsoftonline.com/aee59970-35c3-452a-8340-88feb049cb13/saml2',
	  ),
	),
	'SingleLogoutService' => 
	array (
	  0 => 
	  array (
		'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
		'Location' => 'https://login.microsoftonline.com/aee59970-35c3-452a-8340-88feb049cb13/saml2',
	  ),
	),
	'ArtifactResolutionService' => 
	array (
	),
	'keys' => 
	array (
	  0 => 
	  array (
		'encryption' => false,
		'signing' => true,
		'type' => 'X509Certificate',
		'X509Certificate' => 'MIIC8DCCAdigAwIBAgIQVgr3xR9MMKRMn5Z5CQEgoTANBgkqhkiG9w0BAQsFADA0MTIwMAYDVQQDEylNaWNyb3NvZnQgQXp1cmUgRmVkZXJhdGVkIFNTTyBDZXJ0aWZpY2F0ZTAeFw0yMzAzMDcwOTM3MTFaFw0yNjAzMDcwOTM3MTFaMDQxMjAwBgNVBAMTKU1pY3Jvc29mdCBBenVyZSBGZWRlcmF0ZWQgU1NPIENlcnRpZmljYXRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7UKZnd5RDVOjALqK8u3q46a7h9vZe4dlC9atyJL99K2CUZsQJpKurPd23YeRCF6BcY0eLXcvumR8u7GlNwiGIEneWDcHjPf1QVkOIJXQMHYCgv2DGDtMjdA1HllSLE9UCgcQSEAE1oTSSrLm2N9/41Ww7i4uTOBjiM1ag0qRb6nwmNHnMJebRzF1vtx1OtFon9q+0mqTW9VmGiqtauPYTNCd3rnYTdeAiQW8bm7p5lDsop+tkwWESSl8ziDwylSYnVf78BdEOkjFvmnqqjPwyADhMbbIVG7KIfvTEyZwMpzUQc8ygrGX8mnnJCVpS+3rtcCsnwZDB2mrKZ7+rNFCYQIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQB+remf7f6qETf8Wpw/8nsB1UJ7DQtuL+tEK0Lea7/BkxiU3+6BL22CyoekuiqMg3vfhdwmvqBgXE5WvtQEhxyTfcrIEJJ0qRw1S3YPqNOQ1tOyV9iM0hNlq3OU8Fi+Rid1WnoKrMqr6r5lz0ijSPnvqHTfAlvwL0v083iqQP+RyFt0YU91NHe28fbxewCU4x9ZQNxcuUtE3JZ2ZmyHOW3EmdlJqCWo0RkZ4pPdnY9QdtiOB+Xc4SZTGTtN+UGRBwSNOmOACj00tP0zX+aeB1xJyxi8TgZ208hFCt+32iCDuK09OHtaZZ6vTNZqiYWKXU8bI/xq55ERBqkBgkDSCPcN',
	  ),
	),
  );
