<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 31/05/18
 * Time: 19:12
 */

namespace wfw\tests\PHPUnit\unit\modules\news\data\model\objects;

use PHPUnit\Framework\TestCase;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\data\model\objects\Contact;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;

/**
 * Test du fonctionnement de la classe ModelObject Article
 */
final class ContactTest extends TestCase {
    public function testNotReadContactConstruction(){
        $id = new UUID();
        $date = microtime(true);
        $label = new ContactLabel("A label");
        $infos = new ContactInfos("Content");
        $article = new Contact(
            $id,
            $label,
            $infos,
            $date,
            false
        );
        $this->assertEquals($id,$article->getId());
        $this->assertEquals($date,$article->getCreationDate());
        $this->assertEquals($label,$article->getLabel());
        $this->assertEquals($infos,$article->getInfos());
        $this->assertFalse($article->isRead());
        $this->assertFalse($article->isArchived());
    }
    public function testReadContactConstruction(){
        $article = $this->createContact(true);
        $this->assertTrue($article->isRead());
    }

    public function testToDtoMethod(){
        $article = $this->createContact(true);
        /** @var \wfw\engine\package\contact\data\model\DTO\Contact $dto */
        $dto = $article->toDTO();

        $this->assertEquals($article->getId(),$dto->getId());
        $this->assertEquals($article->getLabel(),$dto->getLabel());
        $this->assertEquals($article->getInfos(),$dto->getInfos());
        $this->assertEquals($article->getCreationDate(),$dto->getCreationDate());
        $this->assertEquals($article->getArchivingDate(), $dto->getArchivingDate());
        $this->assertEquals($article->getReadDate(),$dto->getReadDate());
        $this->assertEquals($article->isRead(),$dto->isRead());
        $this->assertEquals($article->isArchived(),$dto->isArchived());
    }

	/**
	 * @param bool $read
	 * @return Contact
	 * @throws \InvalidArgumentException
	 */
    private function createContact(bool $read = false):Contact{
        return new Contact(
            new UUID(),
            new ContactLabel("A title"),
            new ContactInfos("Infos"),
            microtime(true),
            $read,
	        ($read)?microtime(true):null
        );
    }
}