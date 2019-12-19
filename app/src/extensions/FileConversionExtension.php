<?php

use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

class FileConversionExtension extends DataExtension {

	public function onAfterUpload() {
		Injector::inst()->get(LoggerInterface::class)->info($this->owner->getFileType() . " was uploaded");
	}

}

