<?php
/**
 * Kunena Component
 * @package Kunena.Site
 * @subpackage Models
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * User Model for Kunena
 *
 * @since		2.0
 */
class KunenaModelUser extends KunenaModel {
	protected function populateState() {
		$active = $this->app->getMenu ()->getActive ();
		$active = $active ? (int) $active->id : 0;

		$layout = $this->getCmd ( 'layout', 'default' );
		$this->setState ( 'layout', $layout );

		$config = KunenaFactory::getConfig();

		// TODO: create state for item
		if ($layout != 'list') {
			return;
		}

		// List state information
		$limit = $this->getUserStateFromRequest ( "com_kunena.users_{$active}_list_limit", 'limit', $config->get('userlist_rows'), 'int' );
		if ($limit < 1 || $limit > 100) $limit = $config->get('userlist_rows');
		$this->setState ( 'list.limit', $limit );

		$value = $this->getUserStateFromRequest ( "com_kunena.users_{$active}_list_start", 'limitstart', 0, 'int' );
		$value -= $value % $limit;
		$this->setState ( 'list.start', $value );

		$value = $this->getUserStateFromRequest ( "com_kunena.users_{$active}_list_ordering", 'filter_order', 'id', 'cmd' );
		$this->setState ( 'list.ordering', $value );

		$value = $this->getUserStateFromRequest ( "com_kunena.users_{$active}_list_direction", 'filter_order_Dir', 'asc', 'word' );
		if ($value != 'asc')
			$value = 'desc';
		$this->setState ( 'list.direction', $value );

		$value = $this->getUserStateFromRequest ( "com_kunena.users_{$active}_list_search", 'search', '' );
		if (!empty($value) && $value != JText::_('COM_KUNENA_USRL_SEARCH')) $this->setState ( 'list.search', $value );

		// FIXME: doesn't work: Super administrator id may vary (JUpgrade 1.5 -> 1.6, migrations etc)
		$this->setState ( 'list.exclude', version_compare(JVERSION, '1.6','>') ? '42' : '62');
	}

	public function getQueryWhere() {
		if ($this->config->userlist_count_users == '1' ) $where = '(block=0 OR activation="")';
		elseif ($this->config->userlist_count_users == '2' ) $where = '(block=0 AND activation="")';
		elseif ($this->config->userlist_count_users == '3' ) $where = 'block=0';
		else $where = '1';
		return $where;
	}

	public function getQuerySearch() {
		// TODO: add strict search from the beginning of the name
		$search = $this->getState ( 'list.search');
		$exclude = $this->getState ( 'list.exclude');
		$where = array();
		if ($search) {
			$db = JFactory::getDBO();

			if (!$this->config->username==1) $where[] = "u.name LIKE '%{$db->getEscaped($search)}%'";
			if ($this->config->username==0 || !$where) $where[] = "u.username LIKE '%{$db->getEscaped($search)}%'";
			// TODO: search in displayname

			$where = 'AND ('.implode(' OR ', $where).')';
		} else {
			$where = '';
		}

		return "{$where} AND u.id NOT IN ({$exclude})";
	}

	public function getTotal() {
		static $total = false;
		if ($total === false) {
			$db = JFactory::getDBO();
			$where = $this->getQueryWhere();
			$db->setQuery ( "SELECT COUNT(*) FROM #__users WHERE {$where}" );
			$total = $db->loadResult ();
			KunenaError::checkDatabaseError();
		}
		return $total;
	}

	public function getCount() {
		static $total = false;
		if ($total === false) {
			$db = JFactory::getDBO();
			$where = $this->getQueryWhere();
			$search = $this->getQuerySearch();
			$query = "SELECT COUNT(*)
				FROM #__users AS u
				INNER JOIN #__kunena_users AS ku ON ku.userid = u.id
				WHERE {$where} {$search}";
			$db->setQuery ( $query );
			$total = $db->loadResult ();
			KunenaError::checkDatabaseError();
		}
		return $total;
	}

	public function getItems() {
		// FIXME: use pagination object and redirect on illegal page (maybe in the view)
		// TODO: should we reset to page 1 when user makes a new search?
		static $items = false;
		if ($items === false) {
			$limitstart = $this->getState ( 'list.start');
			$limit = $this->getState ( 'list.limit');
			$count = $this->getCount();
			if ($count < $limitstart) {
				$limitstart = $count - ($count % $limit);
				$this->setState ( 'list.start', $limitstart );
			}

			$db = JFactory::getDBO();
			$where = $this->getQueryWhere();
			$search = $this->getQuerySearch();
			$moderator = intval($this->me->isModerator());
			$query = "SELECT *, IF(ku.hideEmail=0 OR {$moderator},u.email,'') AS email
				FROM #__users AS u
				INNER JOIN #__kunena_users AS ku ON ku.userid = u.id
				WHERE {$where} {$search}";
			$query .= " ORDER BY {$db->nameQuote($this->getState ( 'list.ordering'))} {$this->getState ( 'list.direction')}";

			$db->setQuery ( $query, $limitstart, $limit );
			$items = $db->loadObjectList ('id');
			KunenaError::checkDatabaseError();

			// Prefetch all users/avatars to avoid user by user queries during template iterations
			KunenaUserHelper::loadUsers(array_keys($items));
		}
		return $items;
	}
}