<?php
/**
 * Kunena Component
 * @package Kunena.Site
 * @subpackage Lib
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 *
 * Based on FireBoard Component
 * @copyright (C) 2006 - 2007 Best Of Joomla All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.bestofjoomla.com
 **/
defined ( '_JEXEC' ) or die ();

class CKunenaLink {
	//
	// Basic universal href link
	//
	static function GetHrefLink($link, $name, $title = '', $rel = 'nofollow', $class = '', $anker = '', $attr = '') {
		return '<a ' . ($class ? 'class="' . $class . '" ' : '') . 'href="' . $link . ($anker ? ('#' . $anker) : '') . '" title="' . $title . '"' . ($rel ? ' rel="' . $rel . '"' : '') . ($attr ? ' ' . $attr : '') . '>' . $name . '</a>';
	}

	//
	// Basic universal href link
	//
	static function GetSefHrefLink($link, $name, $title = '', $rel = 'nofollow', $class = '', $anker = '', $attr = '') {
		$uri = $link instanceof JURI ? $link : JURI::getInstance($link);
		if ($anker) $uri->setFragment($anker);
		return JHTML::_('kunenaforum.link', $uri, $name, $title, $class, $rel, $attr);
	}

	//
	// Central Consolidation of all internal href links
	//

	static function GetAttachmentLink($folder,$filename,$name,$title = '', $rel = 'nofollow') {
		return self::GetHrefLink ( JURI::ROOT()."{$folder}/{$filename}", $name, $title, $rel );
	}

	static function GetRSSLink($name, $rel = 'follow', $params = '') {
		return self::GetHrefLink ( self::GetRSSURL($params), $name, '', $rel, '', '', 'target="_blank"' );
	}

	static function GetRSSURL($params = '', $xhtml = true) {
		return KunenaRoute::_ ( "index.php?option=com_kunena&view=rss&format=feed{$params}", $xhtml );
	}

	static function GetCategoryActionLink($task, $catid, $catname, $rel = 'follow', $class = '', $title = '', $extra = '') {
		$token = '&' . JUtility::getToken() . '=1';
		return self::GetSefHrefLink ( "index.php?option=com_kunena&view=category&task={$task}&catid={$catid}{$extra}{$token}", $catname, $title, $rel, $class );
	}

	static function GetCategoryReviewListLink($catid, $catname, $rel = 'nofollow', $class = '') {
		return self::GetSefHrefLink ( "index.php?option=com_kunena&view=review&action=list&catid={$catid}", $catname, '', $rel, $class );
	}

	static function GetSamePageAnkerLink($anker, $name, $rel = 'nofollow', $class = '') {
		jimport ( 'joomla.environment.request' );
		return self::GetHrefLink ( htmlspecialchars(JRequest::getURI (), ENT_COMPAT, 'UTF-8'), $name, '', $rel, $class, $anker );
	}

	static function GetReportMessageLink($catid, $id, $name, $rel = 'nofollow', $class = '', $title = '') {
		$message = KunenaForumMessageHelper::get($id);
		return self::GetSefHrefLink ( "index.php?option=com_kunena&view=report&catid={$catid}&id={$message->thread}&mesid={$message->id}", $name, $title, $rel, $class );
	}

	static function GetMessageIPLink($msg_ip, $rel = 'nofollow') {
		if (! empty ( $msg_ip )) {
			$iplink = '<a href="http://whois.domaintools.com/' . $msg_ip . '" target="_blank">';
			$iplink .= 'IP: ' . $msg_ip . '</a>';
		} else {
			$iplink = '&nbsp;';
		}

		return $iplink;
	}

	// Returns always link to Kunena profile
	static function GetMyProfileLink($userid, $name = null, $rel = 'nofollow', $task = '', $class = '') {
		if (!$name) {
			$profile = KunenaFactory::getUser($userid);
			$name = htmlspecialchars($profile->getName(), ENT_COMPAT, 'UTF-8');
		}
		return self::GetHrefLink ( self::GetMyProfileURL ( $userid, $task ), $name, '', $rel, $class );
	}

	// Returns always url to Kunena profile
	static function GetMyProfileURL($userid = 0, $task = '', $xhtml = true, $extra = '') {
		$my = JFactory::getUser();
		if ($userid && $userid!=$my->id) $userid = "&userid=$userid";
		else $userid = '';
		if ($task) $task = "&do=$task";
		return KunenaRoute::_ ( "index.php?option=com_kunena&view=profile{$userid}{$task}{$extra}", $xhtml );
	}

	static function GetUserlistURL($action = '', $xhtml = true) {
		$profile = KunenaFactory::getProfile ();
		return $profile->getUserListURL ( $action, $xhtml );
	}

	static function GetModerateUserLink($userid, $name = null, $title ='', $rel = 'nofollow', $class = '') {
		return self::GetSefHrefLink ( "index.php?option=com_kunena&view=moderateuser&userid={$userid}", $name, $title, $rel, $class );
	}

	static function GetUserlistLink($action, $name, $rel = 'nofollow', $class = '') {
		$link = self::GetUserlistURL ( $action );
		if ($link) {
			return self::GetHrefLink ( $link, $name, '', $rel, $class );
		}
		return $name;
	}

	static function GetShowLatestLink($name, $do = '', $rel = 'follow') {
		if ($do) $do = "&do=$do";
		return self::GetSefHrefLink ( "index.php?option=com_kunena&view=latest{$do}", $name, '', $rel );
	}

	static function GetKarmaLink($do, $catid, $pid, $userid, $name, $rel = 'nofollow') {
		$token = '&'.JUtility::getToken().'=1';
		return self::GetSefHrefLink ( "index.php?option=com_kunena&view=karma&do={$do}&userid={$userid}&catid={$catid}&pid={$pid}{$token}", $name, '', $rel );
	}

	static function GetSearchURL($view, $searchword='', $limitstart=0, $limit=0, $params = '', $xhtml=true) {
		$config = KunenaFactory::getConfig ();
		$limitstr = "";
		if ($limitstart > 0)
			$limitstr .= "&limitstart=$limitstart";
		if ($limit > 0 && $limit != $config->messages_per_page_search)
			$limitstr .= "&limit=$limit";
		if ($searchword)
			$searchword = '&q=' . urlencode ( $searchword );
		return KunenaRoute::_ ( "index.php?option=com_kunena&view={$view}{$searchword}{$params}{$limitstr}", $xhtml );
	}

	static function GetPollURL($do, $id = NULL, $catid) {
		$idstring = '';
		if ($id)
			$idstring .= "&id=$id";
		$catidstr = "&catid=$catid";
		return KunenaRoute::_ ( "index.php?option=com_kunena&view=poll&do={$do}{$catidstr}{$idstring}" );
	}

	static function GetStatsLink($name, $class = '', $rel = 'follow') {
		return self::GetHrefLink ( KunenaRoute::_ ( 'index.php?option=com_kunena&view=stats' ), $name, '', $rel, $class );
	}
}