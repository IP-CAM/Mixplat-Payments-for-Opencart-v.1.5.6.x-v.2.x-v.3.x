<?php if ($instructionat){ ?>
<h2><?php echo $text_instruction; ?></h2>
<div class="well well-sm">
  <p><?php echo $mixplati; ?></p>
</div>
<?php } ?>
<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" data-loading-text="<?php echo $text_loading; ?>" />
  </div>
</div>
<?php if ($btnlater){ ?><div class="pull-left"><input class="paylater btn btn-secondary" type="button" value="<?php echo $button_later; ?>" id="button-pay"  style="float:right;" data-loading-text="<?php echo $text_loading; ?>" /></div> <?php } ?>
<script type="text/javascript"><!--
$('#button-confirm').on('click', function() {
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
$('#button-pay').on('click', function() {
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
