

  <?php foreach ($this->files as $file){ ?>
    <div class="file-wrapper">
      <div class="icon icon-delete" data-id="<?php echo $file->file_id;?>" data-type="file" data-status="<?php echo (isset($this->disabled)?'disabled':'active');?>"></div>
      <div class="file">
                  <?php if (!isset($this->disabled)){ ?>
                    <a target="_blank" href="<?php echo Locker::getDownloadUrl($file);?>">
                  <?php } ?>
                    <?php echo Locker::fileTypeToImg($file->file_type); ?>
                    <?php echo $file->file_name;?>
                  <?php if (!isset($this->disabled)){ ?>
                    </a>
                  <?php } ?>
            <span style="float:right;width:150px;text-align:right">
              <?php echo $file->getHumanSize()?>
            </span>
            <span style="float:right">
              <?php echo $file->file_creation_date->format('d/m/Y');?>
            </span>

      </div>
    </div>
  <? } ?>
