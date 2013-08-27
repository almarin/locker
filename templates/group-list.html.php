
  <?php foreach ($this->groups as $group){ ?>
    <div class="group-wrapper">
      <div class="icon icon-delete" data-id="<?php echo $group->group_id;?>" data-type="group" data-status="<?php echo (isset($this->disabled)?'disabled':'active');?>"></div>
      <div class="group <?php 
        if (isset($this->disabled)){
          echo "disabled";
        } else {
          $days = $group->getDaysLeftToExpire(); 
          echo ($days > 3) ? 'green':($days > 0?'yellow':'red');

        }?>">
        
        <div class="group-info">
          <div class="name <?php echo $group->group_type;?>">
            <?php echo $group->getTitle();?>
          </div>
          <div class="to">
           
            <?php 
                $parser = new Horde_Mail_Rfc822();
                $list = $parser->parseAddressList($group->group_metadata->to);
                
                if (count($list->addresses)>2){
                    $resto = (count($list->addresses)-2);
                    if ($resto == 1){
                      echo sprintf(_("%s, %s and %s additional recipient"), $list->addresses[0], $list->addresses[1], $resto);
                    } else {
                      echo sprintf(_("%s, %s and %s additional recipients"), $list->addresses[0], $list->addresses[1], $resto);
                    }
                } else {
                  echo htmlspecialchars($group->group_metadata->to);  
                }
            ?>
          </div>
        </div>
        <div class="content">
          <div class="meta to">
             <span class="to-label"><?php echo _("Recipients:");?></span>
            <?php echo htmlspecialchars($group->group_metadata->to);?>
          </div>
          <div class="meta msg">
            <?php echo str_replace(array("\n", "\r", "\n\r", "\r\n"), "<br/>", $group->group_metadata->msg);?>
          </div>
          <div class="meta files">
            <ul>
            <?php
              $files = $group->getFiles();
              foreach ($files as $file){
            ?>
              <li style="display:<?php $file->file_status == 'hidden'?'none':'block';?>"><div class="file-name">
                  <?php if (!isset($this->disabled) && $file->file_status == 'online'){ ?>
                    <a target="_blank" href="<?php echo Locker::getDownloadUrl($file);?>">
                  <?php } ?>                      
                    <?php echo Locker::fileTypeToImg($file->file_type); ?>
                    <span class="<?php echo $file->file_status;?>"><?php echo $file->file_name;?></span>
                  <?php if (!isset($this->disabled) && $file->file_status == 'online'){ ?>
                    </a>
                  <?php } ?>
              </div>
              <div class="file-size <?php echo $file->file_status;?>">
                <?php echo $file->getHumanSize();?>
              </div>
              <div class="file-downloaded"></div>
              </li>
            <?php } ?>
          </ul>
          <div style="clear:both"></div>
          </div>
        </div>
        <div class="extra">
          <div class="files">
            
            <?php echo sprintf(_('Sent %s at %s with <strong>%s files</strong>'), 
                $group->group_sent_date->format('d/m/Y'), 
                $group->group_sent_date->format('H:i:s'),
                count($group->getFiles()));?>
          </div>
          <div class="time-left">
            <?php echo $group->expireStr();?>
          </div>
        </div>  
      </div>
    </div>
  <? } ?>
