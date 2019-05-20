<?php
namespace Microweber\Utils\Backup;

class BackupManager
{

	public $exportType = 'json';
	public $importType = 'json';
	public $importFile = false;

	public function __construct()
	{
		ini_set('memory_limit', '-1');
		set_time_limit(0);
	}

	public function setExportType($type)
	{
		$this->exportType = $type;
	}

	public function setImportType($type)
	{
		$this->importType = $type;
	}

	public function setImportFile($file)
	{
		$this->importFile = $this->getBackupLocation() . $file;
	}

	public function startExport()
	{
		$export = new Export();
		$export->setType($this->exportType);

		$content = $export->getContent();

		if (isset($content['data'])) {

			$exportLocation = $this->getBackupLocation();

			$exportFilename = 'backup_export_' . date("Y-m-d-his") . '.' . $this->exportType;
			$exportPath = $exportLocation . $exportFilename;

			$save = file_put_contents($exportPath, $content['data']);

			if ($save) {
				return array(
					"filename" => $exportPath,
					"success" => "Backup export are saved success."
				);
			} else {
				return array(
					"error" => "File not save"
				);
			}
		}
	}

	public function startImport()
	{
		$import = new Import();
		$import->setType($this->importType);
		$import->setFile($this->importFile);
		
		$content = $import->readContentWithCache();

		$writer = new DatabaseWriter();
		$writer->setContent($content['data']);
		$writer->runWriter();
	}

	public function getBackupLocation()
	{
		$backupContent = storage_path() . '/backup_content/';

		if (! is_dir($backupContent)) {
			mkdir_recursive($backupContent);
			$htaccess = $backupContent . '.htaccess';
			if (! is_file($htaccess)) {
				touch($htaccess);
				file_put_contents($htaccess, 'Deny from all');
			}
		}

		return $backupContent;
	}
}