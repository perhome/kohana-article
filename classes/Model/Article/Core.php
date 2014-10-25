<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Article_Core extends Model_Article_Article {

  protected $key_article_key = 'article:key';
  protected $key_article_spell_map = 'article:spell:map';
  protected $key_article_list = 'article:list';
  protected $key_article_sorted_by_created = 'article:sorted:by:created';
  protected $key_article_sorted_by_hot = 'article:sorted:by_hot';

  public function get_list_sorted_by_created($start=null, $score=null, $limit=10)
  {
    $keys = Kv::instance()->zrscan($this->key($this->key_article_sorted_by_created), $start, $score, null, $limit);
    $list = Kv::instance()->multi_hget($this->key($this->key_article_list), array_keys($keys));
    return $list;
  }

  public function get_list_sorted_by_hot($start=null, $score=null, $limit=10)
  {
    $keys = Kv::instance()->zrscan($this->key($this->key_article_sorted_by_hot), $start, $score, null, $limit);
    $list = Kv::instance()->multi_hget($this->key($this->key_article_list), array_keys($keys));
    return $list;
  }

  public function search($post, $or = true)
  {
    $return = array();
    $page = isset($post['page'])? max((int)$post['page'], 1):1;
    $query = DB::query(Database::SELECT, 
        'select id from :table where search @@ to_tsquery(\'public.zhcfg\', :keyword) order by ts_rank_cd(search, to_tsquery(\'public.zhcfg\', :keyword)) limit :limit offset :offset') 
        ->paraw(':table', static::$table)
        ->param(':keyword', $post['keyword'])
        ->param(':limit', 20)
        ->param(':offset', ($page-1) * 20)
        ->execute()
        ->as_array('id');
    if ($query) {
      $return = Kv::instance()->multi_hget($this->key($this->key_article_list), array_keys($query));
    }
    return $return;
  }

  public function get($key)
  {
    if (is_numeric($key)) {
      $arr = Kv::instance()->hget($this->key($this->key_article_list), $key);
    }
    else {
      $arr = Kv::instance()->hget($this->key($this->key_article_spell_map), $key);
      if ($arr) {
        $arr = Kv::instance()->hget($this->key($this->key_article_list), $arr);
      }
    }
    if ($arr) {
      $arr =  json_decode($arr, true);
      $id = Arr::get($arr, 'id');
      Kv::instance()->zincr($this->key($this->key_article_sorted_by_hot), $id, 1);
      return $arr;
    }
    return false;
  }

  public function save($data)
  {
    $key = Kv::instance()->incr($this->key($this->key_article_key), 1);
    if (isset($data['spell'])==false) {
      $spell = $data['spell'] = str_replace(' ', '-', trim(Text::spell($data['title'], true)));
    }
    if (isset($data['category'])==false) {
      $data['category'] = 0;
    }
    if (Kv::instance()->hexists($this->key($this->key_article_spell_map), $spell)) {
      return false;
    }
    $data['id'] = $key;
    $ret = Kv::instance()->hset($this->key($this->key_article_list), $key, json_encode($data));
    Kv::instance()->hset($this->key($this->key_article_spell_map), $spell, $key);
    Kv::instance()->zset($this->key($this->key_article_sorted_by_created), $key, time());
    Kv::instance()->zset($this->key($this->key_article_sorted_by_hot), $key, 0);
    if (static::$table) {
      DB::query(Database::INSERT, 
        'insert into :table (id, category, search) 
          values (:id, :category, setweight(to_tsvector(\'zhcfg\', coalesce(:title,\'\')), \'A\') || setweight(to_tsvector(\'zhcfg\', coalesce(:body,\'\')), \'D\'))') 
        ->paraw(':table', static::$table)
        ->param(':id', $data['id'])
        ->paraw(':category', $data['category'])
        ->param(':title', $data['title'])
        ->param(':body', $data['body'])
        ->execute();
    }
    return $key;
  }

  public function delete($key)
  {
    if (Kv::instance()->hexists($this->key($this->key_article_key), $key)) {
      $arr = $this->get($key);
      Kv::instance()->hdel($this->key($this->key_article_index), $key);
      Kv::instance()->hdel($this->key($this->key_article_spell_map), Arr::get($arr, 'spell'));
      Kv::instance()->zdel($this->key($this->key_article_sorted_by_created), $key);
      Kv::instance()->zdel($this->key($this->key_article_sorted_by_hot), $key);
      return true;
    }
    return false;
  }

}

