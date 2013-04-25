<h1 class="header"><?php echo _('Active Files'); /* Unescaped output */ ?></h1>
<div class="locker-list">
<?php echo $this->active_files_html;?>
</div>
<?php if ($this->active_files_html) { ?>
<h1 class="header"><?php echo _('Expired Files'); ?></h1> 
<div class="locker-list">
<?php echo $this->inactive_files_html; ?>

<?php if ($this->morepages){ ?>
  <div class="more-link">
	  <div class="more">
	  </div>  	
    <a data-action="next-page" data-type="file"><?php echo _('Load more expired files');?></a>
    <span class="loading" style="display:none">Cargando...</span>    
  </div>
<?php } ?>
</div>


<?php } ?>

