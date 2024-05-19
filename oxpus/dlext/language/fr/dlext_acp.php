<?php

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2024 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/*
* [ french ] language file for Download Extension
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
	'DL_LIMIT_TITLE_SHOW'				=> 'Afficher les limites actuelles',
	'DL_LIMIT_TITLE_HIDE'				=> 'Fixer des limites',
	'DL_PHP_LIMITS'						=> 'Paramètres en PHP',
	'DL_CUR_LIMITS'						=> 'Paramètres dans l’extension de téléchargement',

	'DL_PHP_INI_EXPLAIN'				=> 'Les paramètres PHP peuvent être modifiés dans le fichier <strong>%1$s</strong> ou dans l’un des fichiers de configuration inclus ; voir les informations PHP.',

	'DL_LIMIT_PHP_FILE_UPLOAD'			=> 'file_upload',
	'DL_LIMIT_PHP_MAX_FILE_UPLOAD'		=> 'max_file_upload',
	'DL_LIMIT_PHP_MAX_INPUT_TIME'		=> 'max_input_time',
	'DL_LIMIT_PHP_MAX_EXECUTION_TIME'	=> 'max_execution_time',
	'DL_LIMIT_PHP_MEMORY_LIMIT'			=> 'memory_limit',
	'DL_LIMIT_PHP_POST_MAX_SIZE'		=> 'post_max_size',
	'DL_LIMIT_PHP_UPLOAD_MAX_FILESIZE'	=> 'upload_max_filesize',

	'DL_LIMIT_TOTAL_REMAIN'				=> 'Espace restant pour tous les fichiers téléchargés',
	'DL_LIMIT_THUMBNAIL_XY_SIZE'		=> 'Dimensions maximales des miniatures téléchargées',
	'DL_LIMIT_THUMBNAIL_XYSIZE'			=> '%1$s x %2$s Pixels',

	'DL_LIMIT_PHP_FILE_UPLOAD_EXPLAIN'			=> 'Par défaut = 1 (Activé)<br />Permet à PHP de traiter les fichiers téléchargés.<br />Sinon, ces fichiers ne seront pas disponibles pour PHP.',
	'DL_LIMIT_PHP_MAX_FILE_UPLOAD_EXPLAIN'		=> 'Par défaut = 20, recommandation >= 10<br />Lime le nombre de fichiers téléchargés simultanément que PHP peut traiter.',
	'DL_LIMIT_PHP_MAX_INPUT_TIME_EXPLAIN'		=> 'Par défaut = -1 (non actif)<br />Durée de traitement maximale des données POST et GET en secondes.<br />La période commence avec le démarrage de PHP et se termine avec le démarrage du premier script PHP.',
	'DL_LIMIT_PHP_MAX_EXECUTION_TIME_EXPLAIN'	=> 'Par défaut = 30 secondes<br />Durée maximale d’exécution d’un script PHP depuis le début de PHP jusqu’à la fin d’un script à exécuter.<br />Passé ce délai, PHP arrête le traitement sauf si le script s’est arrêté prématurément.',
	'DL_LIMIT_PHP_MEMORY_LIMIT_EXPLAIN'			=> 'Par défaut = 128 MB (dans les versions PHP plus modernes)<br />Limite la mémoire principale du serveur, que PHP est autorisé à utiliser.<br />Doit être augmentée pour correspondre aux fichiers téléchargés utilisés.<br />C’est fortement recommandé pour utiliser la mémoire RAM disponible. Ne dépassez pas la limite de mémoire RAM du serveur.',
	'DL_LIMIT_PHP_POST_MAX_SIZE_EXPLAIN'		=> 'Par défaut = 8 MB<br />Consommation de mémoire maximale pour un flux de téléchargement HTTP(S) / formulaire HTML.<br />Limitée par la limite sous memory_limit.<br />Doit être augmentée lorsque des fichiers plus volumineux sont mis à disposition pour le téléchargement.',
	'DL_LIMIT_PHP_UPLOAD_MAX_FILESIZE_EXPLAIN'	=> 'Par défaut = 2 MB<br />Taille maximale que PHP est autorisé à traiter par fichier après la soumission d’un formulaire HTML.<br />Les fichiers plus volumineux ne seront pas disponibles pour PHP.<br />Doit être augmenté si des fichiers plus volumineux sont utilisés doivent être disponibles en téléchargement.<br /><strong>Attention :</strong><br />Cette limite compte par fichier téléchargé. Si plusieurs fichiers doivent être téléchargés ensemble, la limite par fichier est multipliée et globalement limitée par la limite post_max_size.',

	'DL_LIMIT_TRAFFIC_USER_REMAIN_EXPLAIN'		=> 'Le trafic de téléchargement actuellement disponible pour tous les utilisateurs enregistrés au cours du mois en cours.',
	'DL_LIMIT_TRAFFIC_GUESTS_REMAIN_EXPLAIN'	=> 'Le trafic de téléchargement actuellement disponible pour tous les invités au cours du mois en cours.',
	'DL_LIMIT_TOTAL_REMAIN_EXPLAIN'				=> 'L’espace de stockage maximum fourni pour tous les fichiers qui doivent être proposés au téléchargement.<br /><strong>Important :</strong><br />Les miniatures et les versions de fichiers sont exclues de cette limite !<br /><strong> Attention :</strong><br />Cette limite ne doit pas atteindre ni même dépasser l’espace de stockage physiquement disponible du serveur, sinon le serveur pourrait tomber en panne par manque de mémoire !<br />Il est également important de s’assurer que le Les tailles de répertoire des vignettes et des versions de fichiers doivent être prises en compte pour cette limite physique.',
	'DL_LIMIT_THUMBNAIL_SIZE_EXPLAIN'			=> 'Les miniatures téléchargées avec une taille de fichier plus grande seront rejetées et ne seront pas incluses dans les téléchargements.',
	'DL_LIMIT_THUMBNAIL_XY_SIZE_EXPLAIN'		=> 'Dimensions maximales en pixels pour la largeur et la hauteur de tous les fichiers miniatures téléchargés.<br />Les fichiers image plus volumineux seront rejetés et acceptés.',
	'DL_LIMIT_THUMBNAIL_XYSIZE_EXPLAIN'			=> '%1$s x %2$s Pixel',
]);
