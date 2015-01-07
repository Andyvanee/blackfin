<?php
namespace blackfin;

class ViewContext {
  function render($model, $__layout, $__view) {
    $yield = function() use ($model, $__view) {
      extract($model, EXTR_SKIP);
      include $__view;
    };
    extract($model, EXTR_SKIP);
    include $__layout;
  }
}
