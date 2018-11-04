<?php
namespace wfw\tests\PHPUnit\unit\modules\news\domain;

use PHPUnit\Framework\TestCase;
use wfw\engine\package\news\domain\Content;

/**
 * Teste la class content
 */
final class ContentTest extends TestCase
{
    public function testCreateEmptyContentThrowInvalidArgumentException(){
        $this->expectException(\InvalidArgumentException::class);
        new Content('');
    }

    public function testCreateNonEmptyContent(){
        $content = new Content('A Content');
        $this->assertEquals("A Content",(string) $content);
    }
}