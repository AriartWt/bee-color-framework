<?php
namespace wfw\tests\PHPUnit\unit\modules\news\domain;


use PHPUnit\Framework\TestCase;
use wfw\engine\package\news\domain\Title;

/**
 * Test de la classe title
 */
class TitleTest extends TestCase
{
    public function testCreateEmptyTitleThrowinvalidArgumentException(){
        $this->expectException(\InvalidArgumentException::class);
        new Title('');
    }

    public function testCreateNonEmptyTitle(){
        $title = new Title("A title");
        $this->assertEquals("A title",(string) $title);
    }
}