<?php

namespace Sensio\Bundle\HangmanBundle\Tests;

use Sensio\Bundle\HangmanBundle\Game\WordList;

class WordListTest extends \PHPUnit_Framework_TestCase
{
    public function testNoRandomWord()
    {
        $this->setExpectedException('InvalidArgumentException');

        $list = new WordList();
        $list->getRandomWord(3);
    }

    public function testGetRandomWord()
    {
        $list = new WordList();
        $list->addWord('php');
        $list->addWord('foo');

        $this->assertContains($list->getRandomWord(3), array('php', 'foo'));
    }
}