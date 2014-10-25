<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Article_Article extends Model {

  protected static $table = '"article"';
  protected static $group = 'default';
  
  public static function instance($group=null)
  {
    if ($group !== null) {
      static::$group = $group;
    } 
    $class = get_called_class();
    return parent::factory($class);
  }

  public function key($key)
  {
    return static::$group.':'.$key;
  }
}

