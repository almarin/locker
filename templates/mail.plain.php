<?php echo str_replace(array("\r\n", "\n\r", "\n", "\r"), "<br/>", $this->group->group_metadata->msg);?>

-------------------------------------------------------------
Ficheros compartidos por consigna
Los enlaces caducan el <?php echo $this->group->group_expiration_date->format('d/m/Y');?> (dentro de <?php echo $this->group->getNumDaysToExpire();?> <?php echo $this->group->getNumDaysToExpire()>1?'días':'día';?>)
-------------------------------------------------------------
<?php foreach ($this->group->getFiles() as $file){ ?>
<?php echo $file->file_name;?> (<?php echo $file->getHumanSize().")\n";?>
<?php echo Horde::url('download.php?file='.$file->id(), true)."\n\n";?>
<?php } ?>

