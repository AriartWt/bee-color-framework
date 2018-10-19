<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 01/06/18
 * Time: 13:03
 */

namespace wfw\tests\PHPUnit\unit\modules\news\domain;

use PHPUnit\Framework\TestCase;
use wfw\engine\package\contact\domain\ContactInfos;

/**
 * Teste la class content
 */
final class ContactInfosTest extends TestCase
{
    public function testCreateEmptyContentThrowInvalidArgumentException(){
        $this->expectException(\InvalidArgumentException::class);
        new ContactInfos('');
    }

    public function testCreateNonEmptyContent(){
        $content = new ContactInfos('A Content');
        $this->assertEquals("A Content",(string) $content);
    }
}