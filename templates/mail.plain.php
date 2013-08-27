<?php echo str_replace(array("\r\n", "\n\r", "\n", "\r"), "<br/>", $this->group->group_metadata->msg);?>

-------------------------------------------------------------
<?php echo _("Shared Locker files"); ?>
<?php echo sprintf(_("Links expire on %s, in %s days"),$this->group->group_expiration_date->format('d/m/Y'), $this->group->getNumDaysToExpire());?>
-------------------------------------------------------------
<?php foreach ($this->group->getFiles() as $file){ ?>
<?php echo $file->file_name;?> (<?php echo $file->getHumanSize().")\n";?>
<?php echo Horde::url('download.php?file='.$file->id(), true)."\n\n";?>
<?php } ?>

