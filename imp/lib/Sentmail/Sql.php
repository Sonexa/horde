<?php
/**
 * Copyright 2010-2014 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @category  Horde
 * @copyright 2010-2014 Horde LLC
 * @license   http://www.horde.org/licenses/gpl GPL
 * @package   IMP
 */

/**
 * Sentmail driver implementation for SQL databases.
 *
 * @author    Jan Schneider <jan@horde.org>
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2010-2014 Horde LLC
 * @license   http://www.horde.org/licenses/gpl GPL
 * @package   IMP
 */
class IMP_Sentmail_Sql extends IMP_Sentmail
{
    /**
     * Handle for the current database connection.
     *
     * @var Horde_Db_Adapter
     */
    protected $_db;

    /**
     * @param array $params  Parameters:
     *   - db: (Horde_Db_Adapter) [REQUIRED] The DB instance.
     *   - table: (string) The name of the sentmail table.
     *            DEFAULT: 'imp_sentmail'
     */
    public function __construct(array $params = array())
    {
        if (!isset($params['db'])) {
            throw new IMP_Exception('Missing db parameter.');
        }
        $this->_db = $params['db'];
        unset($params['db']);

        $params = array_merge(array(
            'table' => 'imp_sentmail'
        ), $params);

        parent::__construct($params);
    }

    /**
     */
    protected function _log($action, $message_id, $recipient, $success)
    {
        /* Build the SQL query. */
        $query = sprintf('INSERT INTO %s (sentmail_who, sentmail_ts, sentmail_messageid, sentmail_action, sentmail_recipient, sentmail_success) VALUES (?, ?, ?, ?, ?, ?)', $this->_params['table']);
        $values = array(
            $GLOBALS['registry']->getAuth(),
            time(),
            $message_id,
            $action,
            $recipient,
            intval($success)
        );

        /* Execute the query. */
        try {
            $this->_db->insert($query, $values);
        } catch (Horde_Db_Exception $e) {}
    }

    /**
     */
    public function favouriteRecipients($limit, $filter = null)
    {
        /* Build the SQL query. */
        $where = '';
        if (!empty($filter)) {
            $filter = array_map(array($this->_db, 'quote'), $filter);
            $where = sprintf(' AND sentmail_action in (%s)',
                             implode(', ', $filter));
        }

        $query = sprintf('SELECT sentmail_recipient, count(*) AS sentmail_count FROM %s WHERE sentmail_who = %s AND sentmail_success = 1%s GROUP BY sentmail_recipient ORDER BY sentmail_count DESC',
                         $this->_params['table'],
                         $this->_db->quote($GLOBALS['registry']->getAuth()),
                         $where);

        /* Execute the query. */
        try {
            $query = $this->_db->addLimitOffset($query, array('limit' => $limit));
            return $this->_db->selectValues($query);
        } catch (Horde_Db_Exception $e) {
            return array();
        }
    }

    /**
     */
    public function numberOfRecipients($hours, $user = false)
    {
        /* Build the SQL query. */
        $query = sprintf(
            'SELECT COUNT(*) FROM %s WHERE sentmail_ts > ? AND sentmail_success = 1',
            $this->_params['table']
        );

        if ($user) {
            $query .= sprintf(' AND sentmail_who = %s', $this->_db->quote($GLOBALS['registry']->getAuth()));
        }

        /* Execute the query. */
        try {
            return $this->_db->selectValue($query, array(time() - $hours * 3600));
        } catch (Horde_Db_Exception $e) {
            return 0;
        }
    }

    /**
     */
    protected function _deleteOldEntries($before)
    {
        /* Build the SQL query. */
        $query = sprintf('DELETE FROM %s WHERE sentmail_ts < ?',
                         $this->_params['table']);

        /* Execute the query. */
        try {
            $this->_db->delete($query, array($before));
        } catch (Horde_Db_Exception $e) {}
    }

}
