<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use Civi\DataProcessor\Output\ExportOutputInterface;

use CRM_Dataprocessor_ExtensionUtil as E;

class CRM_DataprocessorOutputExport_CSV implements ExportOutputInterface {

  const MAX_DIRECT_SIZE = 500;

  const RECORDS_PER_JOB = 250;

  /**
   * Returns true when this filter has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration() {
    return true;
  }

  /**
   * When this filter type has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $filter
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $output=array()) {
    $form->add('text', 'delimiter', E::ts('Delimiter'), array(), true);
    $form->add('text', 'enclosure', E::ts('Enclosure'), array(), true);
    $form->add('text', 'escape_char', E::ts('Escape char'), array(), true);

    $configuration = false;
    if ($output && isset($output['configuration'])) {
      $configuration = $output['configuration'];
    }
    if ($configuration && isset($configuration['delimiter']) && $configuration['delimiter']) {
      $defaults['delimiter'] = $configuration['delimiter'];
    } else {
      $defaults['delimiter'] = ';';
    }
    if ($configuration && isset($configuration['enclosure']) && $configuration['enclosure']) {
      $defaults['enclosure'] = $configuration['enclosure'];
    } else {
      $defaults['enclosure'] = '"';
    }
    if ($configuration && isset($configuration['escape_char']) && $configuration['escape_char']) {
      $defaults['escape_char'] = $configuration['escape_char'];
    } else {
      $defaults['escape_char'] = '\\';
    }
    $form->setDefaults($defaults);
  }

  /**
   * When this filter type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/DataprocessorOutputExport/Form/Configuration/CSV.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $output
   * @return array
   */
  public function processConfiguration($submittedValues, &$output) {
    $configuration = array();
    $configuration['delimiter'] = $submittedValues['delimiter'];
    $configuration['enclosure'] = $submittedValues['enclosure'];
    $configuration['escape_char'] = $submittedValues['escape_char'];
    return $configuration;
  }

  /**
   * Returns the mime type of the export file.
   *
   * @return string
   */
  public function mimeType() {
    return 'text/csv';
  }

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string
   */
  public function getTitleForExport($output, $dataProcessor) {
    return E::ts('Download as CSV');
  }

  /**
   * Returns the url for the page/form this output will show to the user
   *
   * @param array $output
   * @param array $dataProcessor
   * @return string|false
   */
  public function getExportFileIcon($output, $dataProcessor) {
    return '<i class="fa fa-file-excel-o">&nbsp;</i>';
  }

  /**
   * Download export
   *
   * @param \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass
   * @param array $dataProcessor
   * @param array $outputBAO
   * @param array $formValues
   * @param string $sortFieldName
   * @param string $sortDirection
   * @return string
   */
  public function downloadExport(\Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass, $dataProcessor, $outputBAO, $formValues, $sortFieldName = null, $sortDirection = 'ASC') {
    if ($dataProcessorClass->getDataFlow()->recordCount() > self::MAX_DIRECT_SIZE) {
      $this->startBatchJob($dataProcessorClass, $dataProcessor, $outputBAO, $formValues, $sortFieldName, $sortDirection);
    } else {
      $this->doDirectDownload($dataProcessorClass, $dataProcessor, $outputBAO, $sortFieldName, $sortDirection);
    }
  }

  protected function doDirectDownload(\Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass, $dataProcessor, $outputBAO, $sortFieldName = null, $sortDirection = 'ASC') {
    $filename = date('Ymdhis').'_'.$dataProcessor['id'].'_'.$outputBAO['id'].'_'.CRM_Core_Session::getLoggedInContactID().'_'.$dataProcessor['name'].'.csv';
    $download_name = date('Ymdhis').'_'.$dataProcessor['name'].'.csv';

    $basePath = CRM_Core_Config::singleton()->templateCompileDir . 'dataprocessor_export_csv';
    CRM_Utils_File::createDir($basePath);
    CRM_Utils_File::restrictAccess($basePath.'/');

    $path = CRM_Core_Config::singleton()->templateCompileDir . 'dataprocessor_export_csv/'. $filename;
    if ($sortFieldName) {
      $dataProcessorClass->getDataFlow()->addSort($sortFieldName, $sortDirection);
    }

    self::createHeaderLine($path, $dataProcessorClass, $outputBAO['configuration']);
    self::exportDataProcessor($path, $dataProcessorClass, $outputBAO['configuration']);

    $mimeType = CRM_Utils_Request::retrieveValue('mime-type', 'String', '', FALSE);

    if (!$path) {
      CRM_Core_Error::statusBounce('Could not retrieve the file');
    }

    $buffer = file_get_contents($path);
    if (!$buffer) {
      CRM_Core_Error::statusBounce('The file is either empty or you do not have permission to retrieve the file');
    }

    CRM_Utils_System::download(
      $download_name,
      $mimeType,
      $buffer,
      NULL,
      TRUE,
      'download'
    );
  }


  protected function startBatchJob(\Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessorClass, $dataProcessor, $outputBAO, $formValues, $sortFieldName = null, $sortDirection = 'ASC') {
    $session = CRM_Core_Session::singleton();

    $name = date('Ymdhis').'_'.$dataProcessor['id'].'_'.$outputBAO['id'].'_'.CRM_Core_Session::getLoggedInContactID().'_'.$dataProcessor['name'];

    $queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => $name,
      'reset' => TRUE, //do flush queue upon creation
    ));

    $basePath = CRM_Core_Config::singleton()->templateCompileDir . 'dataprocessor_export_csv';
    CRM_Utils_File::createDir($basePath);
    CRM_Utils_File::restrictAccess($basePath.'/');
    $filename = $basePath.'/'. $name.'.csv';

    self::createHeaderLine($filename, $dataProcessorClass, $outputBAO['configuration']);

    $count = $dataProcessorClass->getDataFlow()->recordCount();
    $recordsPerJob = self::RECORDS_PER_JOB;
    for($i=0; $i < $count; $i = $i + $recordsPerJob) {
      $title = E::ts('Exporting records %1/%2', array(
        1 => ($i+$recordsPerJob) <= $count ? $i+$recordsPerJob : $count,
        2 => $count,
      ));

      //create a task without parameters
      $task = new CRM_Queue_Task(
        array(
          'CRM_DataprocessorOutputExport_CSV',
          'exportBatch'
        ), //call back method
        array($filename,$formValues, $dataProcessor['id'], $outputBAO['id'], $i, $recordsPerJob, $sortFieldName, $sortDirection), //parameters,
        $title
      );
      //now add this task to the queue
      $queue->createItem($task);
    }

    $url = str_replace("&amp;", "&", $session->readUserContext());

    $runner = new CRM_Queue_Runner(array(
      'title' => E::ts('Exporting data'), //title fo the queue
      'queue' => $queue, //the queue object
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE, //abort upon error and keep task in queue
      'onEnd' => array('CRM_DataprocessorOutputExport_CSV', 'onEnd'), //method which is called as soon as the queue is finished
      'onEndUrl' => $url,
    ));

    $runner->runAllViaWeb(); // does not return
  }

  protected static function createHeaderLine($filename, \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessor, $configuration) {
    $file = fopen($filename, 'a');
    fwrite($file, "\xEF\xBB\xBF"); // BOF this will make sure excel opens the file correctly.
    $headerLine = array();
    foreach($dataProcessor->getDataFlow()->getOutputFieldHandlers() as $outputHandler) {
      $headerLine[] = $outputHandler->getOutputFieldSpecification()->title;
    }
    fputcsv($file, $headerLine, $configuration['delimiter'], $configuration['enclosure'], $configuration['escape_char']);
    fclose($file);
  }

  protected static function exportDataProcessor($filename, \Civi\DataProcessor\ProcessorType\AbstractProcessorType $dataProcessor, $configuration) {
    $file = fopen($filename, 'a');
    try {
      while($record = $dataProcessor->getDataFlow()->nextRecord()) {
        $row = array();
        foreach($record as $field => $value) {
          $row[] = $value->formattedValue;
        }
        fputcsv($file, $row, $configuration['delimiter'], $configuration['enclosure'], $configuration['escape_char']);
      }
    } catch (\Civi\DataProcessor\DataFlow\EndOfFlowException $e) {
      // Do nothing
    }
    fclose($file);
  }

  public static function exportBatch(CRM_Queue_TaskContext $ctx, $filename, $params, $dataProcessorId, $outputId, $offset, $limit, $sortFieldName = null, $sortDirection = 'ASC') {
    $dataProcessor = civicrm_api3('DataProcessor', 'getsingle', array('id' => $dataProcessorId));
    $output = civicrm_api3('DataProcessorOutput', 'getsingle', array('id' => $outputId));
    $dataProcessorClass = \CRM_Dataprocessor_BAO_DataProcessor::dataProcessorToClass($dataProcessor);
    CRM_Dataprocessor_Form_Output_AbstractUIOutputForm::applyFilters($dataProcessorClass, $params);
    if ($sortFieldName) {
      $dataProcessorClass->getDataFlow()->addSort($sortFieldName, $sortDirection);
    }
    $dataProcessorClass->getDataFlow()->setOffset($offset);
    $dataProcessorClass->getDataFlow()->setLimit($limit);
    self::exportDataProcessor($filename, $dataProcessorClass, $output['configuration']);
    return TRUE;
  }

  public static function onEnd(CRM_Queue_TaskContext $ctx) {
    $queue_name = $ctx->queue->getName();
    $filename = $queue_name.'.csv';
    $downloadLink = CRM_Utils_System::url('civicrm/dataprocessor/form/output/download', 'filename='.$filename.'&directory=dataprocessor_export_csv');
    //set a status message for the user
    CRM_Core_Session::setStatus(E::ts('<a href="%1">Download CSV file</a>', array(1=>$downloadLink)), E::ts('Exported data'), 'success');
  }


}