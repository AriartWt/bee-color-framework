<?php
namespace wfw\tests\PHPUnit\unit\modules\contact\domain;

use PHPUnit\Framework\TestCase;
use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\news\domain\Title;

/**
 * Test de la classe ContactLabel
 */
class ContactLabelTest extends TestCase
{
    public function testCreateEmptyTitleThrowinvalidArgumentException(){
        $this->expectException(\InvalidArgumentException::class);
        new ContactLabel('');
    }

    public function testCreateNonEmptyTitle(){
        $label = new ContactLabel("A title");
        $this->assertEquals("A title",(string) $label);
    }
}