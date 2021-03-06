<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * agm_zebra theme header
 *
 * Based on Zebra theme, modified for Agri-Moodle --tkout
 *
 * @package    theme_agm_zebra
 * @copyright  2012 Agro-Know Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$simplelogin = (($PAGE->theme->settings->simplelogin) && (strpos($PAGE->bodyclasses, 'path-login'))); //Check for simplelogin setting

$hasheading = ($PAGE->heading); //Check for the page title
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar() && !$simplelogin); //Check to see if this page-lay has a navbar
$hasfooter = (empty($PAGE->layout_options['nofooter']) && !$simplelogin); //Check to see if this page-layout has a footer
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']) && !$simplelogin); //Check if the login info (user name, etc...) is available in this page-layout
$haslangmenu = (!empty($PAGE->layout_options['langmenu']) && !$simplelogin); //Check if the language menu is available in this page-layout
$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));
$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu) && !$simplelogin); //Check if this page-layout has a custommenu and if it has content
$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
$canedit = has_capability('moodle/block:edit', $this->page->context); //See if the user is able to edit here
// print_object($USER);
if ($canedit && !empty($USER->editing)) { //If they can edit and they are editing add a class
//if ($canedit) { //If they can edit and they are editing add a class
    $bodyclasses[] = 'can_edit'; //Add a .can_edit class to editing mode
}
if ($simplelogin) {
    $bodyclasses[] = 'pagelayout-simple-login'; //Add class to body tag for styling
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}
$hashomeicon = ($PAGE->theme->settings->homeicon); //Check the theme settings to display the home icon
if (empty($hashomeicon)) {
    $bodyclasses[] = 'no_homeicon';
} else {
    $homeurl = new moodle_url('/index.php');
}
$hascallink = ($PAGE->theme->settings->callink); //Check the theme settings to display the calendar link
if (empty($hascallink)) {
    $bodyclasses[] = 'no_callink';
} else {
    $calurl = new moodle_url('/calendar/view.php');
}
$dateformat = $PAGE->theme->settings->dateformat; //Check the theme settings for the date format
if (empty($dateformat)) {
    $dateformat = "F j, Y";
}
$showuserpic = ($PAGE->theme->settings->userpic); //Check the theme settings to show the user profile picture
if (!empty($PAGE->theme->settings->headeralt)) {
    $headeralt = $PAGE->theme->settings->headeralt; //Use the theme settings for the page title
} else {
    $headeralt = $PAGE->heading; //Use the default page title if the theme setting value is empty
}
$showbranding = ($PAGE->theme->settings->branding); //Check the theme settings to see if footer logos are displayed
$userespond = ($PAGE->theme->settings->userespond); //Check the theme settings to see if respond.js should be called
$usecf = ($PAGE->theme->settings->usecf); //Check the theme settings to see if Chrome Frame should be called
$cfmaxversion = $PAGE->theme->settings->cfmaxversion; //Check the theme settings to see which versions of IE get prompted for Chrome Frame
$ieversion = strpos($PAGE->bodyclasses, $cfmaxversion);
$usingie = strpos($PAGE->bodyclasses, 'ie ie'); //Check if the user is using IE
$usingie9 = strpos($PAGE->bodyclasses, 'ie9'); //Make sure the user isn't using IE9 because it can use @media declarations natively
$usingios = (preg_match('/iPhone|iPod|iPad/i', $_SERVER['HTTP_USER_AGENT']));
$requiresrespond = ($userespond && $usingie && !$usingie9); //Check all the options necessary to print respond.js
$requirescf = ($usecf && $usingie && $ieversion); // Check all the options necessary to print chrome frame

echo $OUTPUT->doctype(); ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <?php echo $OUTPUT->standard_head_html(); ?>
    <?php if ($usecf) { ?><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><?php } ?>
    <title><?php echo $PAGE->title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>