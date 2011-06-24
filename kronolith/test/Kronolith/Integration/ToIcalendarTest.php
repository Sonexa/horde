<?php
/**
 * Test exporting iCalendar events.
 *
 * PHP version 5
 *
 * @category   Horde
 * @package    Kronolith
 * @subpackage UnitTests
 * @author     Gunnar Wrobel <wrobel@pardus.de>
 * @link       http://www.horde.org/apps/kronolith
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 */

/**
 * Prepare the test setup.
 */
require_once dirname(__FILE__) . '/../Autoload.php';

/**
 * Test exporting iCalendar events.
 *
 * Copyright 2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPLv2). If you did not
 * receive this file, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @category   Horde
 * @package    Kronolith
 * @subpackage UnitTests
 * @author     Gunnar Wrobel <wrobel@pardus.de>
 * @link       http://www.horde.org/apps/kronolith
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 */
class Kronolith_Integration_ToIcalendarTest extends Kronolith_TestCase
{
    public function setUp()
    {
        $this->markTestIncomplete('Too much boilerplate necessary.');
        $GLOBALS['registry'] = new Kronolith_Stub_Registry();
        $GLOBALS['injector'] = new Horde_Injector(new Horde_Injector_TopLevel());
        $GLOBALS['conf']['prefs']['driver'] = 'Null';
        $GLOBALS['injector']->bindFactory('Kronolith_Geo', 'Kronolith_Factory_Geo', 'create');
        $GLOBALS['conf']['calendar']['driver'] = 'Ical';
    }

    public function tearDown()
    {
        unset($GLOBALS['registry']);
        unset($GLOBALS['injector']);
        unset($GLOBALS['conf']);
    }

    public function testBasic()
    {
        $event = $this->_getEvent();
        $ical = new Horde_Icalendar('1.0');
        $cal = $event->toiCalendar($ical);
        $ical->addComponent($cal);
        $this->assertEquals(
            $this->_getFixture('export1.ics'),
            $ical->exportvCalendar()
        );
    }

    private function _getEvent()
    {
        $event = new Kronolith_Event_Sql(new Kronolith_Stub_Driver(''));
        $event->start = new Horde_Date('2007-03-15 13:10:20');
        $event->end = new Horde_Date('2007-03-15 14:20:00');
        $event->creator = 'joe';
        $event->uid = '20070315143732.4wlenqz3edq8@horde.org';
        $event->title = 'Hübscher Termin';
        $event->description = "Schöne Bescherung\nNew line";
        $event->location = 'Allgäu';
        $event->alarm = 10;
        $event->tags = array('Schöngeistiges');
        $event->recurrence = new Horde_Date_Recurrence($event->start);
        $event->recurrence->setRecurType(Horde_Date_Recurrence::RECUR_DAILY);
        $event->recurrence->setRecurInterval(2);
        $event->recurrence->addException(2007, 3, 19);
        $event->initialized = true;
        return $event;
    }

    private function _getFixture($name)
    {
        return file_get_contents(dirname(__FILE__) . '/../fixtures/' . $name);
    }
}
