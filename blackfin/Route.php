<?php
namespace blackfin;

class Route {
  private $callback;

  private $path;

  private $method;

  public function __construct($method, $path, $callback) {
    $this->method = $method;
    $this->path = $path;
    $this->callback = $callback;
  }

  public function match($method, $path) {
    return ($this->method == $method) && (fnmatch($this->path, $path));
  }

  public function call() {
    return call_user_func($this->callback);
  }
}
