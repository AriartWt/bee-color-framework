<?php
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