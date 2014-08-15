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
 * Tests for UTF7-IMAP <-> UTF-8 conversions.
 *
 * @author     Michael Slusarz <slusarz@horde.org>
 * @category   Horde
 * @copyright  2011-2014 Horde LLC
 * @ignore
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package    Imap_Client
 * @subpackage UnitTests
 */
class Horde_Imap_Client_Utf7ConvertTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider conversionProvider
     */
    public function testConversion($orig, $expected = null)
    {
        $utf7_imap = Horde_Imap_Client_Utf7imap::Utf8ToUtf7Imap(
            $orig,
            !is_null($expected)
        );

        $this->assertEquals(
            $expected ?: $orig,
            $utf7_imap
        );

        if ($expected) {
            $utf8 = Horde_Imap_Client_Utf7imap::Utf7ImapToUtf8($utf7_imap);
            $this->assertEquals(
                $orig,
                $utf8
            );
        }
    }

    public function conversionProvider()
    {
        return array(
            array('Envoyé', 'Envoy&AOk-'),
            array('Töst-', 'T&APY-st-'),
            array('&', '&-'),
            array('&-'),
            array('Envoy&AOk-'),
            array('T&APY-st-'),
            // Bug #10133
            array('Entw&APw-rfe'),
            // Bug #10093
            array('Foo&Bar-2011', 'Foo&-Bar-2011')
        );
    }

}
