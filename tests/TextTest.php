<?php

/**
 * OriginPHP Framework
 * Copyright 2018 - 2021 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Origin\Test\Text;

use Origin\Text\Text;
use Exception;

class TextTest extends \PHPUnit\Framework\TestCase
{
    public function testToAscii()
    {
        $this->assertEquals('Ragnarr Lodbrok', Text::toAscii('Ragnarr Loðbrók'));
    }
    public function testSlug()
    {
        $this->assertEquals('who-is-ragnarr-lodbrok', Text::slug('Who is Ragnarr Loðbrók?'));
        $this->assertEquals('v2-0-0-with-extra-spacing', Text::slug('v2.0.0 WITH     extra spacing!'));
        $this->assertEquals('test-trimming', Text::slug('^test||trimming!'));
    }

    public function testTokenize()
    {
        $text = '2019-07-10 13:30:00 192.168.1.22 "GET /users/login HTTP/1.0" 200 1024';
        $expected = [
            '2019-07-10',
            '13:30:00',
            '192.168.1.22',
            'GET /users/login HTTP/1.0',
            '200',
            '1024',
        ];
        $this->assertEquals($expected, Text::tokenize($text));

        $expected = [
            'date' => '2019-07-10',
            'time' => '13:30:00',
            'ip' => '192.168.1.22',
            'request' => 'GET /users/login HTTP/1.0',
            'code' => '200',
            'bytes' => '1024',
        ];
        $this->assertEquals($expected, Text::tokenize($text, ['keys' => ['date','time','ip','request','code','bytes']]));

        // invalid amount of keys
        $this->expectException(Exception::class);
        Text::tokenize($text, ['keys' => ['date']]);
    }

    public function testStartsWith()
    {
        $this->assertTrue(Text::startsWith('foo', 'foobar'));
        $this->assertFalse(Text::startsWith('foo', 'barfoo'));
        $this->assertFalse(Text::startsWith('', 'foobar'));
    }
    public function testEndsWith()
    {
        $this->assertFalse(Text::endsWith('foo', 'foobar'));
        $this->assertTrue(Text::endsWith('foo', 'barfoo'));
        $this->assertFalse(Text::endsWith('', 'foobar'));
    }
    public function testLeft()
    {
        $this->assertEquals('foo', Text::left(':', 'foo:bar'));
        $this->assertEquals('one', Text::left(':', 'one:two:three'));
        $this->assertNull(Text::left('x', 'foo:bar'));
        $this->assertNull(Text::left('', ''));
    }
    public function testRight()
    {
        $this->assertEquals('bar', Text::right(':', 'foo:bar'));
        $this->assertNull(Text::right('x', 'foo:bar'));
        $this->assertNull(Text::right('', ''));
    }
    public function testContains()
    {
        $this->assertTrue(Text::contains('foo', 'foobar'));
        $this->assertTrue(Text::contains('foo', 'barfoo'));
        $this->assertTrue(Text::contains('foo', 'xfoox'));
        $this->assertFalse(Text::contains('moo', 'barfoo'));
        $this->assertFalse(Text::contains('', 'barfoo'));
    }
    public function testUpLo()
    {
        $this->assertEquals(strtoupper('foo'), Text::upper('foo'));
        $this->assertEquals(strtolower('FOO'), Text::lower('FOO'));

        $this->assertEquals(strtoupper('foo'), Text::toUpper('foo'));
        $this->assertEquals(strtolower('FOO'), Text::toLower('FOO'));
    }
    public function testReplace()
    {
        $this->assertEquals('foo', Text::replace('bar', '', 'foobar'));
        $this->assertEquals('foo', Text::replace('bar', '', 'fooBAR', ['insensitive' => true]));
    }
    public function testLen()
    {
        $this->assertEquals(3, Text::length('foo'));
        $var = null;
        $this->assertEquals(0, Text::length($var));
    }

    public function testInsert()
    {
        $this->assertEquals(
            'Record 1234568 has been updated',
            Text::insert('Record {id} has been updated', ['id' => 1234568])
        );

        $this->assertEquals(
            'Record 1234568 has been updated',
            Text::insert('Record {id} has been updated', ['id' => 1234568,['before' => ':','after' => '']])
        );
    }

    /**
     * text from ruby docs
     * @source https://api.rubyonrails.org/classes/ActionView/Helpers/TextHelper.html#method-i-word_wrap
     * @return void
     */
    public function testWordWrap()
    {
        $string = 'Once upon a time';
        $expected = 'Once upon a time';
        $this->assertEquals($expected, Text::wordWrap($string));

        $string = 'Once upon a time, in a kingdom called Far Far Away, a king fell ill, and finding a successor to the throne turned out to be more trouble than anyone could have imagined...';
        $expected = "Once upon a time, in a kingdom called Far Far Away, a king fell ill, and finding\na successor to the throne turned out to be more trouble than anyone could have\nimagined...";
        $this->assertEquals($expected, Text::wordWrap($string));

        $string = 'Once upon a time';
        $expected = "Once\nupon a\ntime";
        $this->assertEquals($expected, Text::wordWrap($string, ['width' => 8]));

        $expected = "Once\nupon\na\ntime";
        $this->assertEquals($expected, Text::wordWrap($string, ['width' => 1]));
    }

    public function testTruncate()
    {
        $string = <<< EOF
Ragnar Lodbrok or Lothbrok (Old Norse: Ragnarr Loðbrók, "Ragnar shaggy breeches", contemporary Norse: Ragnar Loðbrók) was a historically dubious[1][2] Norse Viking hero and legendary king of Denmark and Sweden, known from Viking Age Old Norse poetry and sagas. According to that traditional literature, Ragnar distinguished himself by many raids against Francia and Anglo-Saxon England during the 9th century. There is no evidence that he existed under this name and outside of the mythology associated with him. According to the Tale of Ragnar Lodbrok, Ragnar was the son of the Swedish king Sigurd Ring.
EOF;
        $this->assertEquals('Ragnar Lodbrok or Lothbrok (Ol...', Text::truncate($string));
        $this->assertEquals('Ragnar Lodbrok or Lothbrok', Text::truncate('Ragnar Lodbrok or Lothbrok'));
    }
}
