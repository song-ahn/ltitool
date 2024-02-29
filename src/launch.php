<?php
// launch.php
// ==========
// validate launch after OIDC login
//
//  AUTHOR: Song Ho Ahn (song.ahn@gmail.com)
// CREATED: 2024-02-14
// UPDATED: 2024-02-29
///////////////////////////////////////////////////////////////////////////

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/LtiDatabase.php";
require_once __DIR__ . "/LtiMessageLaunch.php";
require_once __DIR__ . "/LtiDeepLink.php";
require_once __DIR__ . "/utils.php";
use IMSGlobal\LTI;


try {

$config = getConfig(__DIR__ . "/cfg/config.json");
$dbName = __DIR__ . $config->database;
$launch = Lti\LtiMessageLaunch::new(new LtiDatabase($dbName));
$launchId = $launch->get_launch_id();
$launch->validate();
// for subsequent call
//$launch = LTI\LTI_Message_Launch::from_cache($launchId, new LtiDatabase());

} catch(Exception $e) {
    echo "<p><b>[ERROR]</b> Failed to validate LTI launch.</p>\n";
    flog("Failed to validate LTI launch.", "ERROR");
    exit;
} catch(Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "<br>";
}


// route to the target URL
$launchData = $launch->get_launch_data();
$targetUri = $launchData["https://purl.imsglobal.org/spec/lti/claim/target_link_uri"];
//echo "<b>target_link_uri</b> = " .$targetUri . "<br>";

if($launch->is_resource_launch())
{
    redirectByForm($targetUri);
    exit;
    echo "<h1>LTI Resource Launch</h1>";
    printArray($launchData);
}
else if($launch->is_deep_link_launch())
{
    $name = $launchData["name"];
    $email = $launchData["email"];

    $deeplink = $launch->get_deep_link();
    $resource = LTI\LTI_Deep_Link_Resource::new()
        ->set_title("EJD LTI")
        ->set_url($targetUri);
        //->set_custom_params(["name" => $name, "email" => $email]);
    $deeplink->output_response_form([$resource]);
    exit;

    echo "<h1>LTI Deep Link request</h1>";
    printArray($launchData);
    //@@ missing: https://purl.imsglobal.org/spec/lti/claim/context, 
    // https://purl.imsglobal.org/spec/lti/claim/lis,
    // https://purl.imsglobal.org/spec/lti/claim/launch_presentation,
    // http://www.brightspace.com,
    // https://purl.imsglobal.org/spec/lti/claim/tool_platform

    $respJwt = $deeplink->get_response_jwt([$resource]); // return string
    print_r($respJwt);
}
else
{
    echo "<h1>Unknown LTI Resource Lanch</h1>";
}



///////////////////////////////////////////////////////////////////////////////
// generate form with post params then submit the form
function redirectByForm($targetUri="/")
{
    echo "<p>Redirect to " . $targetUri . "...</p>";
    echo '<form id="auto_submit" action="' . $targetUri . '" method="POST">';
    // should pass id_token & state
    foreach($_REQUEST as $k => $v)
    {
        echo '<input type="hidden" name="' . $k . '" value="' . $v . '">';
        //echo '<input type="hidden" name="' . htmlentities($k) .
        //     '" value="' .htmlentities($v) . '">';
    }
    ?>
    <input type="submit" value="Redirect">
    </form>
    <script>
        document.getElementById("auto_submit").submit();
    </script>
    <?php
}









// DEBUG ======================================================================
echo "<h3>SUCCESS VALIDATE</h3>";
//$launchData = $launch->get_launch_data();
//echo "<p>Launch ID: " . $launchId . "</p>";
//printArray($launchData);
echo "<b>REQUEST</b><br>";
printArray($_REQUEST);


if($launch->has_ags())
{
    echo "<p>Has Assignments and Grades Service</p>";
    $ags = $launch->get_ags();
    print_r($ags);
    $grade = LTI\LTI_Grade::new()
        ->set_score_given($grade)
        ->set_score_maximum(100)
        ->set_timestamp(date(DateTime::ISO8601))
        ->set_activity_progress('Completed')
        ->set_grading_progress('FullyGraded')
        ->set_user_id($external_user_id);
    $ags->put_grade($grade);
        // for multiple
        //$lineitem = LTI\LTI_Lineitem::new()
        //    ->set_tag('grade')
        //    ->set_score_maximum(100)
        //    ->set_label('Grade');
        //$ags->put_grade($grade, $lineitem);
}
if($launch->has_nrps())
{
    echo "<p>Has Names and Roles Service</p>";
    $nrps = $launch->get_nrps();
    print_r($nrps);
    $members = $nrps->get_members();
    print_r($members);
}

?>
