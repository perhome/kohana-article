<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Article_Core extends Model_Article_Article {

  protected $key_article_id    = 'article:id';
  protected $key_article_index = 'article:index';
  protected $key_article_spell_map = 'article:spell:map';
  protected $key_article_sorted_by_created = 'article:sorted:by:created';
  protected $key_article_sorted_by_hot = 'article:sorted:hot';

  public function get_list_sorted_by_created($start=null, $score=null, $limit=20)
  {
    $keys = Kv::instance()->zrscan($this->key($this->key_article_sorted_by_created), $start, $score, null, $limit);
    $list = Kv::instance()->multi_hget($this->key($this->key_article_index), array_keys($keys));
    return $list;
  }

  public function get_list_sorted_by_hot($start=null, $score=null, $limit=10)
  {
    $keys = Kv::instance()->zrscan($this->key($this->key_article_sorted_by_hot), $start, $score, null, $limit);
    $list = Kv::instance()->multi_hget($this->key($this->key_article_index), array_keys($keys));
    return $list;
  }

  public function search($where, $or = true)
  {
    $return = array();
    $page = isset($where['page'])? max((int)$where['page'], 1):1;
    $param = array();
    $sql = 'select id from :table where true and ';
    if (isset($where['cat']) && $where['cat']) {
      $sql .= 'category=:category order by created desc limit :limit offset :offset';
      $param[':category'] = $where['cat'];
    }
    if (isset($where['keyword']) && $where['keyword']) {
      $sql .= ' search @@ to_tsquery(\'public.zhcfg\', :keyword) order by ts_rank_cd(search, to_tsquery(\'public.zhcfg\', :keyword)), created desc limit :limit offset :offset';
      $param[':keyword'] = $where['keyword'];
    }
    $query = DB::query(Database::SELECT, $sql)
        ->paraw(':table', $this->table)
        ->parameters($param)
        ->param(':limit', 20)
        ->param(':offset', ($page-1) * 20)
        ->execute($this->database) 
        ->as_array('id');
    if ($query) {
      $return = Kv::instance()->multi_hget($this->key($this->key_article_index), array_keys($query));
    }
    return $return;
  }

  public function get($key)
  {
    if (is_numeric($key)) {
      $arr = Kv::instance()->hget($this->key($this->key_article_index), $key);
    }
    else {
      $arr = Kv::instance()->hget($this->key($this->key_article_spell_map), $key);
      if ($arr) {
        $arr = Kv::instance()->hget($this->key($this->key_article_index), $arr);
      }
    }
    if ($arr) {
      $arr =  json_decode($arr, true);
      $id = Arr::get($arr, 'id');
      return $arr;
    }
    return false;
  }

  public function hot($id)
  {
    Kv::instance()->zincr($this->key($this->key_article_sorted_by_hot), $id, 1);
  }
  
  public function save($data)
  {
    $key = isset($data['id'])?(int)$data['id']:0;
    $new = true;
    if ($key) {
      $new = false;
      $data['updated'] = time();
    }
    else {
      $key = Kv::instance()->incr($this->key($this->key_article_id), 1);
      if (isset($data['created'])==false) {
        $data['created'] = time();
      }
      if (isset($data['updated'])==false) {
        $data['updated'] = time();
      }
      $arr = $this->get($key);
      Kv::instance()->hdel($this->key($this->key_article_spell_map), Arr::get($arr, 'spell'));
    }
    $spell = $data['spell'] = str_replace(' ', '-', trim(Text::spell($data['title'], true)));
    $data['id'] = $key;
    $ret = Kv::instance()->hset($this->key($this->key_article_index), $key, json_encode($data));
    Kv::instance()->hset($this->key($this->key_article_spell_map), $spell, $key);
    if ($new) {
      Kv::instance()->zset($this->key($this->key_article_sorted_by_created), $key, time());
      Kv::instance()->zset($this->key($this->key_article_sorted_by_hot), $key, 0);
    }
    if (isset($this->table) && $this->table) {
      DB::query(Database::DELETE, 'delete from :table where id=:id') 
        ->paraw(':table', $this->table)
        ->param(':id', $data['id'])
        ->execute($this->database);
      DB::query(Database::INSERT, 
        'insert into :table (id, category, search) 
          values (:id, :category, setweight(to_tsvector(\'zhcfg\', coalesce(:title,\'\')), \'A\') || setweight(to_tsvector(\'zhcfg\', coalesce(:body,\'\')), \'D\'))') 
        ->paraw(':table', $this->table)
        ->param(':id', $data['id'])
        ->param(':category', $data['category'])
        ->param(':title', $data['title'])
        ->param(':body', $data['body'])
        ->execute($this->database);
    }
    return $key;
  }

  public function delete($key)
  {
    if (Kv::instance()->hexists($this->key($this->key_article_index), $key)) {
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

