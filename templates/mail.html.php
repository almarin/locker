<html>
<head>
	<style>
	body{
	   font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; 
	   font-weight: 300;
	   font-size: 14px;		
	}
		
	</style>
</head>
<body>
	<div style="width:800px;text-align: justify">
	<?php echo str_replace(array("\r\n", "\n\r", "\n", "\r"), "<br/>", $this->group->group_metadata->msg);?>
	<div style="margin-top: 20px; border-bottom:1px solid #C0CACC;width:100%">
		<span style="text-shadow: 1px 1px 1px rgba(0, 0,0,0.5); font-weight: bold;padding:0 5px; background-color: #C0CACC;color: white;border-radius: 0 5px 0 0 ">
			<?php echo _("Shared Locker files"); ?>
		</span>

	</div>
	<div style="border-bottom:1px solid #C0CACC;width:100%">
	<?php foreach ($this->group->getFiles() as $file){ ?>
	<div style="padding:4px">
		<a href="<?php echo Horde::url('download.php?file='.$file->id(),true);?>">

		<img  style="float:left;margin-right:5px" src="cid:<?php echo basename($GLOBALS['injector']->getInstance('Horde_Core_Factory_MimeViewer')->getIcon($file->file_type)->fs);?>" />

		<?php echo $file->file_name;?> 
		</a>
		<span style="color:#999">(<?php echo $file->getHumanSize();?>)</span>
		
	</div>
	<?php } ?>
	</div>
	<span style="color: red; text-shadow: 1px 1px 1px rgba(0, 0,0,0.5);">
		<?php echo sprintf(_("Links expire on %s, in %s days"),$this->group->group_expiration_date->format('d/m/Y'), $this->group->getNumDaysToExpire());?>
		
	</span>
	</div>

</body>
</html>