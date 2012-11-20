<?php


spl_autoload_register(function ($class) {
  
  $dirs = array(
    "lib/controller/",
    "lib/model/",
    "lib/utils/",
    "lib/utils/importers/",
    "lib/utils/exporters/",
    "tests/"
  );
  
  foreach($dirs as $dir) {
    $file = $file = __DIR__.'/../'.$dir.$class.'.php';
    if (file_exists($file)) {
      require_once $file;
    }
  }
});