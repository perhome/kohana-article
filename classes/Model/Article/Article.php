<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Article_Article extends Model {

  protected $table;
  protected $database='default';
  protected static $group = 'default';

  public function __construct($config = null)
  {
    if (is_array($config)) {
      foreach($config as $k=>$v) {
        $this->{$k} = $v;
      }
    }
  }

  public static function instance($group=null, $config=null)
  {
    if ($group !== null) {
      static::$group = $group;
    } 
    $class = get_called_class();
    return parent::factory($class, $config);
  }

  public function key($key)
  {
    return static::$group.':'.$key;
  }
}

