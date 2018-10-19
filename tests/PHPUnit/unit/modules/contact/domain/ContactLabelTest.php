<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 01/06/18
 * Time: 13:05
 */

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