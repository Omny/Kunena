<?php
/**
 * Kunena Component
 * @package Kunena.Site
 * @subpackage Models
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Announcement Model for Kunena
 *
 * @since		2.0
 */
class KunenaModelAnnouncement extends KunenaModel {

	protected function populateState() {
		$id = $this->getInt ( 'id', 0 );
		$this->setState ( 'item.id', $id );

		$value = $this->getInt ( 'limit', 0 );
		if ($value < 1) $value = 20;
		$this->setState ( 'list.limit', $value );

		$value = $this->getInt ( 'limitstart', 0 );
		if ($value < 0) $value = 0;
		$this->setState ( 'list.start', $value );
	}

	function getNewAnnouncement() {
		return new KunenaForumAnnouncement;
	}

	function getAnnouncement() {
		return KunenaForumAnnouncementHelper::get($this->getState ( 'item.id' ));
	}

	function getAnnouncements() {
		return KunenaForumAnnouncementHelper::getAnnouncements($this->getState ( 'list.start'), $this->getState ( 'list.limit'), !$this->me->isModerator());
	}
}