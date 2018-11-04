<?php
namespace wfw\tests\PHPUnit\unit\modules\news\domain;


use PHPUnit\Framework\TestCase;
use wfw\engine\package\news\domain\VisualLink;

/**
 * test de le classe VisualLink
 */
class VisualLinkTest extends TestCase
{
    public function testCreateAnEmptuVisualLinkThrowInvalidArgumentException(){
        $this->expectException(\InvalidArgumentException::class);
        new VisualLink("");
    }

    public function testCreateNonEmptyVisualLink(){
        $visualLink = new VisualLink("a/link");
        $this->assertEquals("a/link",(string) $visualLink);
    }
}