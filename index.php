<?php
namespace blackfin;

require 'blackfin.php';

$app = new Blackfin(function($settings){});

$app->get('/a', function(){
  return 'Hello, World';
});

$app->get('/b', function(){
  $this->title = 'this is a test';
  $this->myfun = function(){
    return 'functions with $this: '.$this->title;
  };
  $this->render('index');
});

$app->get('/g', function(){
  header('Content-type: text/plain');
  return "Plain content";
});

$app->get('/sample/*', function(){
  $this->title = 'Hello';
  $this->render('index');
});

$app->get('/api', function(){
  return array('status'=>'cool');
});

$app->get('*', function(){
  echo '<body>404 - Not found</body>';
});
