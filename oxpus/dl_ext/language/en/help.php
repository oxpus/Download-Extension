<?PHP

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* [ english ] language file for Download Extension
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'HELP_TITLE' => 'Download Extension Online Help',

	'DL_NO_HELP_AVIABLE' => 'There is no help available for this option',

	'HELP_DL_ACTIVE'			=> 'Turns the Downloads according to the following options on or off.',
	'HELP_DL_ANTISPAM'			=> 'This option blocks downloads for which the user must have beside the required amount of traffic the required number of posted and the user posted the number of posts in the last hours.<br /><br />Example:<br />The settings contains 25 posts in 24 hours.<br />Based on this settings the downloads will blocked for the user if he/she will post 25 or more new posts in the last 24 hours.<br />This option shall prevent spamming for downloads, specially for new users, before a team member will get knowledge about this to take actions.<br />The download will still be shown as available to seduce the user. The user will only get a message about missing permissions.<br /><br />To disable this check just set one or both values to 0.',
	'HELP_DL_APPROVE'			=> 'This will approve the download immediately if you will send this form.<br />On the other hand this download will be hidden for users.',
	'HELP_DL_APPROVE_COMMENTS'	=> 'If you disable this option, each new comment must be approved by an download moderator or administrator before other user can see them.',

	'HELP_DL_BUG_TRACKER_CAT'	=> 'Enables the Bug Tracker for downloads in this category.<br />Bugs can be posted and viewed by every registered user for the related downloads and from other categories, if the bug tracker will be enabled there, too.<br />Only Administrators and Board Moderators can manage the bugs.<br />For each change on the bug messages the author will get an email message, and the team member, who shall work for the bug, will be informed, too.',

	'HELP_DL_CAT_DESCRIPTION'	=> 'A short description for this category.<br />BBCodes are not available here.<br />This description will shown on the downloads index and on subcategories.',
	'HELP_DL_CAT_EDIT_LINK'		=> 'Certainly who can see and use the link to edit a download from the category view, provided that this option is not deactivated.<br />"Own downloads" is only active, while the option to edit own downloads is still be activated, too.', 
	'HELP_DL_CAT_ICON'			=> 'The Category Icon must be still uploaded into the forum. E. g. into the folder /images/dl_icons/ (This folder must be created before the icons can be uploaded in this).<br />Enter the relative URL from the forum root, e. g. images/dl_icon.gif.<br /><br />Please use only icons which can be displayed by a web browser.<br />Recommended are for this JPG, GIF or PNG.<br />Regard the image size of the icons to avoid destructing the download index, because the icons will not be resized before using.',
	'HELP_DL_CAT_NAME'			=> 'This is the name of the category which will shown at every point.<br />Try to avoid special chars to avoid confused entries in the jump box.',
	'HELP_DL_CAT_PARENT'		=> 'The main level or an other category this category can be joined to.<br />With this dynamic drop down you can build hierarchical structures for your downloads.',
	'HELP_DL_CAT_PATH'			=> 'Here you must enter an existing path to your downloads.<br />This value must be the name of a sub-folder under the main folder (e. g .downloads/) which you have defined in the main configurations.<br />Enter here the folder name with a slash at the end.<br />For example there exists the folder "downloads/mods/" you must enter as category path just "mods/".<br />If you will send this form, the folder will be checked.<br />Be sure, the entered sub-folder will really exists!<br />If the folder is a sub-folder of a sub-folder, enter the complete hierarchy here.<br />E.g. "downloads/mods/misc/" must be entered as category path "mods/misc/".<br />Be sure that each sub-folder will have permissions CHMOD 777 and regard case sensitive folder names if you uses Unix/Linux.',
	'HELP_DL_CAT_RULES'			=> 'These rules will be displayed over the sub-categories and downloads while viewing the category.',
	'HELP_DL_CAT_TRAFFIC'		=> 'Enter the maximum monthly traffic for this category.<br />This traffic does not increase the overall traffic!<br />Enter 0 here to disable the limit.',
	'HELP_DL_CHOOSE_CATEGORY'	=> 'Choose the category which will include this download.<br />The file must already be saved in the folder you have entered in the category management before you will save this download.<br />Otherwise you will get an error message.',
	'HELP_DL_COMMENTS'			=> 'Activate the comment system for this category.<br />Users which you can enable with the upcoming drop downs can view and/or post comments in this category.<br />Administrators and Download Moderators can edit and delete all comments, the authors can manage their own texts.',
	'HELP_DL_COPY_PERMISSIONS'	=> 'This copies the permissions from the selected category.<br />If you selected the parent category this category will will become the permissions from the category which is was selected above under which this category will be joined to.<br />If the parent category will be the download index this category does not becomes any permissions. In this case choose an other category or set the permissions for this category with the module Permissions.',

	'HELP_DL_DELAY_AUTO_TRAFFIC'	=> 'Enter here the days after a new user will get the first auto traffic.<br />The delay starts with the registration day.<br />Enter 0 here to disable this delay.',
	'HELP_DL_DELAY_POST_TRAFFIC'	=> 'Enter here the number of days after which a new user will get the first traffic for posts.<br />The delay starts with the registration day.<br />Enter 0 here to disable this delay.',
	'HELP_DL_DISABLE_EMAIL'			=> 'With this Option you can enable or completely disable email notifications about new added or edited downloads.<br />While this function is enabled here, it can individually be disabled while add or edit a download.<br />Only user will get an email who have activate the email notifications about new or edited downloads in their download configuration.<br />They can open their configuration from the footer of the downloads.',
	'HELP_DL_DISABLE_EMAIL_FILES'	=> 'Check this option to suppress the email notification.<br />This will not affected the pop-up notification/board message or updating the last edit time.',
	'HELP_DL_DISABLE_POPUP'			=> 'With this Option you can enable or completely disable pop-up notifications or board message in the forum header about new added or edited downloads.<br />While this function is enabled here, it can individually be disabled while add or edit a download.<br />Only user will get a pop-up/board message who have activate the pop-up notifications about new or edited downloads in their download configuration.<br />They can open their configuration from the footer of the downloads.',
	'HELP_DL_DISABLE_POPUP_FILES'	=> 'Check this option to suppress the pop-up notification/board message.<br />This will not affected the email notifications or updating the last edit time.',
	'HELP_DL_DISABLE_POPUP_NOTIFY'	=> 'If this option is enabled, you can disable to change the edit time while editing a download.',
	'HELP_DL_DROP_TRAFFIC_POSTDEL'	=> 'If a post or topic will be deleted, this option can decrease the authors traffic, too (on deleting a topic only the topic author will be respected!), .<br />Regard that the user can gotten an other traffic amount on posting instead he will loose on deleting his post/topic so the amounts for posting and deleting a message can be different for him/her!',

	'HELP_DL_EDIT_OWN_DOWNLOADS'	=> 'If you enable this option, each user can edit their own uploaded files without being an Administrator or Download Moderator.',
	'HELP_DL_EDIT_TIME'				=> 'Enter here the number of days how long an edited download will be marked.<br />Enter 0 to disable this function.',
	'HELP_DL_ENABLE_JUMPBOX'		=> 'This option shows the jump box in the download footer or hide it.<br />If the jump box is disabled it will help to increase the performance of your download panel.',
	'HELP_DL_ENABLE_POST_TRAFFIC'	=> 'Set the amount of traffic a user will get for creating new topics, reply or quote like in the next two options.',
	'HELP_DL_ENABLE_RATE'			=> 'With this option you can enable/disable the rating system.<br />If there are still exists rating points they will not be deleted and the Extension shown them immediately if you enable the rating system again.',
	'HELP_DL_ENABLE_TOPIC'			=> 'Allows to create a topic for each new download which will be uploaded or added within the administration panel into the following forum and with the given text. For download which must be approved before be displayed the topic will be created over the moderation panel.',
	'HELP_DL_EXT_NEW_WINDOW'		=> 'Open external downloads in a new browser window or load them into the current window.',
	'HELP_DL_EXTERN'				=> 'Activate this function for an external files which you will enter in the field above (http://www.example.com/media.mp3).<br />The function "free" becomes insignificant.<br />Optional you can enter the file size for the external download. This size will be displayed in all pages.<br />Regard that here you will see the file size of internal downloads, too. In this case changes of this value will be ignored and replaces with the real file size of the download.',
	'HELP_DL_EXTERN_UP'				=> 'Activate this function for an external files which you will enter in the right field (http://www.example.com/media.mp3).insignificantThe function "free" becomes insignificant.',

	'HELP_DL_FILE_DESCRIPTION'	=> 'A short description for this download.<br />This will be displayed in the download category, too.<br />BBCodes are off for this text.<br />Please enter only a short text to reduces heavy data loads while open the category.',
	'HELP_DL_FILE_HASH_ALGO'	=> 'Defines, which method will be used to calculate the hash value for each download.<br />The hash will be calculated for all downloads and all of the existing variants, but they will only be shown in the download details when the settings are enabled there.<br />You can use the methods md5 and sha1, because these methods are mostly installed by default on the servers.<br />The Extension will calculate the hash automatically on add or edit a download. Also the hash will be calculated on open the detail view, if the hash was not calculated and saved before. This was build for an updated Extension or when you have changed the hash method.<br /><br />Hint:<br />When the hash method was changed, all existing hash values will be dropped, because they are not build within the selected hash method!',
	'HELP_DL_FILES_URL'			=> 'The file name of this download.<br />Enter this name without a leading file path or slash.<br />The file must exist before saving this download otherwise you will get an error message.<br />Note forbidden file Extensions: Each file which have a forbidden Extension will be blocked.',

	'HELP_DL_GUEST_STATS_SHOW'	=> 'This option will only include or exclude the statistical data about guests from the public category statistics.<br />The script will still collect all data.<br />The ACP statistics tool always displays the complete statistical datas.',

	'HELP_DL_HACK_AUTOR'			=> 'The author of this download file.<br />Leave this empty to hide this value at the download details and overall view.',
	'HELP_DL_HACK_AUTOR_EMAIL'		=> 'The email address of the author.<br />If you do not enter it here, it will be hidden at the download details and overall view.',
	'HELP_DL_HACK_AUTOR_WEBSITE'	=> 'Website of the author.<br />This URL should be the website of the author, not the page for the download (not all times the same).<br />Please do not enter links to protected sites or websites with doubtful contents.',
	'HELP_DL_HACK_DL_URL'			=> 'The page to an alternative download for this file.<br />This can be the website of the author or an other alternative website.<br />Please do not enter links for a direct download if the author will not allow this.',
	'HELP_DL_HACK_VERSION'			=> 'The declaration about the download release.<br />This will be all times displayed beside the download.<br />You can not search for this.',
	'HELP_DL_HACKLIST'				=> 'With this option you can add this download to the hack list (this must be enabled in the main configuration) if you choose Yes here.<br />No will not insert the download into the hack list.<br />Showing extra informations will display this block only in the download details.',
	'HELP_DL_HOTLINK_ACTION'		=> 'Here you can choose, how the download script should react, while it prevents a direct link to a download (also see the last option).<br />It will display a message (reduces the server load) or it redirects to the download (produces additional traffic).',

	'HELP_DL_ICON_FREE_FOR_REG'	=> 'This option switch the white icon for downloads (free download for registered users) for guests into white, too.<br />If you will disable this option, guests will see the red icon instead the white one.',
	'HELP_DL_IS_FREE'			=> 'Activate this function if the download should be free to download for everybody.<br />The traffic accounts will not be used.<br />Choose free for reg. Users to enable a free download only for registered users.',

	'HELP_DL_KLICKS_RESET'		=> 'This option can reset the clicks for this month back to zero.<br />This is useful if you will control the clicks after changing the file release.',

	'HELP_DL_LATEST_COMMENTS'		=> 'This option displays the latest X comments at the download details. Enter 0 to disable this block.',
	'HELP_DL_LIMIT_DESC_ON_INDEX'	=> 'Cut the descriptions after the entered number of characters.<br />Set 0 here to disable this function.',
	'HELP_DL_LINKS_PER_PAGE'		=> 'This option controls, how many downloads will be displayed on each category page and ACP statistics.<br />In the hack list and overview list the board setting "topics per page" will be used.',

	'HELP_DL_METHOD'			=> 'The "old" method will send the file directly to the web client.<br />This method is compatible to the most web environnements, but can not display the real file size while downloading, so the user client can not calculate the remaining download time.<br />The "new" method will display the real file size, but it can produce errors.<br />Use the "old" method, if you have trouble with the new one.<br />If nothing will work, check the box "direct" to link the download directly to the file on the server if this will be bigger than the PHP memory limit.',
	'HELP_DL_METHOD_QUOTA'		=> 'Set the file size here from which on the chunked file will be read as a big file if you have chosen the "new" download method.<br />Under this quota the file will be fetched by read file(), and send directly to the web client.',
	'HELP_DL_MOD_DESC'			=> 'Detailed descriptions about the entered Extension.<br />You can use BBCodes and Smilies in this text.<br />Line feeds will be formatted, too.<br />This text will only be shown in the download details.',
	'HELP_DL_MOD_DESC_ALLOW'	=> 'Enables the Extension information block while add or edit a download.',
	'HELP_DL_MOD_LIST'			=> 'Activate this block in the download details.<br />If you will not enable this option, the complete block will not be displayed.',
	'HELP_DL_MOD_REQUIRE'		=> 'Declarations which other Extensions a user needs to install or use this Download.<br />This text will only be shown in the download details.',
	'HELP_DL_MOD_TEST'			=> 'Declare on which phpBB this Extension was tested successful.<br />Just enter the release from the test board.<br />The script will display it as phpBB X, so you must only enter X here.<br />This text will only be shown in the download details.',
	'HELP_DL_MOD_TODO'			=> 'Here you can enter the next steps you have planned for this Extension or whose are currently in work.<br />This will create the ToDo list which can be opened from the downloads footer.<br />With this text user can be informed about the latest status of this Extension.<br />Line feeds will be formatted, BBCodes are not available here.<br />The ToDo list will still be filled even this block will be disabled.',
	'HELP_DL_MOD_WARNING'		=> 'Important advices about this Extension which must be regarded on installation, using or interaction with other Extensions.<br />This text will be shown in the download details formatted with an other colour (per default the font colour is red).<br />Line feeds will be formatted.<br />BBCodes are not available here.',
	'HELP_DL_MUST_APPROVE'		=> 'Enable this option to approve each new uploaded download file before it will be displayed in this category.<br />Administrators and Download Moderators will get emails about each new unapproved downloads.',

	'HELP_DL_NAME'					=> 'This is the name of the downloads, which will shown at the different places.<br />Try to avoid special chars to reduce display errors.',
	'HELP_DL_NEW_TIME'				=> 'Enter here the number of days how long a new download will be marked.<br />Enter 0 to disable this function.',
	'HELP_DL_NEWTOPIC_TRAFFIC'		=> 'For each new posted topic the author will get the here entered traffic on top of its traffic amount.',
	'HELP_DL_NO_CHANGE_EDIT_TIME'	=> 'Check this option to suppress to update the latest edit time for this download.<br />This will not affected the email and pop-up notification/board message.',

	'HELP_DL_OFF_HIDE'					=> 'Hides the link in the board navigation.<br />Otherwise the download area will only display a message.',
	'HELP_DL_OFF_NOW_TIME'				=> 'Turns the downloads immediately off or deactivates the downloads regularly between the following timestamps.',
	'HELP_DL_OFF_PERIOD'				=> 'Time period the download will automatically turn off.',
	'HELP_DL_OFF_PERIOD_TILL'			=> 'Time period the download will automatically turn off.',
	'HELP_DL_ON_ADMINS'					=> 'Allows board administrators to enter the downloads and work within while the download Extension is deactivated.<br />Otherwise the administrators will be locked out, too.',
	'HELP_DL_OVERALL_TRAFFIC'			=> 'The overall limit for registered users for all downloads and, if enabled, all uploads, too, which can not be exceeded in the current month.<br />After reaching this limit, each download will similar be marked and locked and, if enabled, uploads will be impossible, too.',
	'HELP_DL_OVERALL_GUEST_TRAFFIC'		=> 'The overall limit for guests for all downloads and, if enabled, all uploads, too, which can not be exceeded in the current month.<br />After reaching this limit, each download will similar be marked and locked and, if enabled, uploads will be impossible, too.',
	'HELP_DL_OVERVIEW_LINK'				=> 'Display the link to the overall list or hide it.<br />Hint:<br />While the link is disabled the overall list can not be opened within a direct link!',

	'HELP_DL_PHYSICAL_QUOTA'	=> 'The overall physical limit the Extension will be able to use to save and manage downloads.<br />If this limit is reached, new downloads can only be added when they will are uploaded with a ftp client and added with the file management on ACP.',
	'HELP_DL_POSTS'				=> 'Each user, even each Administrator and Download Moderator, must have posted at minimum this number of posts to be able to download not free downloads.<br />It is recommended to install an Anti Spam Module, too,  to avoid spamming poster.<br />Enter 0 to disable this function, recommended for young boards.',
	'HELP_DL_PREVENT_HOTLINK'	=> 'Enable this option, if you wants to prevent each direct download link except from the download details.<br />This option does <strong>not</strong> build a folder protection!',

	'HELP_DL_RATE_POINTS'			=> 'Sets the number of maximum rate points a user can give a download at maximum.<br /><br /><strong>Regard:</strong><br />If you change these settings all given rating points will be dropped to enable the Extension to calculate correct rating points!',
	'HELP_DL_REPLY_TRAFFIC'			=> 'The user will get for each new reply and quote the here entered traffic on top of its traffic amount.',
	'HELP_DL_REPORT_BROKEN'			=> 'Turn the function on or off to report broken downloads.<br />If you will set it to "not for guests", only registered users can report downloads.',
	'HELP_DL_REPORT_BROKEN_LOCK'	=> 'If you enable this option the download will be locked while it is reported as broken.<br />It will hide the download button and no user can download the file until an Administrator or Download Moderator will unlock the download.',
	'HELP_DL_REPORT_BROKEN_MESSAGE'	=> 'If a download was reported as broken a message about this will be displayed.<br />If you enable this option the message will only comes up while the download is locked, too.<br />In this case not under but instead the download button.',
	'HELP_DL_REPORT_BROKEN_VC'		=> 'Enables a visual confirmation if a user will report a broken download.<br />Only if the code was correct the report will be saved and Administrators and Download Moderators will be informed with an email.',

	'HELP_DL_RSS_ENABLE'				=> 'The RSS Feed for downloads can be enabled/disabled completely.<br />When this function is disabled, the following two options will define what the user will see instead the feed.',
	'HELP_DL_RSS_OFF_ACTION'			=> 'With this option you can define what the feed will do when it is disabled.',
	'HELP_DL_RSS_OFF_TEXT'				=> 'This message will be displayed in the RSS feed instead the download entries if the function is disabled and the last option is set to display the text.<br />If the last option is set to redirect the text will still be enabled but just not be displayed.',
	'HELP_DL_RSS_CATS'					=> 'The entries in the RSS feed will be taken from all or selected categories like the list in this option.<br />To select more than one category hold the CRTL key down while you click on the category names.<br />You can choose to include the selected or not selected categories in the feed.',
	'HELP_DL_RSS_PERMS'					=> 'Against the selection from which categories the feed should be build it can be useful to regard the user permissions in the download categories or for guests/bots to avoid displaying downloads which must not be displayed for the current account.<br />The setting "for guests" will select only categories which can be viewed by guests and bots.<br />If a user or guest/bot will not get at least one entry in the feed the script will work like it is disabled.',
	'HELP_DL_RSS_NEW_UPDATE'			=> 'This option displays the marks for new or updated downloads like the mini icon in the category view',
	'HELP_DL_RSS_NUMBER'				=> 'Number of downloads the feed will be display at maximum.',
	'HELP_DL_RSS_SELECT'				=> 'This option defines to display the latest or random download title in the feed based on the selected categories the permissions and the maximum number.',
	'HELP_DL_RSS_DESC_LENGTH'			=> 'With this option the descriptions of the downloads can be displayed. You can select between the full description or the short one like you can use on the download index.<br /><br /><strong>Attention:</strong><br />Because not all RSS reader can use and display HTML-Codes, the download description can be displayed incorrect or the reader will simply display no entry. In this case the user can use an other reader or you must disable the download descriptions.',
	'HELP_DL_RSS_DESC_LENGTH_SHORTEN'	=> 'Cut the description from the downloads after the here setted characters, if the description should be shown shorten (previous option).<br />0 will hide the description!',

	'HELP_DL_SHORTEN_EXTERN_LINKS'	=> 'Enter the length of the displayed external download link on the download details.<br />Based on the length of the link it will be cut in the middle or shorten beginning from the right side.<br />Leave this field empty or enter 0 to disable this function.',
	'HELP_DL_SHOW_FOOTER_EXT_STATS'	=> 'Shows in the download footer additional the overall traffic for registered users and guests and the number of clicks for the current month.',
	'HELP_DL_SHOW_FILE_HASH'		=> 'Shows the file hash in the download details or hide it.',
	'HELP_DL_SHOW_FOOTER_LEGEND'	=> 'This option will turn the legend with the download status icons on the download footer on and off.<br />The icons which you can find beside the downloads will not be changed by this option.',
	'HELP_DL_SHOW_FOOTER_STAT'		=> 'This option will turn the statistic lines on the download footer on and off.<br />The statistic will still earn data, if you switch it off.',
	'HELP_DL_SHOW_REAL_FILETIME'	=> 'With this you can display the real last edit time of the download files in the download details.<br />This is the exactest time-code even files will be uploaded with a ftp client or not updated while editing the downloads.',
	'HELP_DL_SIMILAR_DL'			=> 'Shows on detail view similar downloads from the same category.<br /><br />Regard: On bigger download databases this option can increase the loading time of the detail view, so this option should be disabled.',
	'HELP_DL_SIMILAR_DL_LIMIT'		=> 'Number of similar downloads.',
	'HELP_DL_SORT_PREFORM'			=> 'The option "Preset" will sort all downloads in all categories for all users like they are sorted in the ACP.<br />With the option "User" each user can choose, how downloads will be sorted for him/her and if this sorting will be fix or extended with other sort criteria.',
	'HELP_DL_STAT_PERM'				=> 'Select here from which user level upsides user can view the download statistic page.<br />E. g. if you will enable it for Download Moderators each board administrator and download moderator (NOT forum moderator!) can open and view this page.<br />Note that this page can produce a heavy load, so we recommended to do not open it for the masses if you have a bigger board and/or manage many downloads.',
	'HELP_DL_STATISTICS'			=> 'Enables detailed statistics about the files.<br />Note that this statistics will produces additional database queries and datasets in a separate table.',
	'HELP_DL_STATS_PRUNE'			=> 'Enter the number of data rows the statistic for this category can reach.<br />Each new row will delete the oldest one.<br />Enter 0 here to disable pruning.',
	'HELP_DL_STOP_UPLOADS'			=> 'You can enable/disable uploads with this option.<br />If you disable this option, only administrators will be able to upload new files.<br />Enable this option to allow users to upload depending on the category and group permissions.',

	'HELP_DL_THUMB'						=> 'This field can upload a small image (note the displayed file size and image dimensions under this field) to display it in the download details.<br />If there already exists a thumbnail, you can upload a new one to replace it.<br />Check the existing thumbnail for "delete" you will just drop it.',
	'HELP_DL_THUMB_CAT'					=> 'This option enable Thumbnails on downloads in this category.<br />The size of these Images will base on the settings in the main configuration of this Extension.',
	'HELP_DL_THUMB_MAX_DIM_X'			=> 'This values will limit the possible image width of uploaded files for thumbnails.<br />The thumbnails itself are at max 150 x 100 pixel and you can view the uploaded images in a pop-up if you click on a thumbnail.<br /><br />Enter 0 here to disable thumbnails (not recommended, if the thumbnail file size will be set).<br />Existing thumbnails will be displayed until the thumbs file size was not changed to 0, too.',
	'HELP_DL_THUMB_MAX_DIM_Y'			=> 'This values will limit the possible image height of uploaded files for thumbnails.<br />The thumbnails itself are at max 150 x 100 pixel and you can view the uploaded images in a pop-up if you click on a thumbnail.<br /><br />Enter 0 here to disable thumbnails (not recommended, if the thumbnail file size will be set).<br />Existing thumbnails will be displayed until the thumbs file size was not changed to 0, too.',
	'HELP_DL_THUMB_MAX_SIZE'			=> 'Enter 0 as file size to disable thumbnails in all categories.<br />If you allow thumbnails, then enter the maximum image sizes for the uploaded image files to create new thumbnails from.<br />If you disable thumbnails you can not see existing thumbnails in the download details, too.',
	'HELP_DL_TODO_LINK'					=> 'Switch the link for the to-do list in the download footer on or off. >The to-do entries the management about them are not affected while changing this option.',
	'HELP_DL_USE_TODOLIST'				=> 'Activate or disable the to-do list.',
	'HELP_DL_TOPIC_DETAILS'				=> 'Shows the download description, the file name, file size or on external downloads the URL in the forum topic.<br />The text can be placed over or after the previous entered text.<br />If the topic will be created over the download categories the option in the general configuration will be ignored.',
	'HELP_DL_TOPIC_FORUM'				=> 'The forum which will display the new topics about the downloads.<br />If you select instead a forum the option "Category select" the topic will be inserted in the forum you select in the categories.',
	'HELP_DL_TOPIC_FORUM_C'				=> 'The forum which will display the new topics about the downloads from this category.',
	'HELP_DL_TOPIC_POST_CATNAME'		=> 'Adds the category name into the topic post which will be generated for downloads. The category name will be inserted after the download title.<br />Regard:<br />Existing topics will not be updated until the download will be updated, too.',
	'HELP_DL_TOPIC_TEXT'				=> 'Free text for creating the topics about the downloads. BBCodes, HTML and smileys are not allowed here because the text shall only be used to introduce the topic.',
	'HELP_DL_TOPIC_TITLE_CATNAME'		=> 'Adds the category name to the topic title which will be generated for downloads. The category name will be separated by - from the download name.<br />Regard:<br />Existing topics will not be updated until the download will be updated, too.',
	'HELP_DL_TOPIC_USER'				=> 'Select here, which user shall be author of the download topics.<br />If the current user should be the topic author, then select the option current user. The Option selected by category allows to choose for each category a separately topic user. This can still be the current user or over ID selected user which is entered in the fields on the right side of the drop down field. This is recommended for the option "Select user over ID".<br /><br /><strong>Hint:</strong><br />The user id will not be checked by the download Extension itself, so a not existing ID can disrupt the functions!',
	'HELP_DL_TRAFFIC'					=> 'The maximum of traffic a file will be allowed to produce.<br />A value of 0 deactivates the traffic control.<br />Regard that the file traffic will be set to 0, even the download is marked as external.',
	'HELP_DL_TRAFFIC_OFF'				=> 'Turns the entire traffic management in the download area off and deactivated all following options about the traffic, too.<br />Enabling this option will hidden all the texts about the download traffic in the forum and does not consider other traffic limits. Similarly during the download and upload traffic data are not changed any more.<br />Changes to the user traffic while writing or deleting posts are not taken on user accounts as well.<br />Automatically assigned traffic will not longer allocate to users if this option is turned off. However users or group members can still get traffic with the ACP traffic management module.<br />Also in the administration area all modules, texts and functions for traffic management will be still unchanged.',
	'HELP_DL_TRAFFICS_FOUNDER'			=> 'While the traffic management is disabled for founders, these users can unlimited download and upload files against the traffic options.<br />These users will also not get automatically traffic amounts and will not get traffic points on add a topic or post, even this option is enabled, too.<br />This option will freeze the current traffic amount for founders until this option will be disabled.',
	'HELP_DL_TRAFFICS_OVERALL'			=> 'This option will set a limit for the overall traffic for registered users.<br />The overall traffic can be enabled or disabled for all registered users or can be set only for members of the user groups which can be selected in the next option.<br />If the overall traffic was disabled all affected users can unlimited download and upload files.',
	'HELP_DL_TRAFFICS_OVERALL_GROUPS'	=> 'The last option will be applied to the following user groups and their members, while the last option will limit the traffic for them.',
	'HELP_DL_TRAFFICS_USERS'			=> 'This option will set a limit to the user traffics for registered users.<br />The user traffic can be enabled or disabled for all registered users or can be set only for members of the user groups which can be selected in the next option.<br />If the user traffic was disabled all affected users can unlimited download and upload files.<br />The affected users also will not get automatic traffic amounts and will not get traffic points while writing a topic or post even this options are enabled, too.',
	'HELP_DL_TRAFFICS_USERS_GROUPS'		=> 'The last option will be applied to the following user groups and their members, while the last option will limit the traffic for them.',
	'HELP_DL_TRAFFICS_GUESTS'			=> 'Enable or disable the overall traffic for guests.<br />If this option was disabled, guests can based on their permission unlimited download and upload files.<br />Because this option can cause many traffic for your forum and stress the server while guests can unlimited download and upload files, the server can slow down until a totally deadlock. So it is not recommended to disable this option.',

	'HELP_DL_UCONF_LINK'			=> 'Switch the link for the user configuration in the download footer on or off. >The user configuration are not affected while changing this option.',
	'HELP_DL_UPLOAD_FILE'			=> 'The upload file from your computer.<br />Be sure, the file size will be smaller as shown and the file Extension will not included the list you can see under this field.',
	'HELP_DL_UPLOAD_TRAFFIC_COUNT'	=> 'If the option is enabled, uploads will lower down the monthly overall traffic, too.<br />After the overall limit will be reached no upload will be possible and new downloads must be uploaded with a ftp client and added in the ACP.',
	'HELP_DL_USE_EXT_BLACKLIST'		=> 'If you enable the blacklist all entered file types will be blocked for new uploaded or edited downloads.',
	'HELP_DL_USE_HACKLIST'			=> 'This option switch the hack list on or off.<br />If it is enabled, you can enter hack informations while add or edit a download to insert the download into the hack list.<br />If you will disable the hack list, it will completely be hidden for each user like you never have installed it, but you can enable it every time.<br />Note that each hack information in the downloads will be lost, if you will edit the file after the hack list was disabled.',
	'HELP_DL_USER_TRAFFIC_ONCE'		=> 'Choose, if downloads should only decrease the user traffic only for the first time downloading a file.<br /><strong>Regard:</strong><br />This option will NOT change the download status itself!',

	'HELP_DL_VISUAL_CONFIRMATION'	=> 'Enable this option to let the user enter a displayed 5 digits confirmation code to access the download.<br />If the user will not enter the code or the code will be wrong the Extension will just display a message and stop the download.',

	'HELP_DOWNLOAD_PATH'			=> 'The relative path under your phpBB root folder.<br />On first installation of this package the storage will be set to "forumroot/ext/oxpus/dl_ext/files/downloads/".This can be changed in the ACP within the extensions settings to "forumroot/store/oxpus/dl_ext/downloads/".<br />Regard if you will still manage a huge number of downloads the automatic storage movement will take a while or can produce errors. In this case activate the next option to switch over to the alternative storage place and move the files manually into the destination folder (e.g. within a FTP client).<br />Under this basic folder you must create one or more sub-folders which will contains all physical files.<br />It is recommended to create a sub-folder for each logical group of categories.<br />This sub-folder must be entered as path in the category with the same syntax as the main folder, but without using the main folder (for more read the help in the category management).<br />You can create more sub-folders by using a ftp client or by using the Toolbox (see the drop down on the top right of this page).',

	'HELP_NUMBER_RECENT_DL_ON_PORTAL'	=> 'The number of latest downloads the user which see on the portal.<br />The block uses the last edit time for this list, so it can be possible to have an older download on top of this list.',
));
