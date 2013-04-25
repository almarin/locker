<h1 class="header"><?php echo _('Active Groups'); /* Unescaped output */ ?></h1>
<div class="locker-list actives">

<?php echo $this->active_groups_html;?>
</div>
<?php if ($this->inactive_groups_html) { ?>
<h1 class="header"><?php echo _('Expired Groups'); ?></h1> 
<div class="locker-list disabled">

<?php echo $this->inactive_groups_html; ?>

<?php if ($this->morepages){ ?>

  <div class="more-link">
	  <div class="more">
	  </div>
    <a data-action="next-page" data-type="group"><?php echo _('Load more expired groups');?></a>
    <span class="loading" style="display:none">Cargando...</span>
  </div> 

<?php } ?>
</div> 
<?php } ?>


