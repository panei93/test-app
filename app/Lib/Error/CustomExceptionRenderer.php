<?php
/**
 *	Exception Renderer
 *	@author Thura Moe
 **/
App::uses('ExceptionRenderer', 'Error');

class CustomExceptionRenderer extends ExceptionRenderer {

  protected function _outputMessage($template) {
    $this->controller->layout = '';
    parent::_outputMessage($template);
  }

}
?>