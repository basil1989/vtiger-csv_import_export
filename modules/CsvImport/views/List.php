<?php

class CsvImport_List_View extends Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('uploadAndParse');
		$this->exposeMethod('basicStep');
		$this->exposeMethod('import');
		$this->exposeMethod('checkImportStatus');
	}

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), $moduleName)) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

    public function process(Vtiger_Request $request) {
		global $VTIGER_BULK_SAVE_MODE;
		$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;

		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->basicStep($request);
		}
		
		$VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
    }

	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$jsFileNames = array(
			'modules.CsvImport.resources.Import'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	function basicStep(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$moduleModel = Vtiger_Module_Model::getInstance('Accounts');
		$moduleMeta = $moduleModel->getModuleMeta();

		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'CsvImport');
		$viewer->assign('SUPPORTED_FILE_TYPES', CsvImport_Utils_Helper::getSupportedFileExtensions());
		$viewer->assign('SUPPORTED_FILE_ENCODING', CsvImport_Utils_Helper::getSupportedFileEncoding());
		$viewer->assign('SUPPORTED_DELIMITERS', CsvImport_Utils_Helper::getSupportedDelimiters());
		$viewer->assign('AUTO_MERGE_TYPES', CsvImport_Utils_Helper::getAutoMergeTypes());
		
		//Duplicate records handling not supported for inventory moduels
		$duplicateHandlingNotSupportedModules = getInventoryModules();
		if(in_array($moduleName, $duplicateHandlingNotSupportedModules)){
			$viewer->assign('DUPLICATE_HANDLING_NOT_SUPPORTED', true);
		}
		//End
		
		$viewer->assign('AVAILABLE_FIELDS', $moduleMeta->getMergableFields());
		$viewer->assign('ENTITY_FIELDS', $moduleMeta->getEntityFields());
		$viewer->assign('ERROR_MESSAGE', $request->get('error_message'));
		$viewer->assign('IMPORT_UPLOAD_SIZE', '3145728');

		return $viewer->view('BasicStep.tpl', $request->getModule());
	}

	function uploadAndParse(Vtiger_Request $request) {
		if(CsvImport_Utils_Helper::validateFileUpload($request)) {
			$moduleName = $request->getModule();
			$user = Users_Record_Model::getCurrentUserModel();
			$fileReader = CsvImport_Utils_Helper::getFileReader($request, $user);
			if($fileReader == null) {
				$request->set('error_message', vtranslate('CSV_LBL_INVALID_FILE', 'CsvImport'));
				$this->basicStep($request);
				exit;
			}

			$hasHeader = $fileReader->hasHeader();
			$rowData = $fileReader->getFirstRowData($hasHeader);
			$viewer = $this->getViewer($request);
			$autoMerge = $request->get('auto_merge');
			if(!$autoMerge) {
				$request->set('merge_type', 0);
				$request->set('merge_fields', '');
			} else {
				$viewer->assign('MERGE_FIELDS', Zend_Json::encode($request->get('merge_fields')));
			}

			$moduleName = $request->getModule();
			$moduleModel = Vtiger_Module_Model::getInstance('Accounts');
			$moduleMeta = $moduleModel->getModuleMeta();


			$viewer->assign('DATE_FORMAT', $user->date_format);
			$viewer->assign('FOR_MODULE', $moduleName);
			$viewer->assign('MODULE', 'CsvImport');

			$viewer->assign('HAS_HEADER', $hasHeader);
			$viewer->assign('ROW_1_DATA', $rowData);
			$viewer->assign('USER_INPUT', $request);

			//get field name 
			$adb = PearDatabase::getInstance();
			$result = $adb->pquery ("SELECT fieldname, fieldlabel from vtiger_field where tablename='vtiger_account'");
			$noofrow = $adb->num_rows($result );
			$fieldNames = array();
			for($i=0; $i<$noofrow ; $i++) {
	            $fieldNames[$adb->query_result($result,$i,'fieldname')]=$adb->query_result($result,$i,'fieldlabel');
			}

		//	$viewer->assign('AVAILABLE_FIELDS', $moduleMeta->getImportableFields($moduleName));//$moduleMeta->getImportableFields($moduleName)
			$viewer->assign('ACCOUNT_FIELDS', $fieldNames);
			$viewer->assign('ENCODED_MANDATORY_FIELDS', Zend_Json::encode($moduleMeta->getMandatoryFields($moduleName)));
			$viewer->assign('SAVED_MAPS', CsvImport_Map_Model::getAllByModule($moduleName));
			$viewer->assign('USERS_LIST', CsvImport_Utils_Helper::getAssignedToUserList($moduleName));
			$viewer->assign('GROUPS_LIST', CsvImport_Utils_Helper::getAssignedToGroupList($moduleName));
			//echo "<pre>"; print_r(Import_Utils_Helper::getAssignedToGroupList($moduleName)); echo "</pre>"; exit;
			return $viewer->view('AdvancedStep.tpl', 'CsvImport');
		} else {
			$this->basicStep($request);
		}
	}

	function import(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$user = Users_Record_Model::getCurrentUserModel();
		$fileReader = CsvImport_Utils_Helper::getFileReader($request, $user);
		if($fileReader == null) {
			$request->set('error_message', vtranslate('CSV_LBL_INVALID_FILE', 'CsvImport'));
			$this->basicStep($request);
			exit;
		}
		$columnIdx = $request->get('cur_selected');
		$hasHeader = true;
		$columnData = $fileReader->getColumnData($hasHeader, $columnIdx);

		$fields = $request->get('select2input');
		$headers = $request->get('select2input2');
		$fpieces = explode(",", $fields);
		$hpieces = explode(",", $headers);
		$fieldCount = sizeof($fpieces);

		$Data = array();
		$adb = PearDatabase::getInstance();
		foreach($columnData as $accountNo) {
			$result = $adb->pquery ('SELECT '.$fields.' from vtiger_account where account_no='.$accountNo);
			$rowData = $adb->query_result_rowdata($result,0);
			$rowArr = array();
			for($i = 0; $i < $fieldCount; $i++){
				array_push($rowArr, $rowData[$i]);
			}
			array_push($Data, $rowArr);
		}

		$this->output($request, $hpieces, $Data);
	}


	function output($request, $headers, $entries) {
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=export.csv");
		header("Content-Transfer-Encoding: UTF-8");
	    header('Pragma: no-cache');
	    header("Expires: 0");

	    ob_end_clean();
		
		$fp = fopen("php://output", "w");
		fputcsv($fp, $headers);
		foreach($entries as $row) {
			fputcsv($fp, $row, ',');
		}
		fclose($fp);
		exit;
	}
}