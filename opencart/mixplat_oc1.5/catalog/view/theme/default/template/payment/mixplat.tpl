<?php if ($instructionat){ ?>
<h2><?php echo $text_instruction; ?></h2>
<div class="content">
  <p><?php echo $mixplati; ?></p>
</div>
<?php } ?>
<div class="buttons">
  <div class="right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" data-loading-text="<?php echo $text_loading; ?>" />
  </div>
</div>
<?php if ($btnlater){ ?><div class="left"><input class="paylater button" type="button" value="<?php echo $button_later; ?>" id="button-pay"  style="float:right;" data-loading-text="<?php echo $text_loading; ?>" /></div> <?php } ?>
<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
	$.ajax({ 
		type: 'get',
		<?php if (!isset($notcreate)) { ?>
		url: 'index.php?route=payment/<?php echo $pname; ?>/confirm',
		<?php } ?>
		cache: false,
		beforeSend: function() {
			$('#button-confirm').button('loading');
		},
		complete: function() {
			$('#button-confirm').button('reset');
		},
		success: function() {
			location = '<?php echo $pay_url; ?>';
		}		
	});
});
<?php if ($btnlater){ ?>
$('#button-pay').bind('click', function() {
	$.ajax({ 
		type: 'get',
		url: 'index.php?route=payment/<?php echo $pname; ?>/confirm',
		cache: false,
		beforeSend: function() {
			$('#button-confirm').button('loading');
		},
		complete: function() {
			$('#button-confirm').button('reset');
		},		
		success: function() {
			location = '<?php echo $payment_url; ?>';
		}		
	});
});
<?php } ?>
//--></script>
