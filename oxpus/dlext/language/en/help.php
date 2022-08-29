<?PHP

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	$lang = [];
}

$lang = array_merge($lang, [
	'HELP_TITLE' => 'Download Extension Online Help',

	'DL_NO_HELP_AVAILABLE' => 'There is no help available for this option',

	'HELP_DL_ACTIVE'			=> 'Turns the Downloads on and off according to the following options.',
	'HELP_DL_ANTISPAM'			=> 'This option blocks downloads for which the user must have the required amount of traffic and the required number of posts and the user reached this number of posts in the last hours.<br /><br />Example:<br />The setting contains 25 posts in 24 hours.<br />Based on this setting the downloads will blocked for the user if he/she will post 25 or more new posts in the last 24 hours.<br />This option is intended to prevent spamming for downloads, specially for new users, before a team member will get knowledge about this to take action.<br />The download will still be shown as available to seduce the user. The user will only get a message about missing permissions.<br /><br />To disable this check just set one or both values to 0.',
	'HELP_DL_APPROVE'			=> 'This will approve the download immediately after submitting this form.<br />Otherwise this download will be hidden for users until approved by a download moderator or administrator.',
	'HELP_DL_APPROVE_COMMENTS'	=> 'If you disable this option, each new comment must be approved by a download moderator or administrator before other users can see it.',

	'HELP_DL_BUG_TRACKER_CAT'	=> 'Enables the Bug Tracker for downloads in this category.<br />Bugs can be posted and viewed by every registered user for the related downloads and from other categories, if the bug tracker is enabled there, too.<br />Only Administrators and Board Moderators can manage the bugs.<br />For each change on the bug message the author will get an email message and the team member working on that bug will be informed, too.',

	'HELP_DL_CAT_DESCRIPTION'	=> 'A short description of this category.<br />BBCodes are only available if the description will be displayed on index at all times.<br />This description will be shown on the downloads index and on subcategories.',
	'HELP_DL_CAT_EDIT_LINK'		=> 'Determines who can see and use the link to edit a download from the category view, provided that this option is not deactivated.<br />´Own downloads´ is only active if the option to edit own downloads is activated, too.',
	'HELP_DL_CAT_ICON'			=> 'The category icon must be already uploaded into the forum, e.g. into the folder /images/dl_icons/ (this folder must be created before the icons can be uploaded into it).<br />Enter the relative URL from the forum root, e. g. images/dl_icon.gif.<br /><br />Please use only icons which can be displayed by a web browser.<br />Recommended files are JPG, GIF or PNG.<br />Regard the image size of the icons to avoid destructing the download index, because the icons will not be resized before using.',
	'HELP_DL_CAT_NAME'			=> 'This is the name of the category which will shown at every point.<br />Do not use special characters to avoid hardly legible entries in the jump box.',
	'HELP_DL_CAT_PARENT'		=> 'The top level or any other category this category can be assigned to.<br />You can build hierarchical structures for your downloads with this dynamic drop down.',
	'HELP_DL_CAT_PATH'			=> 'Enter an existing path to your downloads here.<br />This value must be the name of a sub-folder under the main folder (e. g .downloads/) which you have defined in the main configurations.<br />Enter the folder name with a trailing slash.<br />As an example for the existing folder ´downloads/mods/´ enter ´mods/´ as category path.<br />After submitting this form, the folder will be checked.<br />Please make sure that the specified sub-folder really exists!<br />If the folder is a sub-folder of a sub-folder, enter the complete hierarchy here.<br />E.g. ´downloads/mods/misc/´ must be entered as category path ´mods/misc/´.<br />Make sure that each sub-folder has permissions CHMOD 777 and keep in mind that Unix/Linux does have case sensitive folder names.',
	'HELP_DL_CAT_RULES'			=> 'These rules will be displayed above the sub-categories and downloads while viewing the category.',
	'HELP_DL_CAT_TRAFFIC'		=> 'Enter the maximum monthly traffic for this category.<br />This traffic does not increase the overall traffic!<br />Enter 0 here to disable the limit.',
	'HELP_DL_CHOOSE_CATEGORY'	=> 'Choose the category to contain this download.<br />The file must already be stored in the folder you have entered in the category management before you can save this download.<br />Otherwise you will get an error message.',
	'HELP_DL_COMMENTS'			=> 'Activate the comment system for this category.<br />Users enabled through the upcoming drop downs can view and/or post comments in this category.<br />Administrators and Download Moderators can edit and delete all comments, authors can manage their own texts.',
	'HELP_DL_COPY_PERMISSIONS'	=> 'Copies the permissions from the selected category.<br />If you selected the parent category this category will get the permissions from the parent category it will be joined to.<br />If the parent category is the download index (top level) this category does not get any permissions. In this case choose another category or set the permissions for this category with the Permissions module.',

	'HELP_DL_DELAY_AUTO_TRAFFIC'	=> 'Enter here the number of days after which a new user will get the first auto traffic.<br />Counting starts with the registration date.<br />Enter 0 to disable this delay.',
	'HELP_DL_DELAY_POST_TRAFFIC'	=> 'Enter here the number of days after which a new user will get the first traffic for posts.<br />The delay starts with the registration date.<br />Enter 0 to disable this delay.',
	'HELP_DL_DISABLE_NOTIFY'		=> 'With this Option you can enable or completely disable notifications about newly added or edited downloads.<br />If this function is enabled, it can individually be disabled while adding or editing a download.<br />Users will get an notification only if they have activated the notifications about new or edited downloads in their UCP.',
	'HELP_DL_DISABLE_POPUP_NOTIFY'	=> 'If this option is enabled logging the time stamp for editing a download is disabled.',
	'HELP_DL_DROP_TRAFFIC_POSTDEL'	=> 'If enabled an author`s traffic will be decreased by the traffic yielded for posting (on deleting a topic only the topic author will be affected!).<br />Please keep in mind that the original yield can differ from the current yield and this author`s decreased traffic value might differ from what was gained originally!',

	'HELP_DL_EDIT_OWN_DOWNLOADS'	=> 'If you enable this option, each user can edit their own uploaded files without being an Administrator or Download Moderator.',
	'HELP_DL_EDIT_TIME'				=> 'Enter here the number of days how long an edited download will be marked.<br />Enter 0 to disable this function.',
	'HELP_DL_ENABLE_INDEX_DESC'		=> 'Hides the description of the downloads in the categories view.<br />If this option is enabled the length of the displayed description can be set with the following option.',
	'HELP_DL_ENABLE_JUMPBOX'		=> 'This option shows or hides the jump box in the download footer.<br />Disabling the jump box will increase the performance of your download panel.',
	'HELP_DL_ENABLE_POST_TRAFFIC'	=> 'The following two options will set the amount of traffic a user will gain for creating new topics or for replying or quoting.',
	'HELP_DL_ENABLE_RATE'			=> 'With this option you can enable/disable the rating system.<br />Existing rating points will not be deleted by disabling the rating system, instead they will be stored and shown again immediately if you re-enable the rating system.',
	'HELP_DL_ENABLE_SEARCH_DESC'	=> 'Hides the download description in the search results. <br />If this option is disabled, the length of the description displayed can be set with the following option.',
	'HELP_DL_ENABLE_TOPIC'			=> 'Allows creating a topic into the following forum and with the given text for each new download which will be uploaded or added within the administration panel. If a download must be approved before being displayed the topic will be created afterwards within the moderation panel.',
	'HELP_DL_EXT_NEW_WINDOW'		=> 'Opens external downloads in a new browser window or loads them into the current window.',
	'HELP_DL_EXTERN'				=> 'Activate this function to enable entering a URL outside your own server in the line above (e.g. http://www.example.com/media.mp3).<br />In this case the ´free´ setting becomes obsolete.<br />Optionally you can enter the file size for the external download. This size will be displayed in all pages and can be edited.<br />Please keep in mind that the file size will be displayed in case the download is not marked as external. In this case changes of this value will be ignored and replaced with the real file size of the download.',
	'HELP_DL_EXTERN_UP'				=> 'Activate this function to enable entering a URL outside your own server in the field to the right (e.g. http://www.example.com/media.mp3).<br />In this case the ´free´ setting becomes obsolete.',

	'HELP_DL_FILE_DESCRIPTION'	=> 'A short description for this download.<br />This will be displayed in the download category, too.<br />BBCodes are off for this text.<br />Please enter only a short text to reduce heavy data loads while opening the category.',
	'HELP_DL_FILE_EDIT_HINT'	=> 'Allows a explanatory text when adding or editing a download. This text is prominently visible at the beginning of the form.<br />BBCodes are possible.',
	'HELP_DL_FILE_HASH_ALGO'	=> 'Defines the method being used to calculate the hash value for each download.<br />A hash value will be calculated for all downloads and all the existing variants, but will only be shown in the download details when the respective settings are enabled.<br />Available methods are md5 and sha1 since these methods are mostly installed by default on the servers.<br />TheeExtension will calculate the hash value automatically on adding or editing a download. Also the hash value will be calculated on opening the detail view if there was not calculated and saved a hash value previously. This is mainly intended for an updated extension or if you have changed the hash method.<br /><br /><strong>Hint:</strong><br />If the method calculating the hash value is changed all existing hash values will be dropped, because they are not build with the currently selected method!',
	'HELP_DL_FILES_EXTERN'		=> 'URL from an external file',
	'HELP_DL_FILES_INTERN'		=> 'The file name of this download.<br />Enter this name without a leading file path or slash.<br />The file must exist before saving this download otherwise you will get an error message.<br />Please note that using forbidden file extensions blocks saving the file!',

	'HELP_DL_GLOBAL_BOTS'		=> 'This option allows or denies access to the download area for bots.<br />All other permissions are not affected by this option.',
	'HELP_DL_GLOBAL_GUESTS'		=> 'This option allows or denies access to the download area for guests.<br />All other permissions are not affected by this option.',
	'HELP_DL_GUEST_STATS_SHOW'	=> 'This option will include or exclude the statistical data about guests from the public category statistics.<br />The script will still collect all data.<br />The ACP statistics tool always displays the complete statistical data.',

	'HELP_DL_HACK_AUTOR'			=> 'The author of this download file.<br />Leave empty to hide this value in the download details and overall view.',
	'HELP_DL_HACK_AUTOR_EMAIL'		=> 'The author´s email address.<br />Leave empty to hide this value in the download details and overall view.',
	'HELP_DL_HACK_AUTOR_WEBSITE'	=> 'The author´s website.<br />This URL should be the author´s website of the author, not the URL for the download (not all times the same).<br />Please do not enter links to websites with proprietary or questionable content.',
	'HELP_DL_HACK_DL_URL'			=> 'The URL to an alternative download for this file.<br />This can be the author´s website or another alternative website.<br />Please do only enter links for a direct download if the author has explicitly permitted to do so.',
	'HELP_DL_HACK_VERSION'			=> 'The version of the download release.<br />This will only be displayed on the download´s page.<br />This value is not searchable.',
	'HELP_DL_HACKLIST'				=> 'By selecting `Yes` you can add this download to the hack list (hack list must be enabled in the main configuration).<br />Selecting `No` prevents addition of the download to the hack list and `Showing extra informations` will display this block only in the download details.',
	'HELP_DL_HOTLINK_ACTION'		=> 'Here you can choose how the download script should react if a direct link to a download has been averted (also see the last option).<br />It will display a message (reduces the server load) or it redirects to the download (produces additional traffic).',

	'HELP_DL_ICON_FREE_FOR_REG'		=> 'If enabled this option switches the download icon for guests into white (free download for registered users), too.<br />If you disable this option, guests will see the red icon instead of the white one.',
	'HELP_DL_INDEX_DESC_HIDE'		=> 'Hides the descriptions for the categories on the download index and for sub categories.<br />The descriptions will then fade in on hovering the mouse pointer over the category row.',
	'HELP_DL_IS_FREE'				=> 'Enable this setting if the download should be free for everybody and not be charged to the traffic account.<br />Choose `Free for registered users` to enable a free download for registered users only.',

	'HELP_DL_KLICKS_RESET'			=> 'This option resets the clicks for the current month back to zero.<br />This is useful if you want to control the clicks after updating the file release.',

	'HELP_DL_LATEST_COMMENTS'		=> 'This option displays the last X comments at the download details. Enter 0 to disable this block.',
	'HELP_DL_LATEST_DOWNLOADS'		=> 'Determines whether this list is deactivated, shows all downloads (this corresponds to the overall view, sorted descending by age) or the currently last added or changed downloads.',
	'HELP_DL_LIMIT_DESC_ON_INDEX'	=> 'Cuts the download descriptions within the categories after the entered number of characters.<br />Set 0 here to disable this function.',
	'HELP_DL_LIMIT_DESC_ON_SEARCH'	=> 'Cuts the download descriptions within search results after the entered number of characters.<br />Set 0 here to disable this function.',
	'HELP_DL_LINKS_PER_PAGE'		=> 'This option controls the number of downloads displayed on each category page and ACP statistics.<br />In the hack list and overview list the board setting ´topics per page´ will be used.',

	'HELP_DL_MOD_DESC'			=> 'Detailed description of the entered extension.<br />Usage of BBCodes and smilies is possible, line feeds will be considered, too.<br />This text will be shown in the download details only.',
	'HELP_DL_MOD_DESC_ALLOW'	=> 'Enables the extension information block while adding or editing a download.',
	'HELP_DL_MOD_LIST'			=> 'Activate displaying this block in the download details.<br />If disabled the complete block will not be displayed.',
	'HELP_DL_MOD_REQUIRE'		=> 'Declares what other extensions a user needs to install or use this download.<br />This text will be shown in the download details only.',
	'HELP_DL_MOD_TEST'			=> 'Specifies on which phpBB version this extension was tested successful.<br />Just enter the release from the test board.<br />The script will display this as`phpBB X`, so you must only enter `X` here.<br />This text will be shown in the download details only.',
	'HELP_DL_MOD_TODO'			=> 'Here you can enter the next steps you have planned for this extension or those which are currently in work.<br />This will create the to-do list which can be opened from the download´s footer.<br />With this text users can be informed about the latest status of this extension.<br />Line feeds will be considered but BBCodes are not available.<br />The to-do list can still be filled even this block is disabled.',
	'HELP_DL_MOD_WARNING'		=> 'Important advice about this extension which must be regarded on installation, usage or interaction with other extensions.<br />This text will be displayed highlighted in the download details formatted (default is red).<br />Line feeds will be considered.<br />BBCodes are not available here.',
	'HELP_DL_MUST_APPROVE'		=> 'Enable this option to force approval of each new uploaded download file before it will be displayed in this category.<br />Administrators and Download Moderators will be notified by email about unapproved new downloads.',

	'HELP_DL_NAME'					=> 'This is the name of the download under which it will be displayed throughout this extension.<br />Please refrain from using special characters to prevent errors.',
	'HELP_DL_NEW_TIME'				=> 'Enter the number of days a download will be marked as new.<br />Enter 0 to disable this function.',
	'HELP_DL_NEWTOPIC_TRAFFIC'		=> 'Enter the amount of traffic an author will be credited for posting a new topic.',
	'HELP_DL_NO_CHANGE_EDIT_TIME'	=> 'Check this option to suppress updating the last edited time of this download.<br />Email and popup notifications/board messages will not be affected.',

	'HELP_DL_OFF_HIDE'					=> 'Hides the link in the board navigation.<br />Otherwise the download area will only display a message.',
	'HELP_DL_OFF_NOW_TIME'				=> 'Deactivates the downloads immediately or regularly between the following times.',
	'HELP_DL_OFF_PERIOD'				=> 'Time period in which the download will be turned off automatically.',
	'HELP_DL_OFF_PERIOD_TILL'			=> 'Time period in which the download will be turned off automatically.',
	'HELP_DL_ON_ADMINS'					=> 'Allows board administrators to enter the downloads area and work within it while the download extension is deactivated.<br />Otherwise the administrators will be locked out, too.',
	'HELP_DL_OVERALL_TRAFFIC'			=> 'The overall limit for registered users for all downloads and, if enabled, all uploads, too, which can not be exceeded in the current month.<br />After reaching this limit, each download will be marked and locked and, if enabled, uploads will be impossible, too.',
	'HELP_DL_OVERALL_GUEST_TRAFFIC'		=> 'The overall limit for guests for all downloads and, if enabled, all uploads, too, which can not be exceeded in the current month.<br />After reaching this limit, each download will be marked and locked and, if enabled, uploads will be impossible, too.',
	'HELP_DL_OVERVIEW_LINK'				=> 'Displays the link to the overall list or hide it.<br />Hint:<br />While the link is disabled the overall list can not be opened using a direct link!',

	'HELP_DL_PHYSICAL_QUOTA'	=> 'The overall physical limit the extension may use to save and manage downloads.<br />If this limit is reached, new downloads can be added only by using a ftp client and the file management in the ACP.',
	'HELP_DL_POSTS'				=> 'Each user, even each Administrators and Download Moderators, must have posted at minimum this number of posts to be able to download files not under the free download.<br />It is strongly recommended to install an anti spam module to avoid spamming posters.<br />Enter 0 to disable this function, recommended for lately founded boards.',
	'HELP_DL_PREVENT_HOTLINK'	=> 'Enable this option if you want to prevent direct download links except from the download details.<br />This option does <strong>not</strong> protect your download directories!',

	'HELP_DL_RATE_POINTS'			=> 'Sets the maximum rate points a user can award to a download.<br /><br /><strong>Please note:</strong><br />If you change this setting all awarded rating points will be dropped to enable the extension to calculate correct rating points!',
	'HELP_DL_REPLY_TRAFFIC'			=> 'The user will be credited the amount of traffic entered here for each new reply and quote.',
	'HELP_DL_REPORT_BROKEN'			=> 'Turns on or off the function to report broken downloads.<br />If you set this to `not for guests` only registered users can report broken downloads.',
	'HELP_DL_REPORT_BROKEN_LOCK'	=> 'If you enable this option the download will be disabled while it is reported as broken.<br />This will hide the download button and prevent anyone from downloading this file until an Administrator or Download Moderator re-enables it.',
	'HELP_DL_REPORT_BROKEN_MESSAGE'	=> 'If a download was reported as broken this will be announced with a message.<br />If you enable this option the message will only be displayed instead of the download button while the download is locked.',
	'HELP_DL_REPORT_BROKEN_VC'		=> 'Enables a visual confirmation code if a user reports a broken download.<br />This report will then be saved only if the correct code was entered and accordingly Administrators and Download Moderators will be informed with an email.',

	'HELP_DL_RSS_ENABLE'				=> 'Enables the RSS Feed for downloads.<br />If disabled the following two options will define what the user will see instead of the feed.',
	'HELP_DL_RSS_OFF_ACTION'			=> 'With this option the behaviour of the disabled feed is defined.',
	'HELP_DL_RSS_OFF_TEXT'				=> 'This text will be displayed instead of the download entries in the RSS feed if the feed has been disabled and the previous option has been set to display this message. <br />If forwarding has been set up in the previous option this text will remain active but is not displayed.',
	'HELP_DL_RSS_CATS'					=> 'The entries in the RSS feed will be taken from all or selected categories from the list in this option.<br />To select more than one category hold the CRTL key down while you click on the category names.<br />You can choose to include the selected or deselected categories in the feed.',
	'HELP_DL_RSS_PERMS'					=> 'Despite the selection of the categories from which entries should be displayed it is advised to set the permissions of the user to log on or even more closely to that of guests or bots in order to prevent the display of downloads in the feed that the user should not be able to see.<br />In the ´for guests´ setting only those categories that a guest should be able to see are selected.<br />Provided that the user or guest / bot is not shown any feeds due to the selected categories and the access permissions the feed behaves analogously to the settings as if it were switched off.',
	'HELP_DL_RSS_NEW_UPDATE'			=> 'This option marks new or updated downloads like the mini icon in the category view',
	'HELP_DL_RSS_NUMBER'				=> 'The maximum number of downloads displayed in the feed.',
	'HELP_DL_RSS_SELECT'				=> 'This option determines whether the most recent or random downloads should be listed in the feed, depending on the selected categories, access rights and the number.',
	'HELP_DL_RSS_DESC_LENGTH'			=> 'With this option you can display the download descriptions or choose a shortened description (according to the setting for the download index).<br /><br /><strong>Attention:</strong><br />Since not every feed reader recognizes and / or displays HTML codes it can happen that the text is displayed incorrectly or the reader simply does not display any entries. In this case, the user must use a different reader or the descriptions would have to be switched off.',
	'HELP_DL_RSS_DESC_LENGTH_SHORTEN'	=> 'Truncates the description of the downloads after x characters if the description is to be displayed in shortened form (see previous option).<br />If set to 0 the description is not displayed!',

	'HELP_DL_SET_ADD'				=> 'With this option you can select the username under which new downloads will be published.<br />You can select the current user, an user selected by the category settings (if you have selected ´category selection´ here) or another user who is registered in the forum.<br /><br />Please note that the automatically generated download topic in the forum will still use the given user for that function. This option will only change the ´add user´ setting for new downloads.<br /><br /><strong>Hint:</strong><br />The user id will not be checked by the download extension itself, so a nonexisting id can disrupt the functions!',
	'HELP_DL_SHORTEN_EXTERN_LINKS'	=> 'Enter the length of the displayed external download link in the download details.<br />Based on the length of the link it will be cut in the middle or truncated beginning from the right.<br />Leave this field empty or enter 0 to disable this function.',
	'HELP_DL_SHOW_FOOTER_EXT_STATS'	=> 'Shows the overall traffic for registered users and guests and the number of clicks for the current month in the download footer.',
	'HELP_DL_SHOW_FILE_HASH'		=> 'Shows or hides the file hash in the download details.',
	'HELP_DL_SHOW_FOOTER_LEGEND'	=> 'This option will turn on and off the download status icon legend in the download footer.<br />The icons which you can find beside the downloads will not be changed by this option.',
	'HELP_DL_SHOW_FOOTER_STAT'		=> 'This option will turn on and off the mini statistic in the download footer.<br />Even if turned off the statistic will still collect data.',
	'HELP_DL_SHOW_REAL_FILETIME'	=> 'This option displays the real last edit time of the download files in the download details.<br />This is the exactest time code even for files uploaded with a ftp client or updated multiple times without logging this.',
	'HELP_DL_SIMILAR_DL'			=> 'Shows similar downloads from the same category in detail view.<br /><br />Regard: On bigger download databases this option can increase the loading time of the detail view, so this option should be disabled.',
	'HELP_DL_SIMILAR_DL_LIMIT'		=> 'Number of similar downloads which will be listed in the download details page.',
	'HELP_DL_SORT_PREFORM'			=> 'The option `Preset` will sort all downloads in all categories for all users like they are sorted in the ACP.<br />With the option `User` each user can determine how downloads will be sorted for him/her and if this sorting will be fix or expanded with other sort criteria.',
	'HELP_DL_STAT_PERM'				=> 'Select here from which user level on the download statistics can be viewed. <br /> For example, if you only activate Download Moderators, every administrator and download moderator (NOT forum moderator!) can open and view this page. <br />Pleas note that this page can take an extremely long time to load, so it is recommended that you do not open this page to too many user categories if you run a large board and / or provide many downloads.',
	'HELP_DL_STATISTICS'			=> 'Enables detailed statistics about the download files.<br />Note that these statistics will produce additional database queries and datasets in a separate table.',
	'HELP_DL_STATS_PRUNE'			=> 'Enter the number of data rows the statistic for this category can reach.<br />Each new row will than delete the oldest one.<br />Enter 0 here to disable pruning.',
	'HELP_DL_STOP_UPLOADS'			=> 'With this option you can enable or disable uploads.<br />If you disable this option, only administrators will be able to upload new files via the upload form.<br />Enable this option to allow users to upload files depending on the category and group permissions.',

	'HELP_DL_THUMB'						=> 'Using this field you can upload a thumbnail image (note the file size and image dimensions beneath this field) to display it in the download details.<br />If there already exists a thumbnail, you can upload a new one to replace it.<br />If you check the `delete` box of an existing thumbnail it will be deleted.',
	'HELP_DL_THUMB_CAT'					=> 'This option enables thumbnails for downloads in this category.<br />The maximum size of these thumbnails is based on the settings in the main configuration of thiseExtension.',
	'HELP_DL_THUMB_MAX_DIM_X'			=> 'This value limits the possible image width of uploaded thumbnails.<br />Thumbnails are limited to 150 x 100 pixels, you can view the uploaded image in a popup window byu clicking on a thumbnail.<br /><br />Enter 0 to disable thumbnails (not recommended, if the thumbnail file size is set).<br />Existing thumbnails are still displayed unless the thumbnail´s file size was not set to 0.',
	'HELP_DL_THUMB_MAX_DIM_Y'			=> 'This value limits the possible image height of uploaded thumbnails.<br />Thumbnails are limited to 150 x 100 pixels, you can view the uploaded image in a popup window byu clicking on a thumbnail.<br /><br />Enter 0 to disable thumbnails (not recommended, if the thumbnail file size is set).<br />Existing thumbnails are still displayed unless the thumbnail´s file size was not set to 0.',
	'HELP_DL_THUMB_MAX_SIZE'			=> 'Enter 0 as file size to disable thumbnails in all categories.<br />If you enable thumbnails by defining a file size then enter the maximum image size for the uploaded images from which new thumbnails are created.<br />If you disable thumbnails existing thumbnails will no longer be displayed in the download details.',
	'HELP_DL_TODO_LINK'					=> 'Enables or disables the link of the to-do list in the download footer.<br />The to-do data and their management within the download are not affected by this option.',
	'HELP_DL_USE_TODOLIST'				=> 'Enables or disables the to-do list.',
	'HELP_DL_TOPIC_DETAILS'				=> 'Shows the download description, the file name, file size or for external downloads the URL in the forum topic.<br />This text can be placed above or beneath the previously entered text.<br />If the topic will be created via the download category the option in the general configuration will be ignored.',
	'HELP_DL_TOPIC_FORUM'				=> 'The forum which will display all new topics about the downloads.<br />In order to select the forum for download topics per category please select the option `Category select` instead a forum.',
	'HELP_DL_TOPIC_FORUM_C'				=> 'The forum which will display all new topics about the downloads from this category.',
	'HELP_DL_TOPIC_POST_CATNAME'		=> 'Adds the category name into the topic post generated for downloads. The category name will be inserted after the download title.<br />Regard:<br />Existing topics will not be updated unless the respective download will be updated.',
	'HELP_DL_TOPIC_TEXT'				=> 'Free text for creating the topics about the downloads. BBCodes, HTML and smileys are not allowed since the text is indented only to introduce the topic.',
	'HELP_DL_TOPIC_TITLE_CATNAME'		=> 'Adds the category name to the topic title which will be generated for a download. The category name will be separated by `-` from the download name.<br />Regard:<br />Existing topics will not be updated unless the download will be updated.',
	'HELP_DL_TOPIC_TYPE'				=> 'This option selects the topic type for the download topics.<br />After changing this type all new added or edited downloads will be posted with the new topic type. Existing topics will not be changed.',
	'HELP_DL_TOPIC_USER'				=> 'Select here which user will be listed as author of the download topics.<br />If the current user should be the topic author, then select the option `current user`. The option selected by category allows to choose a seperate user for each category. This could be the current user or any user selected by his/her id entered in the field to the right of the drop down field. This is recommended for the option ´Select user over ID´.<br /><br /><strong>Hint:</strong><br />The user id will not be checked by the download extension, so a nonexisting id can disrupt the functions!',
	'HELP_DL_TRAFFIC'					=> 'The maximum of traffic a file will be allowed to produce.<br />A value of 0 deactivates the traffic control.<br />Please note that the file traffic will be set to 0 if the download is marked as external.',
	'HELP_DL_TRAFFIC_OFF'				=> 'Turns the entire traffic management in the download area off and deactivates all subsequent traffic options.<br />Enabling this option will hide all texts about the download traffic in the forum and does not consider any traffic limits. Similarly traffic data is not updated any longer during download and upload.<br />Writing or deleting posts will no longer be credited to user accounts.<br />Automatically assigned traffic bonuses will no longer be allocated to users if this option is turned off. However users or group members can still get allocated traffic within the ACP traffic management module.<br />All ACP modules, texts and functions concerning traffic management will remain unchanged.',
	'HELP_DL_TRAFFICS_FOUNDER'			=> 'If the traffic management is disabled for founders they can still download and upload files without regard to the traffic options.<br />Even if crediting traffic for creating topics or posts is activated these users are exempted from it.<br />This option will freeze the current traffic amount for founders until this option will be disabled.',
	'HELP_DL_TRAFFICS_OVERALL'			=> 'This option limits the overall traffic for registered users.<br />The overall traffic can be enabled or disabled for all registered users or can be set only for members of the user groups selected in the following option.<br />If the overall traffic is disabled all affected users have an unlimited traffic for downloading and uploading files.',
	'HELP_DL_TRAFFICS_OVERALL_GROUPS'	=> 'The previous option will be applied to the user groups and their members selected here if the traffic limit is applied to user groups.',
	'HELP_DL_TRAFFICS_USERS'			=> 'This option limits the traffic for registered users.<br />The user traffic can be enabled or disabled for all registered users or can be set only for members of the user groups which can be selected in the subsequent option.<br />If the user traffic was disabled all affected users have unlimited download and upload traffic.<br />The affected users also will not get automatic traffic amounts credited for writing a topic or post even this option is enabled.',
	'HELP_DL_TRAFFICS_USERS_GROUPS'		=> 'The previous option will be applied to the user groups and their members selected here if the previous option limits the traffic for user groups.',
	'HELP_DL_TRAFFICS_GUESTS'			=> 'Enable or disable the overall traffic for guests.<br />If this option is disabled guests have unlimited traffic for downloading and uploading files based on their permission.<br />Since this option can cause a heavy traffic load for your forum and your server up to a complete congestion it is not recommended to disable this option.',

	'HELP_DL_UPLOAD_FILE'			=> 'The file to be uploaded from your computer.<br />Be sure that the file size ise smaller than the shown limit and that the file´s extension is not included in the list you can see underneath this field.',
	'HELP_DL_UPLOAD_TRAFFIC_COUNT'	=> 'If this option is enabled uploads will decrease the monthly overall traffic.<br />After reaching the overall limit no more upload will be possible and new files must be uploaded with a ftp client and added in the ACP.',
	'HELP_DL_USE_EXT_BLACKLIST'		=> 'If you enable the blacklist all entered file types will be blocked for new uploading or editing downloads.',
	'HELP_DL_USE_HACKLIST'			=> 'This option enables or disables the hack list.<br />If enabled you can enter hack information while adding or editing a download to insert the download into the hack list.<br />If disabled the hack list will be completely hidden for each user but you can enable it any time.<br />Please note that all hack information in the downloads will be lost if you edit the file after the hack list was disabled.',
	'HELP_DL_USER_TRAFFIC_ONCE'		=> 'Select if downloads should decrease the user traffic only once at downloading a file for the first time.<br /><strong>Regard:</strong><br />This option will NOT change the download status itself!',

	'HELP_DL_VISUAL_CONFIRMATION'	=> 'Activate this option to prompt users to enter a 5-digit confirmation code prior to downloading a file. <br />If the user has submitted an invalid or no code the extension will display a message instead of releasing the download.<br />If this option is deactivated the user will not have to enter a code and can download the files directly from the details page.',

	'HELP_NUMBER_RECENT_DL_ON_PORTAL'	=> 'The number of latest downloads the user will see on the portal.<br />The extension uses the last edit time for this list so it is possible to have an older download on top of this list.',
]);
