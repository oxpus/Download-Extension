/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2015-2020 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

$('.dlTooltip').hover(
	function() {cat = $(this).attr("id"); $('#desc_' + cat).show();},
	function() {cat = $(this).attr("id"); $('#desc_' + cat).hide();}
);
