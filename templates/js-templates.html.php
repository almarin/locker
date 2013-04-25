<script type="text/template" id="new-upload">
<div class="upload-shadowbox-wrapper">
<div class="upload-shadowbox"></div>
<form id="fileupload_form" class=" upload-window"  method="POST" enctype="multipart/form-data">
  <div class="dropzone ">
  		<span id="upload_msg">Arrastra hasta aquí ficheros desde tu escritorio, o utiliza el siguiente botón<br/></span>
  		<input id="fileupload" type="file" class="horde-default" name="files[]" multiple>
   </div>
   <div class="hideable" style="display:none">
	   <div class="uploading-zone" id="uploading-zone">
	   		<div class="uploading-zone-content"></div>
	   </div>
	   <div class="options">
	   		<div class="duration">
	   			<strong>¿Cuantos días desea mantener estos ficheros accesibles para que puedan ser descargados?</strong>
	   			<select name="expire">
	   				<option value="1">1 día</option>
	   				<option value="3" selected>3 días</option>
	   				<option value="5">5 días</option>
	   				<option value="7">7 días</option>
	   				<option value="10">10 días</option>
	   				<option value="15">15 días</option>
	   				<option value="30">30 días</option>
	   			</select>
	   		</div>
	   </div>
		<div class="mail">
			<table>
				<tr>
					<td class="label">Para:</td>
					<td >
						<input class="field" name="to"/>
					</td>
				</tr>
				<tr>
					<td class="label">Asunto:</td>
					<td>
						<input class="field" name="subject"/>
					</td>
				</tr>
			</table>
			<textarea name="msg" class="field"></textarea>
			<div class="buttons">
				<a class="horde-default" data-action="send">Enviar</a>
				<a class="horde-button" data-action="cancelar">Cancelar</a>
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
	</tr>
</div>
</script>