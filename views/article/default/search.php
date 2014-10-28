<?php defined('SYSPATH') or die('No direct script access.');?><!DOCTYPE html>
<?php if (isset($data) && $data): ?>
  <ul>
  <?php foreach($data as $one): $one = json_decode($one, true);?>
    <li><?php echo HTML::anchor('test_article/detail/'.$one['spell'], Arr::get($one, 'id').'-'.Arr::get($one, 'title')); ?></li>
  <?php endforeach;?>
  </ul>
  <?php if (count($data)) : ?>
  <?php echo HTML::anchor('test_article'.URL::query(array('from'=>Arr::get($one, 'id').'-'.Arr::get($one, 'created'))), '查看更多', array('class'=>'pure-button')); ?>
  <?php endif; ?>
<?php endif; ?>
