<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Article_Category extends Model_Article_Article {

  protected $key_category_index = 'category:index';
  protected $key_category_sorted_by_weight = 'category:sorted:weight';

  public function get_list_sorted_by_weight($limit=50)
  {
    $keys = Kv::instance()->zrscan($this->key($this->key_category_sorted_by_weight), null, null, null, $limit);
    $list = Kv::instance()->multi_hget($this->key($this->key_category_index), array_keys($keys));
    $arr = array();
    if (is_array($list)) {
      foreach($list as $index=>$v) {
        $arr[$index] =  json_decode($v);
      }
    }
    return $arr;
  }

  public function get($key)
  {
    $arr = Kv::instance()->hget($this->key($this->key_category_index), $key);
    if ($arr) {
      return json_decode($arr, true);
    }
    return false;
  }

  public function save($data)
  {
    if (isset($data['weight'])==false) {
      $data['weight'] = time();
    }
    $spell = $data['spell'] = str_replace(' ', '-', trim(Text::spell($data['name'], true)));
    $ret = Kv::instance()->hset($this->key($this->key_category_index), $spell, json_encode($data));
    Kv::instance()->zset($this->key($this->key_category_sorted_by_weight), $spell, $data['weight']);
    return $spell;
  }

  public function delete($key)
  {
    if (Kv::instance()->hexists($this->key($this->key_category_index), $key)) {
      Kv::instance()->hdel($this->key($this->key_category_index), $key);
      return true;
    }
    return false;
  }

}

