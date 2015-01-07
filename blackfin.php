<?php
namespace blackfin;
require_once 'blackfin/Route.php';
require_once 'blackfin/ViewModel.php';
require_once 'blackfin/Settings.php';

class Blackfin {
  private $routes;
  private $settings;
  private $viewModel;

  function __construct($cb) {
    $this->routes = array();
    $this->viewModel = new ViewModel;
    $this->settings = new Settings;

    if (is_callable($cb)) $this->init($cb);
  }

  function __destruct() {
    if ($this->settings->auto_route) echo $this->route();
  }

  //
  // map - a generic interface for adding new routes
  //
  // $method - should be a String which will be matched against REQUEST_METHOD.
  // $path - should be a String that will be matched against REQUEST_URI using
  //         fnmatch.
  // $cb - the callback to be invoked when this route is matched. This callback
  //       is invoked in the context of an associated blackfin\ViewModel object.
  //       Any data to be passed to the view should be assigned using $this->*
  //
  public function map($method, $path, $cb) {
    $bound_cb = $cb->bindTo($this->viewModel, $this->viewModel);
    $this->routes[] = new Route($method, $path, $bound_cb);
  }

  //
  // get - add a route that matches GET requests
  //
  public function get($path, $cb) {
    $this->map('HEAD', $path, $cb);
    $this->map('GET', $path, $cb);
  }

  //
  // post - add a route that matches POST requests
  //
  public function post($path, $cb) {
    $this->map('HEAD', $path, $cb);
    $this->map('POST', $path, $cb);
  }

  //
  // route - match and execute the route
  //
  public function route($path=null, $method=null){
    if (!$method) $method = $_SERVER['REQUEST_METHOD'];
    if (!$path)   $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $route = $this->find_route($path, $method);
    return ($this->settings->echo)
        ? $this->unbuffered_call($route)
        : $this->buffered_call($route);
  }

  private function find_route($path, $method) {
    foreach ($this->routes as $route) {
      if ($route->match($method, $path)) return $route;
    }
    return $this->route_404();
  }

  private function route_404() {
    return new Route('*', '*', function(){
      http_send_status(404);
      echo '404';
    });
  }

  private function init($cb) {
    // Bind a copy of the anonymous function to $this
    $m = $cb->bindTo($this, $this);
    // Call the bound function with the settings object as a parameter
    call_user_func($m, $this->settings);
  }

  private function unbuffered_call($cb) {
    $ret = $this->handle_return($cb->call());
    return $ret;
  }

  private function buffered_call($cb) {
    ob_start();
    $ret = $this->handle_return($cb->call());
    $echo = ob_get_clean();
    return $ret ? $ret : $echo;
  }

  private function handle_return($ret) {
    if (is_int($ret)) {
      http_response_code($ret);
      return;
    }

    if (is_array($ret)) {
      header('Content-type: application/json');
      return json_encode($ret);
    }

    if (is_object($ret)) {
      header('Content-type: application/json');
      return json_encode((array)$ret);
    }

    return $ret;
  }
}
