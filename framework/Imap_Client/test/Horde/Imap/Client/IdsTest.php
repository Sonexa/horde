<?php
/**
 * Copyright 2011-2014 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category   Horde
 * @copyright  2011-2014 Horde LLC
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package    Imap_Client
 * @subpackage UnitTests
 */

/**
 * Tests for the Ids object.
 *
 * @author     Michael Slusarz <slusarz@horde.org>
 * @category   Horde
 * @copyright  2011-2014 Horde LLC
 * @ignore
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package    Imap_Client
 * @subpackage UnitTests
 */
class Horde_Imap_Client_IdsTest extends PHPUnit_Framework_TestCase
{
    public function testBasicAddingOfIds()
    {
        $ids = new Horde_Imap_Client_Ids(array(1, 3, 5));

        $this->assertEquals(
            3,
            count($ids)
        );

        $this->assertEquals(
            '1,3,5',
            strval($ids)
        );

        $this->assertFalse($ids->isEmpty());
    }

    public function testIgnoreNullInput()
    {
        $ids = new Horde_Imap_Client_Ids(null);
        $this->assertEquals(
            0,
            count($ids)
        );

        $ids = new Horde_Imap_Client_Ids();
        $ids->add(null);
        $this->assertEquals(
            0,
            count($ids)
        );
    }

    public function testEmptyIdsArray()
    {
        $ids = new Horde_Imap_Client_Ids(array());

        $this->assertEquals(
            0,
            count($ids)
        );

        $this->assertEquals(
            '',
            strval($ids)
        );

        $this->assertTrue($ids->isEmpty());
    }

    public function testSequenceParsing()
    {
        $ids = new Horde_Imap_Client_Ids('12:10');

        $this->assertEquals(
            3,
            count($ids)
        );

        $this->assertEquals(
            array(10, 11, 12),
            iterator_to_array($ids)
        );

        $ids = new Horde_Imap_Client_Ids('12,11,10');

        $this->assertEquals(
            3,
            count($ids)
        );

        $this->assertEquals(
            array(12, 11, 10),
            iterator_to_array($ids)
        );

        $ids = new Horde_Imap_Client_Ids('10:12,10,11,12,10:12');

        $this->assertEquals(
            3,
            count($ids)
        );

        $ids = new Horde_Imap_Client_Ids('10:10');

        $this->assertEquals(
            1,
            count($ids)
        );
    }

    public function testRangeGeneration()
    {
        $ids = new Horde_Imap_Client_Ids('100,300,200');

        $this->assertEquals(
            '100:300',
            $ids->range_string
        );

        $ids = new Horde_Imap_Client_Ids(Horde_Imap_Client_Ids::ALL);

        $this->assertEquals(
            '',
            $ids->range_string
        );

        $ids = new Horde_Imap_Client_Ids('50');

        $this->assertEquals(
            '50',
            $ids->range_string
        );
    }

    public function testSorting()
    {
        $ids = new Horde_Imap_Client_Ids('14,12,10');

        $this->assertEquals(
            '14,12,10',
            $ids->tostring
        );
        $this->assertEquals(
            '10,12,14',
            $ids->tostring_sort
        );
    }

    public function testSpecialIdValueStringRepresentations()
    {
        $ids = new Horde_Imap_Client_Ids(Horde_Imap_Client_Ids::ALL);

        $this->assertEquals(
            '1:*',
            $ids->tostring
        );

        $ids = new Horde_Imap_Client_Ids(Horde_Imap_Client_Ids::SEARCH_RES);

        $this->assertEquals(
            '$',
            $ids->tostring
        );

        $ids = new Horde_Imap_Client_Ids(Horde_Imap_Client_Ids::LARGEST);

        $this->assertEquals(
            '*',
            $ids->tostring
        );
    }

    public function testDuplicatesAllowed()
    {
        $ids = new Horde_Imap_Client_Ids('1:10,1:10');
        $this->assertEquals(
            10,
            count($ids)
        );

        $ids = new Horde_Imap_Client_Ids();
        $ids->duplicates = true;
        $ids->add('1:10,1:10');
        $this->assertEquals(
            20,
            count($ids)
        );
    }

    public function testPop3SequenceStringGenerate()
    {
        $this->assertEquals(
            'ABCDEFGHIJ ABCDE',
            strval(new Horde_Imap_Client_Ids_Pop3(array('ABCDEFGHIJ', 'ABCDE')))
        );

        $this->assertEquals(
            'ABCDEFGHIJ',
            strval(new Horde_Imap_Client_Ids_Pop3('ABCDEFGHIJ'))
        );
    }

    public function testPop3SequenceStringParse()
    {
        $ids = new Horde_Imap_Client_Ids_Pop3('ABCDEFGHIJ ABCDE');
        $this->assertEquals(
            array('ABCDEFGHIJ', 'ABCDE'),
            $ids->ids
        );

        $ids = new Horde_Imap_Client_Ids_Pop3('ABCDEFGHIJ ABC ABCDE');
        $this->assertEquals(
            array('ABCDEFGHIJ', 'ABC', 'ABCDE'),
            $ids->ids
        );

        $ids = new Horde_Imap_Client_Ids_Pop3('10:12');
        $this->assertEquals(
            array('10:12'),
            $ids->ids
        );
    }

    public function testSplit()
    {
        // ~5000 non-sequential IDs
        $ids = new Horde_Imap_Client_Ids(range(1, 10000, 2));

        $split = $ids->split(2000);

        $this->assertGreaterThan(1, count($split));

        foreach (array_slice($split, 0, -1) as $val) {
            $this->assertGreaterThan(
                2000,
                strlen($val)
            );

            $this->assertNotEquals(
                ',',
                substr($val, -1)
            );
        }

        $last = array_pop($split);
        $this->assertLessThan(
            2001,
            strlen($last)
        );

        $this->assertNotEquals(
            ',',
            substr($last, -1)
        );
    }

    public function testSplitOnAll()
    {
        $ids = new Horde_Imap_Client_Ids(Horde_Imap_Client_Ids::ALL);

        $this->assertEquals(
            array(
                '1:*'
            ),
            $ids->split(2000)
        );
    }

    public function testRemove()
    {
        $ids = new Horde_Imap_Client_Ids('1:100');
        $this->assertEquals(
            100,
            count($ids)
        );

        // Remove from array
        $ids->remove(range(1, 10));
        $this->assertEquals(
            90,
            count($ids)
        );

        // Removing same IDs shouldn't change anything.
        $ids->remove(range(1, 10));
        $this->assertEquals(
            90,
            count($ids)
        );

        // Remove via sequence string
        $ids->remove('11:20');
        $this->assertEquals(
            80,
            count($ids)
        );

        // Remove via object
        $ids->remove(new Horde_Imap_Client_Ids('21:30'));
        $this->assertEquals(
            70,
            count($ids)
        );
    }

    public function testMinAndMax()
    {
        $ids = new Horde_Imap_Client_Ids(array(1));

        $this->assertEquals(
            1,
            $ids->min
        );
        $this->assertEquals(
            1,
            $ids->max
        );

        $ids2 = new Horde_Imap_Client_Ids(array(1, 2));

        $this->assertEquals(
            1,
            $ids2->min
        );
        $this->assertEquals(
            2,
            $ids2->max
        );

        $ids3 = new Horde_Imap_Client_Ids(array(1, 5, 3));

        $this->assertEquals(
            1,
            $ids3->min
        );
        $this->assertEquals(
            5,
            $ids3->max
        );
    }

}
