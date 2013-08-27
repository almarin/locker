<script type="text/template" id="new-upload">
<div class="upload-shadowbox-wrapper">
<div class="upload-shadowbox"></div>
<form id="fileupload_form" class=" upload-window"  method="POST" enctype="multipart/form-data">
  <div class="dropzone ">
  		<span id="upload_msg"><?php echo _("Drag here your files from your desktop, or use next button");?><br/></span>
  		<div style="position:relative; width: 100%;margin-top:10px">
  			<button style="margin:auto;" class="horde-button"><?php echo _("Select Files");?></button>
	  		<input style="position:absolute;top:0px;left:30%;opacity:0" id="fileupload" type="file" class="horde-default" name="files[]" multiple>
  		</div>
   </div>
   <div class="hideable" style="display:none">
	   <div class="uploading-zone" id="uploading-zone">
	   		<div class="uploading-zone-content"></div>
	   </div>
	   <div class="options">
	   		<div class="duration">
	   			<strong>
	   				<?php echo _("How many days do you want to keep these files accesible so they can be downloaded?");?>
	   			</strong>
	   			<select name="expire">
	   				<?php foreach ($GLOBALS['conf']['locker_days'] as $day) { ?>
	   					<option value = "<?php echo ($day == $GLOBALS['conf']['default_days'] ? $day.'" selected': $day.'"');?>>
	   						<?php echo $day . " " . ($day == 1 ? _('day'):_('days')); ?>
	   					</option>
	   				<?php } ?>
	   			</select>
	   		</div>
	   </div>
		<div class="mail">
			<table>
				<tr>
					<td class="label"><?php echo _("For:");?></td>
					<td >
						<input class="field" name="to"/>
					</td>
				</tr>
				<tr>
					<td class="label"><?php echo _("Subject:");?></td>
					<td>
						<input class="field" name="subject"/>
					</td>
				</tr>
			</table>
			<textarea name="msg" class="field"></textarea>
			<div class="buttons">
				<a class="horde-default" data-action="send"><?php echo _("Send");?></a>
				<a class="horde-button" data-action="cancelar"><?php echo _("Cancel");?></a>
			</div>
		</div>
	</div>
</form>
</div>
</script>

<script type="text/template" id="upload-list-item">
<table width="100%">
	<tr>
		<td class="file-name">
		  	<strong><%=name %></strong>
		</td>
		<td class="file-size">
			<strong><%=fsize %></strong>
		</td>
		<td class="file-progress">
		  <div class="upload-progress">
		    <div class="bar" style="width:0%">
		    </div>
		  </div>
		</td>
		<td>
			<span class="removeFileButton">×</span>
		</td>
	</tr>
</div>
</script>
<script type="text/template" id="continue-upload-msg">
<?php echo _("Uploading files... You can continue adding additional files");?><br/>
</script>
<script type="text/javascript">
var TR = {
	"You must set at least one valid recipient": "<?php echo _("You must set at least one valid recipient");?>",
	"There are still files pending upload ": "<?php echo _("There are still files pending upload ");?>",
	"Are you sure you want to delete this message? Associated files will no longer be available for download": "<?php echo _("Are you sure you want to delete this message? Associated files will no longer be available for download");?>",
	"Are you sure you want to delete this file? Note that it will no longer be available for download": "<?php echo _("Are you sure you want to delete this file? Note that it will no longer be available for download");?>"

}
</script>