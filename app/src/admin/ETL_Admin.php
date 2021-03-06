<?php

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Control\Controller;
use SilverStripe\View\Requirements;

use SwiftDevLabs\DuplicateDataObject\Forms\GridField\GridFieldDuplicateAction;

class Extract_Admin extends ModelAdmin {
	private static $managed_models = ['E_Process'];

	private static $url_segment = 'extract';

	private static $menu_title = 'Extract';

	private static $menu_priority = 9;

	public function init() {
		parent::init();
	}

	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		$gridField = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
		$gridField->getConfig()->addComponent(new GridFieldRunProcessAction());
		$gridField->getConfig()->addComponent(new GridFieldDuplicateAction());
		return $form;
	}
}

class Analyze_Admin extends ModelAdmin {
	private static $managed_models = ['ETL_RecordType'];

	private static $url_segment = 'analyze';

	private static $menu_title = 'Analyze';

	private static $menu_priority = 8;

}

class Transform_Admin extends ModelAdmin {
	private static $managed_models = ['T_Process'];

	private static $url_segment = 'transform';

	private static $menu_title = 'Transform';

	private static $menu_priority = 7;

	public function init() {
		parent::init();
//		Requirements::javascript("app/javascript/ws.js");
	}

	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		$gridField = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
		$gridField->getConfig()->addComponent(new GridFieldRunProcessAction());
		$gridField->getConfig()->addComponent(new GridFieldDuplicateAction());
		return $form;
	}

}

class Load_Admin extends ModelAdmin {
	private static $managed_models = ['L_Process'];

	private static $url_segment = 'load';

	private static $menu_title = 'Validate/Load';

	private static $menu_priority = 6;

	public function init() {
		parent::init();
	}

	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		$gridField = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
		$gridField->getConfig()->addComponent(new GridFieldRunProcessAction());
		$gridField->getConfig()->addComponent(new GridFieldDuplicateAction());
		return $form;
	}

}

class ETL_DB_Admin extends ModelAdmin {

    private static $managed_models = [
        'ETL_DB', 'ETL_REST'
    ];

    private static $url_segment = 'remotes';

    private static $menu_title = 'Remote Connections';

    private static $menu_priority = 1;
	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		$gridField = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
		$gridField->getConfig()->addComponent(new GridFieldDuplicateAction());
		return $form;
	}
}

class GridFieldRunProcessAction implements GridField_ColumnProvider, GridField_ActionProvider 
{

    public function augmentColumns($gridField, &$columns) 
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnAttributes($gridField, $record, $columnName) 
    {
        return ['class' => 'grid-field__col-compact'];
    }

    public function getColumnMetadata($gridField, $columnName) 
    {
        if ($columnName === 'Actions') {
            return ['title' => ''];
        }
    }

    public function getColumnsHandled($gridField) 
    {
        return ['Actions'];
    }

    public function getColumnContent($gridField, $record, $columnName) 
    {
        if (!$record->canEdit()) {
            return;
        }
/**
        $field = GridField_FormAction::create(
            $gridField,
            'RunProcess'.$record->ID,
            'Run Process',
            "docustomaction",
            ['RecordID' => $record->ID]
        )->addExtraClass("foo");
**/

	$field = new LiteralField("RunProcess",
		"<button class='btn btn-primary ws-action font-icon-rocket' id='process" . $record->ID . "'>Run Process</button>".
		"<div id='process" . $record->ID . "results'></div>"
	);
        return $field->Field();
    }

    public function getActions($gridField) 
    {
        return ['docustomaction'];
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data) 
    {
        if ($actionName !== 'docustomaction') {
            return;
        }
        // perform your action here

        // output a success message to the user
        Controller::curr()->getResponse()->setStatusCode(
            200,
            'Do Custom Action Done.'
        );
    }
}

