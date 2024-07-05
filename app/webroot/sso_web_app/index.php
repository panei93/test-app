<?php

require_once('_include.php');


// SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('core/frontpage_welcome.php'));
if(in_array('sso_web_app', explode('/', $_SERVER['REQUEST_URI'])) || in_array('simplesaml', explode('/', $_SERVER['REQUEST_URI']))){
    SimpleSAML_Utilities::redirect(SSOInfo::entity_ID.'/NotFound');
}
SimpleSAML_Utilities::redirect(SSOInfo::entity_ID.'/NotFound');
?>


