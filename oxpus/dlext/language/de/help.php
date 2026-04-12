<?PHP

/**
 *
 * @package   phpBB Extension - Oxpus Downloads
 * @copyright 2002-2021 OXPUS - www.oxpus.net
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/*
* [ german ] language file for Download Extension
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

/*
* no help found?
*/
$lang = array_merge($lang, [
	'HELP_TITLE'				=> 'Download Extension Online Hilfe',

	'DL_NO_HELP_AVAILABLE'		=> 'Für dieses Thema steht keine Hilfe zur Verfügung.',

	'HELP_DL_ACTIVE'			=> 'Schaltet den Download Bereich in Abhängigkeit der nachfolgenden Optionen ein oder aus.',
	'HELP_DL_APPROVE'			=> 'Diese Einstellung gibt den Download sofort frei, wenn Du dieses Formular absendest.<br>Andernfalls wird dieser Downloads vor den Benutzern versteckt, bis ein Administrator oder Download Moderator den Download freigibt.',
	'HELP_DL_APPROVE_COMMENTS'	=> 'Wenn Du diese Option deaktivierst, müssen neue Kommentare zunächst von einem Download Moderator oder Administrator freigegeben werden, bevor diese angezeigt werden.',


	'HELP_DL_CAT_DESCRIPTION'	=> 'Eine kurze Beschreibung für diese Kategorie.<br>BBCodes sind hier nur verfügbar, wenn die Beschreibungen auf dem Index immer angezeigt werden sollen.<br>Diese Beschreibung wird auf dem Downloads Index und in den Subkategorien angezeigt.',
	'HELP_DL_CAT_EDIT_LINK'		=> 'Bestimmt, wer den Link zum Bearbeiten eines Downloads in der Kategorieansicht sehen darf, sofern diese Option nicht deaktiviert wird.<br>´Eigene Downloads´ ist hierbei nur aktiv, wenn die Option für das Bearbeiten der eigenen Downloads ebenfalls aktiv ist.',
	'HELP_DL_CAT_ICON'			=> 'Das Icon für die Kategorie muss ein Icon sein, welches bereits in das Forum hochgeladen wurde. Z. B. unter /images/dl_icons/ (Dieser Ordner wäre dann zunächst anzulegen).<br>Dazu ist die relative URL ab dem Forum-Root anzugeben, also z. B. images/dl_icon.gif.<br><br>Verwendet werden dürfen dabei alle Bildtypen, die Webbrowser darstellen können.<br>Empfohlen wird an dieser Stelle JPG, GIF oder PNG.<br>Bedenke auch dabei die Größe der Icons, um das Layout auf dem Download Index nicht all zu sehr zu zerstören, da die Icons in der Größe nicht umgewandelt werden.',
	'HELP_DL_CAT_NAME'			=> 'Dies ist der Name der Kategorie, der überall angezeigt wird.<br>Verwende möglichst keine Sonderzeichen, um keine schwerer zu lesenden Einträge in der Jumpbox zu generieren.',
	'HELP_DL_CAT_PARENT'		=> 'Die oberste Ebene oder eine Kategorie, der diese Kategorie zugordnet werden kann.<br>Mit dieser dynamischen Auswahlliste können hierarchische Strukturen für Deine Downloads erstellt werden.',
	'HELP_DL_CAT_PATH'			=> 'Hier musst Du einen existierenden Pfad für Deine Downloads angeben.<br>Diese Angabe ist der Name eines Subordners unterhalb des Hauptordners (z. B. downloads/), den Du in der allgemeinen Konfiguration angegeben hast.<br>Trage hier nur den Ordnernamen des Subordners mit einem Slash am Ende ein.<br>Zum Beispiel existiert der Ordner ´downloads/mods/´ den Du als Kategoriepfad ´mods/´ angeben mußt.<br>Wenn Du dieses Formular absendest, wird der Ordner überprüft. Stelle daher wirklich sicher, daß der Subordner bereits existiert!<br>Wenn der Subordner unterhalb eines anderen Subordners vorhanden ist, dann gib den kompletten Pfad der Hierarchie an.<br>Z. B. wird dann aus ´downloads/mods/misc/´ der Kategoriepfad ´mods/misc/´.<br>Versichere Dich, daß jeder Subordner die Zugriffsrechte CHMOD 777 besitzt und achte auf Groß- und Kleinschreibung der Ordnernamen, wenn Du Unix/Linux verwendest.',
	'HELP_DL_CAT_RULES'			=> 'Diese Regeln werden während der Kategorieansicht oberhalb der Unterkategorien und Downloads angezeigt.',
	'HELP_DL_CHOOSE_CATEGORY'	=> 'Wähle die Kategorie, die den Download beinhalten soll.<br>Die Datei muss sich bereits in dem Ordner befinden, den Du in der Kategorieverwaltung für die gewählte Kategorie angeben hast, bevor Du diesen Download speicherst.<br>Andernfalls bekommst Du eine Fehlermeldung.',
	'HELP_DL_COMMENTS'			=> 'Aktiviert das Kommentarsystem für diese Kategorie.<br>Benutzer die Du gemäß den nachfolgenden Drop Downs zulässt, können Kommentare ansehen und/oder verfassen.<br>Administratoren und Download Moderatoren können alle Kommentare löschen und bearbeiten, die Autoren dagegen nur ihren eigenen Texte bearbeiten.',
	'HELP_DL_COPY_PERMISSIONS'	=> 'Kopiert die Berechtigungen von der angegebenen Kategorie.<br>Wenn die übergeordnete Kategorie angegeben wird, so erhält diese Kategorie die Berechtigungseinstellungen der zuvor angegebenen Kategorie, unter der diese Kategorie eingebunden wird.<br>Ist die zuvor gewählte übergeordnete Kategorie die oberste Ebene, so werden keine Berechtigungen gesetzt. In diesem Fall dann entweder eine andere Kategorie auswählen oder die Berechtigungen für diese Kategorie mit dem Modul Berechtigungen einstellen.',

	'HELP_DL_DISABLE_NOTIFY'		=> 'Mit dieser Option kannst Du die Benachrichtigungen für neue, bzw. geänderte Downloads ein- oder komplett abschalten.<br>Ist diese Funktion eingeschaltet, kann beim Bearbeiten eines Downloads dieses individuell abgeschaltet werden.<br>Benachrichtigt werden bei aktivierter Funktion jedoch nur die User, die über neue, bzw. geänderte Downloads eine Benachrichtigung erhalten wollen.',
	'HELP_DL_DISABLE_POPUP_NOTIFY'	=> 'Wird diese Option eingeschaltet, kann beim Bearbeiten eines Downloads das Protokolieren der Bearbeitungszeit unterbunden werden.',

	'HELP_DL_EDIT_OWN_DOWNLOADS'	=> 'Wenn diese Option eingeschaltet wird, kann jeder Benutzer seine eigenen hochgeladenen Dateien bearbeiten, ohne selber Administrator oder Download Moderator zu sein.<br>Löschen, verschieben und sperren kann die Downloads dann aber weiterhin nur ein Administrator oder Download Moderator.',
	'HELP_DL_EDIT_TIME'				=> 'Trage hier die Anzahl Tage ein, die ein geänderter Download markiert bleibt.<br>Gib 0 ein, um diese Funktion abzuschalten.',
	'HELP_DL_ENABLE_INDEX_DESC'		=> 'Versteckt die Beschreibung der Downloads in der Kategorieansicht.<br>Wird diese Option deaktiviert, kann mit der nachfolgenden Option die Länge der angezeigten Beschreibung festgelegt werden.',
	'HELP_DL_ENABLE_JUMPBOX'		=> 'Diese Option lässt die Jumpbox im Fuß des Download Bereiches anzeigen oder verstecken.<br>Wenn die Jumpbox abgeschaltet wird, verbessert das ein Stück weit die Performance des Download Bereiches.',
	'HELP_DL_ENABLE_RATE'			=> 'Hiermit schaltest du das Bewertungssystem ein oder aus.<br>Sofern bereits Bewertungen vorliegen, werden diese nicht entfernt und sofort wieder angezeigt, wenn du das Bewertungssystem wieder aktivierst.',
	'HELP_DL_ENABLE_SEARCH_DESC'	=> 'Versteckt die Beschreibung der Downloads in den Suchergebnissen.<br>Wird diese Option deaktiviert, kann mit der nachfolgenden Option die Länge der angezeigten Beschreibung festgelegt werden.',
	'HELP_DL_ENABLE_TOPIC'			=> 'Ermöglicht es, bei jedem neuen Download, der hochgeladen oder im Admininstration Bereich eingestellt wurde, ein neues Thema in dem nachfolgend eingestellten Forum mit dem angegebenen Text zu erstellen.<br>Wird ein Download nicht sofort freigegeben, so wird das Thema erst mit Freigabe des Download über das Moderationspanel erstellt.',
	'HELP_DL_EXT_NEW_WINDOW'		=> 'Öffnet externe Downloads entweder in einem neuen Browserfenster oder lädt diese im im aktuellen Fenster.',
	'HELP_DL_EXTERN'				=> 'Aktiviere diese Funktion, wenn du in der obigen Zeile eine URL ausserhalb Deines Servers angeben willst (http://www.beispiel.de/media.mp3).<br>Die Funktion ´frei´ wird dann bedeutungslos.<br>Optional kannst du hier auch die Dateigröße des externen Downloads eingeben. Diese erscheint dann in allen Ansichten und kann jederzeit geändert werden.<br>Beachte, dass die Dateigröße hier auch angezeigt wird, wenn der Download nicht als extern markiert wird. Dann wird die Änderung an diesem Wert ignoriert und durch die tatsächliche Dateigröße ersetzt.',
	'HELP_DL_EXTERN_UP'				=> 'Aktiviere diese Funktion, wenn du in dem nachfolgenden Feld eine URL ausserhalb Deines Servers angeben willst (http://www.beispiel.de/media.mp3).<br>Die Funktion ´frei´ wird dann bedeutungslos.',

	'HELP_DL_FILE_DESCRIPTION'	=> 'Eine kurze Beschreibung des Downloads.<br>Diese wird auch in der Kategorie angezeigt.<br>BBCodes sind für diesen Text nicht verfügbar.<br>Bitte verfasst einen möglichst kurzen Text, um beim Öffnen der Kategorie die Ladezeit gering zu halten.',
	'HELP_DL_FILE_EDIT_HINT'	=> 'Ermöglicht einen Hinweistest beim Hinzufügen oder Bearbeiten eines Downloads. Dieser Text wird deutlich sichtbar am Anfang des Formulars angezeigt.<br>BBCodes sind hierbei möglich.',
	'HELP_DL_FILE_HASH_ALGO'	=> 'Legt fest, mit welcher Methode der Hash der einzelnen Downloads berechnet werden soll.<br>Der Hash-Wert wird dabei für jeden Download und alle vorhandenen Varianten berechnet, jedoch nur in den Download Details angezeigt, wenn dieses in den betreffenden Kategorien aktiviert wurde.<br>Zur Verfügung stehen für das Bilden des Hash-Wertes md5 und sha1, da diese auf den meisten Systemen im Standard aktiv sind.<br>Die Extension ermittelt den Hash-Wert automatisch, wenn ein Download neu hinzugefügt oder geändert wurde. Ebenso wird der Hash-Wert ermittelt, wenn die Detailansicht eines Downloads aufgerufen, aber noch kein Hash-Wert gespeichert wurde. Das ist besonders für aktualisierte MODs oder das Ändern der Hash-Methode gedacht.<br><br><strong>Hinweis:</strong><br>Wenn die Hash-Methode geändert wird, werden alle bestehenden Hash-Werte zunächst gelöscht, da diese dann nicht mehr zur gewählten Methode passen!',
	'HELP_DL_FILES_EXTERN'		=> 'URL einer externen Datei',
	'HELP_DL_FILES_INTERN'		=> 'Der Dateiname des Downloads.<br>Gib ihn ohne Pfadangaben und ohne führenden Slash an.<br>Die Datei muss vor dem Speichern bereits im Ordner der Kategorie existieren, sonst wird eine Fehlermeldung angezeigt.<br>Beachte auch verbotene Dateiendungen: Dateien, die hierzu zählen, werden abgewiesen.',

	'HELP_DL_GLOBAL_BOTS'		=> 'Diese Option erlaubt oder verhindert den grundlegenden Zugriff auf den Download Bereich für Bots.<br>Alle anderen Zugriffsrechte bleiben hiervon unberührt.',
	'HELP_DL_GLOBAL_GUESTS'		=> 'Diese Option erlaubt oder verhindert den grundlegenden Zugriff auf den Download Bereich für Gäste.<br>Alle anderen Zugriffsrechte bleiben hiervon unberührt.',
	'HELP_DL_GUEST_STATS_SHOW'	=> 'Diese Option inkludiert oder exkludiert die Statistikdaten über Gastaktivitäten in den öffentlichen Kategoriestatistiken.<br>Das Script blendet die Daten dabei nur aus, erhebt aber weiterhin alle Daten.<br>Das ACP Statistiktool zeigt dazu immer die kompletten Statistikdaten an.',

	'HELP_DL_HACK_AUTOR'			=> 'Der Autor der Download Datei.<br>Lasse ihn frei, um die Angabe in den Download Details und der Gesamtübersicht auszusparen.',
	'HELP_DL_HACK_AUTOR_EMAIL'		=> 'E-Mail-Adresse des Autoren.<br>Wird diese nicht angegeben, lässt die Extension diese in den Details und in der Gesamtübersicht ebenfalls aus.',
	'HELP_DL_HACK_AUTOR_WEBSITE'	=> 'Webseite des Autoren.<br>Diese URL sollte auf die Webseite, nicht auf die Seite des Downloads verweisen (sind nicht immer die gleichen).<br>Bitte verlinke keine geschützten Seiten oder Seiten mit fragwürdigen Inhalten.',
	'HELP_DL_HACK_DL_URL'			=> 'Die Seite zum alternativen Download der Datei.<br>Dieses kann die Seite des Autoren sein oder eine andere Alternative sein.<br>Bitte keine Dateien direkt verlinken, wenn der Autor das ausdrücklich untersagt.',
	'HELP_DL_HACK_VERSION'			=> 'Angabe über die Version des Downloads.<br>Diese wird nur bei den Downloads angezeigt.<br>Es kann nicht danach gesucht werden.',
	'HELP_DL_HOTLINK_ACTION'		=> 'Hier wird eingestellt, wie sich das Download Script verhalten soll, wenn ein Direktlink zum Download verhindert wurde (siehe letzte Option).<br>Es wird dann entweder eine Meldung angezeigt (verringert die Serverlast) oder zum Download weitergeleitet (erzeugt zusätzlichen Traffic).',

	'HELP_DL_ICON_FREE_FOR_REG'		=> 'Diese Option schaltet das weiße Download Icon (freier Download für registrierte Benutzer) ebenfalls für Gäste auf weiß.<br>Wenn Du diese Option deaktivierst, sehen Gäste hier das rote Icon anstelle des Weissen.',
	'HELP_DL_INDEX_DESC_HIDE'		=> 'Versteckt die Kategoriebeschreibungen auf dem Index und in Subkategorien.<br>Die Beschreibungen werden dann eingeblendet, wenn man mit der Maus über die jeweilige Kategoriezeile fährt.',
	'HELP_DL_IS_FREE'				=> 'Aktiviere diese Funktion, wenn der Download unabhängig des Kontos für alle Benutzer möglich sein soll.<br>Wähle frei für reg. Benutzer, um nur registrierten Benutzern einen freien Download zu ermöglichen.',

	'HELP_DL_KLICKS_RESET'			=> 'Hiermit können die Klicks für den aktuellen Monat wieder auf 0 gesetzt werden.<br>Diese Option ist dann sinnvoll, wenn man die Downloads für neue Dateiversionen sofort überwachen will.',

	'HELP_DL_LATEST_COMMENTS'		=> 'Zeigt die letzten X Kommentare in den Download Details. 0 schaltete diesen Block aus.',
	'HELP_DL_LATEST_DOWNLOADS'		=> 'Bestimmt, ob diese Liste deaktiviert wird, alle Downloads anzeigt (das entspricht der Gesamtübersicht, nur nach Alter absteigend sortiert) oder die tatsächlich zuletzt hinzugefügten oder geänderten Downloads anzeigt.',
	'HELP_DL_LIMIT_DESC_ON_INDEX'	=> 'Schneidet die Download Beschreibungen in den Kategorien nach der hier angegebene Anzahl Zeichen ab.<br>Gib 0 an, um diese Funktion zu deaktivieren.',
	'HELP_DL_LIMIT_DESC_ON_SEARCH'	=> 'Schneidet die Download Beschreibungen in den Suchergebnissen nach der hier angegebene Anzahl Zeichen ab.<br>Gib 0 an, um diese Funktion zu deaktivieren.',
	'HELP_DL_LINKS_PER_PAGE'		=> 'Hiermit wird eingestellt, wieviele Downloads in den Kategorien und der ACP Statistik je angezeigter Seite aufgelistet werden.<br>In der Hackliste und in der Gesamtübersicht wird hierzu die Boardeinstellung ´Themen je Seite´ verwendet.',

	'HELP_DL_MOD_DESC'			=> 'Ausführliche Beschreibung zu der hier eingetragenen Extension.<br>In der Beschreibung können BBCodes und Smilies verwendet werden, Zeilenumbrüche werden ebenfalls berücksichtigt.<br>Diese Angaben werden nur in den Download Details angezeigt.',
	'HELP_DL_MOD_DESC_ALLOW'	=> 'Aktiviert einen Block für MOD/Extension Informationen während dem Hinzufügen oder Bearbeiten von Downloads.',
	'HELP_DL_MOD_LIST'			=> 'Aktiviert diesen Block in den Download Details.<br>Wenn diese Option nicht gewählt ist, wird der gesamte Block in den Download Details ausgeblendet.',
	'HELP_DL_MOD_REQUIRE'		=> 'Angaben, welche weiteren MODs/Extensions dieser Download benötigt, um installiert oder benutzt werden zu können.<br>Diese Angaben werden nur in den Download Details angezeigt.',
	'HELP_DL_MOD_TEST'			=> 'Angabe zur Testumgebung dieses Downloads.<br>Hiermit ist die Forenversion gemeint.<br>Umgesetzt wird dieses als phpBB X, wobei X hier anzugeben wäre.<br>Diese Angaben werden nur in den Download Details angezeigt.',
	'HELP_DL_MOD_TODO'			=> 'Hier können die nächsten Tätigkeiten an der MOD/Extension angegeben werden, die geplant sind oder aktuell anstehen.<br>Aus diesen Angaben wird die ToDo Liste erstellt, die im Fußbereich der Download aufgerufen werden kann.<br>Mit diesen Angaben kann man anderen Usern den Stand der eigenen MOD/Extension aufzeigen.<br>Zeilenumbrüche werden hierbei berücksichtigt, BBCodes sind hier nicht verfügbar.<br>Die ToDo-Liste wird auch dann mit diesen Angaben versorgt, wenn der MOD/Extension Block nicht aktiviert wurde.',
	'HELP_DL_MOD_WARNING'		=> 'Wichtige Hinweise zur MOD/Extension, die unbedingt bei der Installation, Benutzung oder im Zusammenspiel mit anderen MODs/Extensions zu beachten sind.<br>Dieser Text wird farbig hervorgehoben in den Download Details angezeigt (im Original mit roter Schrift).<br>Zeilenumbrüche werden hierbei berücksichtigt, BBCodes sind hier nicht verfügbar.',
	'HELP_DL_MUST_APPROVE'		=> 'Aktiviere diese Option, um neu hochgeladene Download Dateien freizugeben, bevor sie in dieser Kategorie angezeigt werden.<br>Administratoren und Download Moderatoren erhalten über jeden neuen nicht freigegebenen Download eine E-Mail.',

	'HELP_DL_NAME'					=> 'Dies ist der Name des Downloads, der überall angezeigt wird.<br>Verwende möglichst keine Sonderzeichen, um Fehler bei der Darstellung zu vermeiden.',
	'HELP_DL_NEW_TIME'				=> 'Trage hier die Anzahl Tage ein, die ein Download nach dem hinzufügen als neu markiert bleibt.<br>Trage 0 ein, um diese Funktion abzuschalten.',
	'HELP_DL_NO_CHANGE_EDIT_TIME'	=> 'Wähle diese Option, um die Aktualisierung der Bearbeitungszeit zu unterdrücken.<br>Dieses betrifft nicht die E-Mail und Popup Benachrichtigung/Board Nachricht.',

	'HELP_DL_OFF_HIDE'					=> 'Schaltet den Link in der Boardnavigation ab, um den Download Bereich zu verstecken.<br>Andernfalls wird beim Aufruf des Download Bereiches eine Meldung angezeigt.',
	'HELP_DL_OFF_NOW_TIME'				=> 'Schaltet die Download Extension sofort ab oder immer zwischen den nachfolgend eingestellten Uhrzeiten.',
	'HELP_DL_OFF_PERIOD'				=> 'Zeitspanne, in der der Download Bereich automatisch deaktiviert wird.',
	'HELP_DL_OFF_PERIOD_TILL'			=> 'Zeitspanne, in der der Download Bereich automatisch deaktiviert wird.',
	'HELP_DL_ON_ADMINS'					=> 'Erlaubt den Board-Administratoren weiterhin den Download Bereich zu betreten und darin zu arbeiten, auch wenn dieser deaktiviert ist.<br>Andernfalls werden auch diese Benutzer ausgesperrt.',
	'HELP_DL_OVERVIEW_LINK'				=> 'Zeigt den Link zur Gesamtübersicht an oder versteckt ihn.<br>Hinweis:<br>Wenn der Link versteckt wird, ist auch ein direkter Aufruf der Gesamtübersicht nicht mehr möglich!',

	'HELP_DL_PHYSICAL_QUOTA'	=> 'Das gesamte physische Limit, die die Extension zum Speichern und Verwalten der Downloads verwenden darf.<br>Wenn dieses Limit erreicht ist, können neue Download nur noch hinzugefügt werden, wenn sie per FTP Client hochgeladen und im ACP mit der Dateiverwaltung hinzugefügt werden.',
	'HELP_DL_PREVENT_HOTLINK'	=> 'Aktiviere diese Option, wenn Du Links zum direkten Download ausser aus den Download Details unterbinden willst.<br>Diese Option richtet <strong>keinen</strong> Verzeichnisschutz ein!',

	'HELP_DL_RATE_POINTS'			=> 'Legt die Anzahl an Bewertungspunkten fest, die ein Benutzer einem Download maximal geben kann.<br><br><strong>Wichtig:</strong><br>Wenn du diese Einstellung änderst, werden alle bestehenden Bewertungen gelöscht, damit die Extension korrekte Bewertungspunkte errechnen kann!',
	'HELP_DL_REPORT_BROKEN'			=> 'Schalte die Möglichkeit an oder aus, defekte Downloads zu melden.<br>Wenn Du dieses auf ´nicht für Gäste´ einstellst, können nur registrierte Benutzer defekte Downloads melden.',
	'HELP_DL_REPORT_BROKEN_LOCK'	=> 'Wenn diese Option aktiv ist, wird der Download gesperrt, solange er als defekt gemeldet gilt.<br>Dabei wird der Download Button versteckt und kein Benutzer kann diese Datei herunterladen, bis sie von einem Administrator oder Download Moderator wieder entsperrt wurde.',
	'HELP_DL_REPORT_BROKEN_MESSAGE'	=> 'Wenn ein Download als defekt gemeldet wurde, erscheint eine entsprechende Nachricht.<br>Ist diese Option aktiviert, erscheint diese Nachricht nur, wenn der Download auch gleichzeitig gesperrt wurde.<br>In dem Fall dann nicht unter, sondern anstelle des Download Buttons.',
	'HELP_DL_REPORT_BROKEN_VC'		=> 'Aktiviert die visuelle Bestätigung, wenn ein Benutzer einen Download als defekt melden will.<br>Nur, wenn der Code dann korrekt angegeben wurde, wird die Meldung gespeichert und den Administratoren, bzw. Download Moderatoren eine Nachricht hierzu gesendet.',

	'HELP_DL_RSS_ENABLE'				=> 'Der RSS Feed für die Downloads kann komplett ein- oder auch ausgeschaltet werden.<br>Wenn diese Funktion abgeschaltet wird, kann mit den nachfolgenden beiden Optionen bestimmt werden, was der Anwender stattdessen erhält oder sieht.',
	'HELP_DL_RSS_OFF_ACTION'			=> 'Mit dieser Option wird bestimmt, wie sich der RSS Feed verhält, wenn er abgeschaltet wurde.',
	'HELP_DL_RSS_OFF_TEXT'				=> 'Dieser Text wird anstelle der Download Einträge im RSS Feed angezeigt, wenn die Funktion abgeschaltet wurde und die vorherige Option entsprechend auf das Anzeigen dieser Nachricht eingestellt wurde.<br>Sollte in der vorherigen Option eine Weiterleitung eingerichtet worden sein, so wird dieser Text weiterhin aktiv bleiben, jedoch nicht angezeigt.',
	'HELP_DL_RSS_CATS'					=> 'Die Einträge im RSS Feed werden aus allen oder aus den in der angezeigten Liste ausgewählten Kategorien angezeigt.<br>Um mehrere Kategorien auszuwählen, bitte die STRG-Taste gedrückt halten.<br>Es kann hierbei unterschieden werden, ob die ausgewählten oder nicht ausgewählten Kategorien für den Feed herangezogen werden sollen.',
	'HELP_DL_RSS_PERMS'					=> 'Trotz der Auswahl, aus welchen Kategorien Einträge angezeigt werden sollen, kann es ratsam sein, die Berechtigungen des Anwenders auf dessen Anmeldung oder noch enger auf die für Gäste, bzw. Bots einzustellen, um keine Downloads im Feed anzuzeigen, die der Anwender nicht sehen dürfte.<br>In der Einstellung ´für Gäste´ werden nur die Kategorien ausgewählt, die ein Gast auch einsehen dürfte.<br>Sofern dem Anwender, bzw. Gast/Bot aufgrund der ausgewählten Kategorien und der eingestellten Zugriffsrechte keine Feeds dargestellt werden können, verhält sich der Feed analog den Einstellungen, als wäre er ausgeschaltet.',
	'HELP_DL_RSS_NEW_UPDATE'			=> 'Diese Option markiert neue und geänderte Downloads wie auch in der Kategorie das Mini Symbol',
	'HELP_DL_RSS_NUMBER'				=> 'Anzahl der Downloads, die im Feed maximal angezeigt werden.',
	'HELP_DL_RSS_SELECT'				=> 'Diese Option legt fest, ob die letzten oder zufällig gewählte Downloads im Feed angezeigt werden sollen, abhängig von den gewählten Kategorien, der Zugriffsrechte und der Anzahl.',
	'HELP_DL_RSS_DESC_LENGTH'			=> 'Mit dieser Option kann man die Beschreibungen der Downloads mit anzeigen lassen, bzw. eine verkürzte Darstellung (gemäß der Einstellung für den Download Index) wählen.<br><br><strong>Achtung:</strong><br>Da nicht jeder Feed-Reader auch HTML-Codes erkennt und/oder darstellt, kann es passieren, dass der Text fehlerhaft dargestellt wird oder der Reader schlicht gar keine Einträge anzeigt. In diesem Fall muss der Anwender einen anderen Reader verwenden oder die Beschreibungen müssten abgeschalten werden.',
	'HELP_DL_RSS_DESC_LENGTH_SHORTEN'	=> 'Schneidet die Beschreibung der Downloads nach x Zeichen ab, wenn die Beschreibung verkürzt angezeigt werden soll (siehe vorherige Option).<br>Bei 0 wird die Beschreibung nicht angezeigt!',

	'HELP_DL_SET_ADD'				=> 'Hiermit kann gewählt werden, unter welchem Benutzer neue Downloads veröffentlicht werden sollen.<br>Das kann der jeweils aktuelle Benutzer sein, ein Benutzer je Downloads Kategorie (hier ist ´Kategorieauswahl´ anzugeben) oder jeder beliebige andere Benutzer, der im Forum registriert ist.<br><br>Bitte beachte, dass ein automatisch erstelltes Thema für den Downloads weiterhin auf den dafür eingestellten Benutzer im Forum gepostet wird. Diese Option ändert lediglich den Benutzer, der den Download ´hochgeladen´ hat.<br><br><strong>Hinweis:</strong><br>Die Benutzer-ID wird durch die Download Extension nicht geprüft. Daher kann eine nicht existierende User-ID zu unerwarteten Störungen führen!',
	'HELP_DL_SHORTEN_EXTERN_LINKS'	=> 'Gib die Länge des angezeigten externen Download Links an.<br>Je nach Länge wird der Link entweder in der Mitte oder von rechts beginnend gekürzt.<br>Lass dieses Feld leer oder gib 0 ein, um diese Funktion abzuschalten.',
	'HELP_DL_SHOW_FOOTER_EXT_STATS'	=> 'Zeigt im Download Fuß zusätzlich für die eingestellte Benutzergruppe den voreingestellten Gesamttraffic für registrierte Benutzer und Gäste sowie die Anzahl Klicks im aktuellen Monat an.',
	'HELP_DL_SHOW_FILE_HASH'		=> 'Zeigt den File-Hash in den Download Details an oder versteckt ihn.',
	'HELP_DL_SHOW_FOOTER_LEGEND'	=> 'Diese Option schaltet die Legende mit den Icons im Fußbereich der Downloads ein oder aus.<br>Die Icons bei den Downloads selber werden dadurch nicht beinflusst.',
	'HELP_DL_SHOW_FOOTER_STAT'		=> 'Diese Option schaltet die Ministatistik im Fußbereich der Download Extension ein und aus.<br>Die Statistik wird weiterhin Daten sammeln, selbst wenn Du sie ausschaltest.',
	'HELP_DL_SHOW_REAL_FILETIME'	=> 'Hiermit wird der wirkliche Zeitpunkt der letzten Änderung an den Download Dateien in den jeweiligen Details angezeigt.<br>Dieses ist die genaueste Angabe, selbst dann, wenn Dateien per FTP hochgeladen oder mehrfach geändert wurden, dieses aber nicht protokolliert wurde.',
	'HELP_DL_SIMILAR_DL'			=> 'Zeigt in der Detailansicht ähnliche Downloads an, die in der gleichen Kategorie gespeichert sind.<br><br>Hinweis: Bei großen Download Bereichen kann diese Option eine lange Ladezeit der Detailansicht hervorrufen und sollte dort ggf. deaktiviert werden.',
	'HELP_DL_SIMILAR_DL_LIMIT'		=> 'Anzahl der ähnlichen Downloads, die in der Download Detailseite zum aktuell angezeigten Download mit aufgelistet werden.',
	'HELP_DL_SORT_PREFORM'			=> 'Mit der Option `Voreinstellung` werden die Downloads für alle Benutzer in allen Kategorien gemäß der Sortierung im ACP angezeigt.<br>Mit der Option `Benutzer` kann der jeweiligen Benutzer selber entscheiden, nach welchen Kriterien sortiert wird und ob er diese fest eingestellt oder mit weiteren Auswahlmöglichkeiten haben möchte.',
	'HELP_DL_STAT_PERM'				=> 'Wähle hier, ab welchem Userlevel die Download Statistiken eingesehen werden dürfen.<br>Wenn Du diese z. B. erst ab Download Moderatoren aktivierst, kann jeder Board Administrator und Download Moderator (NICHT Forum Moderator!) diese Seite öffnen und ansehen.<br>Beachte, daß diese Seite eine extreme Ladezeit haben kann, so daß empfohlen wird, diese Seite nicht für viele zu öffnen, wenn Du ein Größeres Board betreibst und/oder viele Downloads bereitstellst.',
	'HELP_DL_STATISTICS'			=> 'Aktiviere detaillierte Statistiken für die Download Dateien.<br>Beachte, daß diese Statistiken zusätzliche Datenbank Abfragen benötigen und Datensätze in einer seperaten Tabelle anlegen.',
	'HELP_DL_STATS_PRUNE'			=> 'Gib hier die Anzahl der Datensätze ein, die die Statistik für diese Kategorie erreichen darf.<br>Jeder neue Datensatz löscht dann den Ältesten.<br>Gib hier 0 ein, um das Pruning zu deaktivieren, dadurch wächst jedoch die Datenbank immer weiter an.',
	'HELP_DL_STOP_UPLOADS'			=> 'Du kannst mit dieser Option Uploads aktivieren oder gänzlich deaktivieren.<br>Wenn Du dieses deaktivierst, können nur noch Adminitratoren Dateien mit dem Uploadformular hochladen.<br>Wenn diese Option aktiviert wird, können Benutzer nur abhängig der Kategorie- und Gruppenbefugnisse Dateien hochladen.',

	'HELP_DL_THUMB'						=> 'Dieses Feld kann ein kleines Bild hochladen (beachte die angegebene Dateigröße und Bildmaße unterhalb dieses Feldes), das in den Download Details angezeigt wird.<br>Wenn bereits ein Thumbnail existiert, kannst Du hiermit ein neues hochladen, um das bestehende Bild zu ersetzen.<br>Wenn Du das bestehende Thumbnail mit ´löschen´ markierst, wird das alte Bild nur entfernt',
	'HELP_DL_THUMB_CAT'					=> 'Diese Option lässt Thumbnails bei den Downloads dieser Kategorie zu.<br>Die Größe der Images ist von den Einstellungen in der allgemeinen Konfiguration der Extension abhängig.',
	'HELP_DL_THUMB_CAT_MAX'				=> 'Diese Option limitiert die maximale Anzahl Thumbnails je Download. Es wird hierbei mindestens ein Thumbnail erlaubt.',
	'HELP_DL_THUMB_MAX_DIM_X'			=> 'Diese Angaben begrenzen die mögliche Bildbreite hochzuladender Bilder, die als Thumbnail dargestellt werden.<br>Die Thumbnails selber werden verkleinert dargestellt und man kann mit einem Klick auf ein Thumbnail das hochgeladene Bild in einem Popup anzeigen lassen.<br><br>Gib hier 0 ein, um Thumbnails zu deaktivieren (nicht empfehlenswert, wenn die Thumbnail DateiGröße angegeben wurde).<br>Bestehende Thumbnails werden nach Änderungen dieser Angaben weiterhin angezeigt, sofern nicht die Dateigröße auf 0 gesetzt wurde.',
	'HELP_DL_THUMB_MAX_DIM_X_MAX'		=> 'Diese Angabe bestimmt die angezeigte Breite der Thumbnails in Pixeln. Breitere Bilder werden dabei auf die angegebene Größe verkleinert dargestellt.',
	'HELP_DL_THUMB_MAX_DIM_Y'			=> 'Diese Angaben begrenzen die mögliche Bildhöhe hochzuladender Bilder, die als Thumbnail dargestellt werden.<br>Die Thumbnails selber werden verkleinert dargestellt und man kann mit einem Klick auf ein Thumbnail das hochgeladene Bild in einem Popup anzeigen lassen.<br><br>Gib hier 0 ein, um Thumbnails zu deaktivieren (nicht empfehlenswert, wenn die Thumbnail DateiGröße angegeben wurde).<br>Bestehende Thumbnails werden nach Änderungen dieser Angaben weiterhin angezeigt, sofern nicht die Dateigröße auf 0 gesetzt wurde.',
	'HELP_DL_THUMB_MAX_DIM_Y_MAX'		=> 'Diese Angabe bestimmt die angezeigte Höhe der Thumbnails in Pixeln. Höhere Bilder werden dabei auf die angegebene Größe verkleinert dargestellt.',
	'HELP_DL_THUMB_MAX_SIZE'			=> 'Gib  0 als Dateigröße an, um Thumbnails in allen Kategorien abzuschalten.<br>Wenn Du Thumbnails erlaubst, dann gib in der nächsten Einstellung bitte die Bildmaße an, die die hochzuladenden Bilder maximal haben dürfen, aus denen die Thumbnails dargestellt werden.<br>Werden Thumbnails deaktiviert, zeigen die Download Details bestehene Thumbnails ebenfalls nicht mehr an.',
	'HELP_DL_TOPIC_DETAILS'				=> 'Zeigt die Download Beschreibung, Dateiname und Größe, bzw. bei externen Downloads die URL des Downloads mit im Forum Thema an.<br>Der Text kann dabei über oder unterhalb dem zuvor eingetragenen Text angezeigt werden.<br>Wenn das Thema über die Downloadkategorien angelegt wird, ist diese Option in der allgemeinen Konfiguration nicht relevant.',
	'HELP_DL_TODO_LINK'					=> 'Schaltet im Fußbereich der Download Extension den Link zur To-do-Liste an oder aus.<br>Die To-do-Daten und die Verwaltung in den Downloads bleiben hiervon unberührt.',
	'HELP_DL_USE_TODOLIST'				=> 'Aktiviert oder deaktiviert die To-Do-Liste.',
	'HELP_DL_TOPIC_FORUM'				=> 'Das Forum, in dem alle neue Themen zu den Downloads erstellt werden.<br>Wähle anstelle eines Forums `Kategorieauswahl`, um das Forum für Download Topics je Kategorie auswählen zu können.',
	'HELP_DL_TOPIC_FORUM_C'				=> 'Das Forum, in dem alle neue Themen zu den Downloads dieser Kategorie erstellt werden.',
	'HELP_DL_TOPIC_POST_CATNAME'		=> 'Fügt in den Beiträgen der Themen, welche für Downloads generiert werden, den Kategorienamen mit ein. Der Kategoriename wird dabei nach dem Download Namen eingefügt.<br>Hinweis:<br>Bestehende Themen werden nicht geändert. Erst nach der Bearbeitung der betreffenden Downloads erfolgt eine Aktualisierung der Beiträge in den Themen.',
	'HELP_DL_TOPIC_TEXT'				=> 'Freitext, der für die Themen verwendet wird. BBCodes, HTML und Smilies sind hierbei nicht möglich, da es sich lediglich um einen einleitenden Text zum Thema handeln soll.',
	'HELP_DL_TOPIC_TITLE_CATNAME'		=> 'Fügt beim Thema, welches für den Download generiert wird, den Kategorienamen an. Dieser wird durch ein `-` vom Download Titel getrennt.<br>Hinweis:<br>Bestehende Themen werden nicht geändert. Erst nach der Bearbeitung der betreffenden Downloads erfolgt eine Aktualisierung der Themen Titel.',
	'HELP_DL_TOPIC_TYPE'				=> 'Diese Option legt fest, als welchen Topic Typ das Thema für die Downloads gepostet werden sollen.<br>Beim erfassen oder ändern eines Downloads wird dann immer die letzte Einstellung laut Konfiguration, bzw. Kategorie verwendet. Bestehende Themen werden allerdings nicht verändert.',
	'HELP_DL_TOPIC_USER'				=> 'Wähle hier den Benutzer, der als Autor des Download Themas eingesetzt werden soll.<br>Wenn der aktuelle Benutzer Autor der Themen werden soll, dann ist die Option ´Der aktuelle Benutzer´ zu verwenden. Die Option über die Kategorie ermöglicht dagegen, einen Benutzer je Kategorie festzulegen. Dieses kann dann wiederum der aktuelle Benutzer sein oder ein anderer Benutzer, der über das Feld rechts neben dem Auswahlfeld mit seiner ID-Nummer vorgegeben wird. Dieses gilt auch, wenn die Auswahl ´Benutzer über ID auswählen´ angegeben wurde.<br><br><strong>Hinweis:</strong><br>Die Benutzer-ID wird durch die Download Extension nicht geprüft. Daher kann eine nicht existierende User-ID zu unerwarteten Störungen führen!',

	'HELP_DL_UPLOAD_FILE'			=> 'Die von Deinem Computer hochzuladene Datei.<br>Stelle sicher, daß die Dateigröße kleiner als die angezeigte Größe ist und die Dateierweiterung nicht in der Liste enthalten ist, die Du unterhalb dieses Feldes sehen kannst.',
	'HELP_DL_USE_EXT_BLACKLIST'		=> 'Wenn Du die Blackliste aktivierst, werden die eingetragenen Dateiendungen beim Hochladen oder Bearbeiten eines Downloads blockiert.',
	'HELP_DL_USER_TRAFFIC_ONCE'		=> 'Wähle, ob Downloads den Benutzer nur einmal Traffic abziehen sollen und danach nicht mehr erneut.<br><strong>Beachte:</strong><br>Diese Option ändert nicht den Status des Downloads selber!',

	'HELP_DL_VISUAL_CONFIRMATION'	=> 'Aktiviere diese Option, um Benutzer einen angezeigten 5-stelligen Bestätigungscode eingeben zu lassen, damit der Download der Datei zugelassen wird.<br>Wenn der Benutzer keinen Code eingegeben hat oder der Code falsch ist, wird die Extension nur eine Meldung anzeigen und den Download nicht freigeben.<br>Ist diese Option abgeschaltet, muss der Benutzer keinen Code eingeben und kann direkt aus den Details die Dateien herunterladen.',

	'HELP_NUMBER_RECENT_DL_ON_PORTAL'	=> 'Die Anzahl letzter Downloads, die der Benutzer auf dem Portal sieht.<br>Dabei wird die letzte Änderungszeit der Downloads verwendet, so daß auch ältere Downloads wieder ganz oben auf der Liste stehen können.',
]);
