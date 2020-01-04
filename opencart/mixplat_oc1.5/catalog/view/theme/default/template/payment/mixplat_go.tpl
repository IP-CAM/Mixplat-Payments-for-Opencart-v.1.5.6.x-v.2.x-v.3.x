<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <h1><?php echo $heading_title; ?></h1>
 <div class="yandexur">
 <p><?php echo $send_text; ?></p>
 <div class="buttons">
  <table>
    <tr>
      <?php if ($paystat != 1){ ?><td align="right"><form method="POST" action="<?php echo $merchant_url;?>"><input type="hidden" name="nesyandexa" value="nesyandexa"><input type="submit" value="<?php echo $button_pay; ?>" class="button"></form></td> <?php } ?>
    </tr>
  </table>
</div>
</div>
  </div>
  <?php echo $content_bottom; ?>
<?php echo $footer; ?>