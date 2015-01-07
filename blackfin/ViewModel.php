<?php
namespace blackfin;
require_once 'DynamicDefinition.php';
require_once 'ViewContext.php';

class ViewModel {
  use DynamicDefinition;

  private function render($view){
    $model  = (array)$this;
    $layout = "views/layout.php";
    $view   = "views/$view.php";
    $ctx    = new ViewContext;
    $ctx->render($model, $layout, $view);
  }
}
